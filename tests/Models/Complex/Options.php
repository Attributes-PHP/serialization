<?php

namespace Attributes\Serialization\Tests\Models\Complex;

use Attributes\Options;

class Post
{
    #[Options\Ignore(validation: false)]
    public int|string $myPostId;

    #[Options\Alias('my_title')]
    public string $title;
}

class Profile
{
    public Post $myPost;
}

#[Options\AliasGenerator('snake')]
class User
{
    public Profile $profile;

    public string $fullName;
}
