<?php

declare(strict_types=1);

namespace Attributes\Serialization;

use ArrayAccess;
use ArrayObject;
use Attributes\Options;
use Attributes\Serialization\Exceptions\SerializeException;
use DateTimeInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionProperty;
use stdClass;
use UnitEnum;

final class Serializer implements Serializable
{
    /**
     * @var string[]
     */
    private ?array $allVisibilityMethods = null;

    private array $allow;

    private string $datetimeFormat;

    /**
     * @var callable
     */
    private $defaultAliasGenerator;

    public function __construct(array $allow = ['public', 'private', 'protected'], string $datetimeFormat = DateTimeInterface::ATOM)
    {
        $this->allow = $allow;
        $this->datetimeFormat = $datetimeFormat;
        $this->defaultAliasGenerator = fn (string $name) => $name;
    }

    /**
     * Converts a model into an array of primitive types.
     *
     * @param  object  $model  - The model to serialize
     * @param  bool  $useIgnores  - Determines if we should rely on Options\Ignore to serialize the fields needed
     * @param  bool  $useValidation  - Determines if we want only the properties that were marked as validation. Default false
     * @param  bool  $useVisibilityMethods  - Determines if we should rely on the visibility methods to serialize the fields needed
     * @param  bool  $byAlias  - Determines if we should rely on the alias for the fields names
     * @return array|string|int
     *
     * @throws SerializeException - when an error occurs
     */
    public function serialize(object $model, bool $useIgnores = true, bool $useValidation = false, bool $useVisibilityMethods = true, bool $byAlias = true): mixed
    {
        if ($this->allVisibilityMethods === null) {
            $this->allVisibilityMethods = $this->getAllVisibilityMethods();
        }

        if ($model instanceof StdClass) {
            $model = new ArrayObject((array) $model);

            return $this->clone()->serialize($model, $useIgnores, $useValidation, $useVisibilityMethods, $byAlias);
        }

        if ($model instanceof DateTimeInterface) {
            return $model->format($this->datetimeFormat);
        }

        if ($model instanceof ArrayAccess) {
            $allValues = [];
            $clone = clone $this;
            foreach ($model as $key => $item) {
                if (is_array($item)) {
                    $item = new ArrayObject($item);
                }
                $allValues[$key] = is_object($item) ? $clone->serialize($item, $useIgnores, $useValidation, $useVisibilityMethods, $byAlias) : $item;
            }

            return $allValues;
        }

        if ($model instanceof UnitEnum) {
            try {
                $reflectionEnum = new ReflectionEnum($model);

                return $reflectionEnum->isBacked() ? $model->value : $model->name;
            } catch (ReflectionException $e) {
                throw new SerializeException('Unable to serialize enum', previous: $e);
            }
        }

        $data = [];
        $reflection = new ReflectionClass($model);
        $this->defaultAliasGenerator = $this->getDefaultAliasGenerator($reflection);
        foreach ($reflection->getProperties() as $property) {
            if (! $property->isInitialized($model)) {
                continue;
            }

            if (! $this->shouldBeSerialized($property, useIgnores: $useIgnores, useValidation: $useValidation, useVisibilityMethods: $useVisibilityMethods)) {
                continue;
            }

            $value = $property->getValue($model);
            if (is_array($value)) {
                $value = new ArrayObject($value);
            }
            $name = $this->getPropertyName($property, byAlias: $byAlias);
            $data[$name] = is_object($value) ? $this->clone()->serialize($value, $useIgnores, $useValidation, $useVisibilityMethods, $byAlias) : $value;
        }

        return $data;
    }

    /**
     * Retrieves a set of methods that we want to use to restrict a given property visibility
     *
     * @throws SerializeException
     */
    private function getAllVisibilityMethods(): array
    {
        $validVisibilities = [
            'public' => 'isPublic',
            'private' => 'isPrivate',
            'protected' => 'isProtected',
        ];
        $allVisibilities = [];
        foreach ($this->allow as $visibility) {
            $visibility = strtolower((string) $visibility);
            if (! isset($validVisibilities[$visibility])) {
                throw new SerializeException("Invalid property visibility '{$visibility}'. Expected 'public', 'private' or 'protected'.");
            }

            $allVisibilities[] = $validVisibilities[$visibility];
        }

        if (count($allVisibilities) === count($validVisibilities)) {
            return [];
        }

        return $allVisibilities;
    }

    /**
     * Determines if a given property should be serialized
     */
    private function shouldBeSerialized(ReflectionProperty $property, bool $useIgnores, bool $useValidation, bool $useVisibilityMethods): bool
    {
        $allIgnoreAttributes = $property->getAttributes(Options\Ignore::class);
        if ($useIgnores && $allIgnoreAttributes) {
            $ignore = $allIgnoreAttributes[0]->newInstance();

            return $useValidation ? ! $ignore->ignoreValidation() : ! $ignore->ignoreSerialization();
        }

        if (! $this->allVisibilityMethods || ! $useVisibilityMethods) {
            return true;
        }

        foreach ($this->allVisibilityMethods as $methodName) {
            if (call_user_func([$property, $methodName])) {
                return true;
            }
        }

        return false;
    }

    private function getDefaultAliasGenerator(ReflectionClass $reflectionClass): callable
    {
        $aliasGenerator = $reflectionClass->getAttributes(Options\AliasGenerator::class);
        if (! $aliasGenerator) {
            return $this->defaultAliasGenerator;
        }

        return $aliasGenerator[0]->newInstance()->getAliasGenerator();
    }

    private function getPropertyName(ReflectionProperty $property, bool $byAlias): string
    {
        if (! $byAlias) {
            return $property->getName();
        }

        $alias = $property->getAttributes(Options\Alias::class);
        if ($alias) {
            return $alias[0]->newInstance()->getAlias($property->getName());
        }

        return call_user_func($this->defaultAliasGenerator, $property->getName());
    }

    /**
     * @internal
     */
    public function setVisibilityMethods(array $visibilityMethods): void
    {
        $this->allVisibilityMethods = $visibilityMethods;
    }

    /**
     * @internal
     */
    public function setDefaultAliasGenerator(callable $defaultAliasGenerator): void
    {
        $this->defaultAliasGenerator = $defaultAliasGenerator;
    }

    /**
     * Clones the given serializer
     *
     * @internal
     */
    private function clone(): Serializable
    {
        $serializer = new Serializer(allow: $this->allow, datetimeFormat: $this->datetimeFormat);
        $serializer->setVisibilityMethods($this->allVisibilityMethods);
        $serializer->setDefaultAliasGenerator($this->defaultAliasGenerator);

        return $serializer;
    }

    public function __clone()
    {
        return $this->clone();
    }
}
