<?php

/**
 * Holds interface for converting models into an array of primitive types
 */

declare(strict_types=1);

namespace Attributes\Serialization;

use Attributes\Serialization\Exceptions\SerializeException;

interface Serializable
{
    /**
     * Converts a given class instance into a primitive type
     *
     * @param  object  $model  - Model to serialize
     * @param  bool  $useIgnores  - Determines if we should rely on Options\Ignore to serialize the fields needed. Default true
     * @param  bool  $useValidation  - Determines if we want only the properties that were marked as validation. Default false
     * @param  bool  $useVisibilityMethods  - Determines if we should rely on the visibility methods to serialize the fields needed. Default true
     * @param  bool  $byAlias  - Determines if we should rely on the alias for the fields names. Default true
     *
     * @returns mixed
     *
     * @throws SerializeException If unable to serialize value
     */
    public function serialize(object $model, bool $useIgnores = true, bool $useValidation = false, bool $useVisibilityMethods = true, bool $byAlias = true): mixed;
}
