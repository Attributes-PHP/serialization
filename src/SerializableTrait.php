<?php

namespace Attributes\Serialization;

use Attributes\Options\Ignore;
use Attributes\Serialization\Exceptions\SerializeException;

trait SerializableTrait
{
    #[Ignore]
    protected ?Serializable $serializer = null;

    /**
     * @throws SerializeException
     */
    public function serialize(bool $useIgnores = true, bool $useValidation = false, bool $useVisibilityMethods = true, bool $byAlias = true): mixed
    {
        if ($this->serializer === null) {
            $this->serializer = new Serializer;
            if (function_exists('apply_filters')) {
                $this->serializer = apply_filters('fastendpoints_serializer', $this->serializer);
            }
        }

        return $this->serializer->serialize($this, useIgnores: $useIgnores, useValidation: $useValidation, useVisibilityMethods: $useVisibilityMethods, byAlias: $byAlias);
    }
}
