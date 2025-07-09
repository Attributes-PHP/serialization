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
    public function serialize(): mixed
    {
        if ($this->serializer === null) {
            $this->serializer = new Serializer;
        }

        return $this->serializer->serialize($this);
    }
}
