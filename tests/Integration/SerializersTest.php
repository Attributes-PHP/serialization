<?php

/**
 * Holds integration tests for serializers
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Serialization\Tests\Integration;

use Attributes\Serialization\Exceptions\SerializeException;
use Attributes\Serialization\SerializableTrait;
use Attributes\Serialization\Serializer;
use Attributes\Serialization\Tests\Models as Models;
use DateTime;
use DateTimeInterface;
use stdClass;
use UnitEnum;

// Nested

test('Serialize nested', function (string $datetimeFormat): void {
    $user = new Models\Nested\User;
    $user->userType = Models\Nested\UserType::GUEST;
    $user->profile = new Models\Nested\Profile;
    $user->profile->firstName = 'Andre';
    $user->profile->lastName = 'Gil';
    $user->profile->post = new Models\Nested\Post;
    $user->profile->post->id = 1;
    $user->profile->post->title = 'My post title';

    $serializer = new Serializer(datetimeFormat: $datetimeFormat);
    $data = $serializer->serialize($user);
    expect($data)
        ->toBeArray()
        ->toMatchArray([
            'profile' => [
                'id' => $user->profile->id,
                'firstName' => 'Andre',
                'lastName' => 'Gil',
                'post' => [
                    'id' => 1,
                    'title' => 'My post title',
                    'published' => $user->profile->post->published->format($datetimeFormat),
                ],
            ],
            'userType' => 'guest',
            'createdAt' => $user->createdAt->format($datetimeFormat),
        ]);

})
    ->with([DateTimeInterface::ATOM, DateTimeInterface::COOKIE])
    ->group('serializer', 'nested');

// Array object

test('Serialize array object', function (): void {
    $listOfPosts = new Models\Complex\PostsArr;
    for ($i = 0; $i < 5; $i++) {
        $post = new Models\Nested\Post;
        $post->id = $i;
        $post->title = 'My post title';
        $post->published = 10;
        $listOfPosts[] = $post;
    }

    $serializer = new Serializer;
    $data = $serializer->serialize($listOfPosts);
    expect($data)
        ->toBeArray()
        ->toMatchArray([
            [
                'id' => 0,
                'title' => 'My post title',
                'published' => 10,
            ],
            [
                'id' => 1,
                'title' => 'My post title',
                'published' => 10,
            ],
            [
                'id' => 2,
                'title' => 'My post title',
                'published' => 10,
            ],
            [
                'id' => 3,
                'title' => 'My post title',
                'published' => 10,
            ],
            [
                'id' => 4,
                'title' => 'My post title',
                'published' => 10,
            ],
        ]);

})
    ->group('serializer', 'array-object');

// Raw array

test('Serialize raw array with objects', function (): void {
    $rawArrayOfPosts = [];
    for ($i = 0; $i < 2; $i++) {
        $post = new Models\Nested\Post;
        $post->id = $i;
        $post->title = 'My post title';
        $post->published = 10 + $i;
        $rawArrayOfPosts[] = $post;
    }
    $value = new class
    {
        public array $arrayOfPosts;
    };
    $value->arrayOfPosts = $rawArrayOfPosts;

    $serializer = new Serializer;
    $data = $serializer->serialize($value);
    expect($data)
        ->toBeArray()
        ->toMatchArray(['arrayOfPosts' => [
            [
                'id' => 0,
                'title' => 'My post title',
                'published' => 10,
            ],
            [
                'id' => 1,
                'title' => 'My post title',
                'published' => 11,
            ],
        ]]);
})
    ->group('serializer', 'array');

test('Serialize nested raw array\'s', function (): void {
    $rawArrayOfPosts = [];
    for ($i = 0; $i < 2; $i++) {
        $post = new Models\Nested\Post;
        $post->id = $i;
        $post->title = 'My post title';
        $post->published = 10 + $i;
        $rawArrayOfPosts[] = $post;
    }
    $value = new class
    {
        public array $nestedArray;
    };
    $value->nestedArray = [
        'string' => 'string',
        'nested' => [
            'number' => 123,
            'nested' => $rawArrayOfPosts,
        ],
    ];

    $serializer = new Serializer;
    $data = $serializer->serialize($value);
    expect($data)
        ->toBeArray()
        ->toMatchArray(['nestedArray' => [
            'string' => 'string',
            'nested' => [
                'number' => 123,
                'nested' => [
                    [
                        'id' => 0,
                        'title' => 'My post title',
                        'published' => 10,
                    ],
                    [
                        'id' => 1,
                        'title' => 'My post title',
                        'published' => 11,
                    ],
                ],
            ],
        ]]);
})
    ->group('serializer', 'array');

// Raw array

test('Serialize raw object', function (): void {
    $value = new StdClass;
    $value->id = 1;
    $value->title = 'My post title';

    $serializer = new Serializer;
    $data = $serializer->serialize($value);
    expect($data)
        ->toBeArray()
        ->toMatchArray([
            'id' => 1,
            'title' => 'My post title',
        ]);
})
    ->group('serializer', 'object');

// DateTime

test('Serialize datetime', function (string $datetimeFormat): void {
    $datetime = new DateTime('2025-03-31T18:00:00+00:00');
    $serializer = new Serializer(datetimeFormat: $datetimeFormat);
    $data = $serializer->serialize($datetime);
    expect($data)
        ->toBeString()
        ->toBe($datetime->format($datetimeFormat));

})
    ->with([DateTimeInterface::ATOM, DateTimeInterface::COOKIE])
    ->group('serializer', 'datetime');

// Enum

test('Serialize enum', function (UnitEnum $enumValue, string|int $expectedValue): void {
    $serializer = new Serializer;
    $data = $serializer->serialize($enumValue);
    expect($data)->toBe($expectedValue);

})
    ->with([
        [Models\Basic\Enum::ADMIN, 'ADMIN'],
        [Models\Basic\IntEnum::GUEST, 1],
        [Models\Basic\StrEnum::ADMIN, 'admin'],
    ])
    ->group('serializer', 'enum');

// Uninitialized values

test('Serialize with uninitialized values', function (): void {
    $serializer = new Serializer;
    $data = $serializer->serialize(new class
    {
        private string $hello;
    });
    expect($data)
        ->toBeArray()
        ->toBeEmpty();

})
    ->group('serializer', 'uninitialized-values');

// Visibility

test('Serialize only properties of a given visibility', function (bool $public, bool $private, bool $protected): void {
    $allow = [];
    if ($public) {
        $allow[] = 'public';
    }
    if ($private) {
        $allow[] = 'private';
    }
    if ($protected) {
        $allow[] = 'protected';
    }

    $serializer = new Serializer(allow: $allow);
    $data = $serializer->serialize(new class
    {
        public string $public = 'public';

        private string $private = 'private';

        protected string $protected = 'protected';
    });

    $expectedData = [];
    $allVisibilities = $allow ?: ['public', 'private', 'protected'];
    foreach ($allVisibilities as $visibility) {
        $expectedData[$visibility] = $visibility;
    }

    expect($data)
        ->toBeArray()
        ->toBe($expectedData);
})
    ->with([true, false])
    ->with([true, false])
    ->with([true, false])
    ->group('serializer', 'visibility');

test('Invalid property visibility', function (mixed $value): void {
    $serializer = new Serializer(allow: ['public', $value]);
    $serializer->serialize(new class
    {
        public string $value = 'value';
    });
})
    ->with(['invalid', 123, false, null, 9.1])
    ->throws(SerializeException::class)
    ->group('serializer', 'visibility');

// Serializable trait

test('Serialize with trait', function (): void {
    $model = new class
    {
        use SerializableTrait;

        public int $id = 10;

        public string $name = 'Andre';
    };

    expect($model->serialize())
        ->toBeArray()
        ->toBe([
            'id' => 10,
            'name' => 'Andre',
        ]);
})
    ->group('serializer', 'trait');
