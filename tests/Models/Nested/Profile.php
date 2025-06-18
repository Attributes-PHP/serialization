<?php

namespace Attributes\Serialization\Tests\Models\Nested;

class Profile
{
    public string $id;

    public string $firstName;

    public string $lastName;

    public Post $post;

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function __construct()
    {
        $this->id = uniqid();
    }
}
