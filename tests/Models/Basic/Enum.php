<?php

namespace Attributes\Serialization\Tests\Models\Basic;

enum Enum
{
    case ADMIN;
    case GUEST;
}

enum IntEnum: int
{
    case ADMIN = 0;
    case GUEST = 1;
}

enum StrEnum: string
{
    case ADMIN = 'admin';
    case GUEST = 'guest';
}
