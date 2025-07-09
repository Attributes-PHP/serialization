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
     *
     * @returns mixed
     *
     * @throws SerializeException If unable to serialize value
     */
    public function serialize(object $model): mixed;
}
