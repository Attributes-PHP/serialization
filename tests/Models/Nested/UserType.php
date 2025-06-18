<?php

namespace Attributes\Serialization\Tests\Models\Nested;

enum UserType: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case GUEST = 'guest';
}
