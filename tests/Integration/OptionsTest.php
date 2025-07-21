<?php

/**
 * Holds integration tests for serializers
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Serialization\Tests\Integration;

use Attributes\Options\Alias;
use Attributes\Options\AliasGenerator;
use Attributes\Options\Ignore;
use Attributes\Serialization\SerializableTrait;
use Attributes\Serialization\Serializer;
use Attributes\Serialization\Tests\Models as Models;

// Ignores

test('Serialize with options', function (): void {
    $post = new Models\Complex\Post;
    $post->myPostId = 1;
    $post->title = 'My post title';

    $profile = new Models\Complex\Profile;
    $profile->myPost = $post;

    $user = new Models\Complex\User;
    $user->profile = $profile;
    $user->fullName = 'Andre Gil';

    $serializer = new Serializer;
    $data = $serializer->serialize($user);

    expect($data)
        ->toBeArray()
        ->toBe([
            'profile' => [
                'my_post' => [
                    'my_title' => 'My post title',
                ],
            ],
            'full_name' => 'Andre Gil',
        ]);
})
    ->group('serializer', 'options');

// Using options

test('Serialize by ignoring ignores', function (): void {
    $model = new class
    {
        use SerializableTrait;

        #[Ignore(validation: false)]
        public string $validation = 'validation';

        #[Ignore(serialization: false)]
        public string $serialization = 'serialization';
    };

    expect($model->serialize(useIgnores: false))
        ->toBeArray()
        ->toBe([
            'validation' => 'validation',
            'serialization' => 'serialization',
            'serializer' => [
                'allVisibilityMethods' => [],
                'allow' => ['public', 'private', 'protected'],
                'datetimeFormat' => 'Y-m-d\TH:i:sP',
                'defaultAliasGenerator' => [],
            ],
        ]);
})
    ->group('serializer', 'options');

test('Serialize by ignoring visibility methods', function (bool $allowPublic, bool $allowProtected, bool $allowPrivate): void {
    $allow = [];
    $allow = array_merge($allow, $allowPublic ? ['public'] : []);
    $allow = array_merge($allow, $allowProtected ? ['protected'] : []);
    $allow = array_merge($allow, $allowPrivate ? ['private'] : []);

    $serializer = new Serializer(allow: $allow);
    $model = new class
    {
        public string $public = 'public';

        protected string $protected = 'protected';

        private string $private = 'private';
    };

    expect($serializer->serialize($model, useVisibilityMethods: false))
        ->toBeArray()
        ->toBe([
            'public' => 'public',
            'protected' => 'protected',
            'private' => 'private',
        ]);
})
    ->with([true, false])
    ->with([true, false])
    ->with([true, false])
    ->group('serializer', 'options');

test('Serialize validation properties', function (): void {
    $model = new class
    {
        use SerializableTrait;

        #[Ignore(validation: false)]
        public string $validation = 'validation';

        #[Ignore(serialization: false)]
        public string $serialization = 'serialization';
    };

    expect($model->serialize(useValidation: true))
        ->toBeArray()
        ->toBe([
            'validation' => 'validation',
        ]);
})
    ->group('serializer', 'options');

test('Serialize without alias', function (): void {
    $model = new #[AliasGenerator('snake')] class
    {
        use SerializableTrait;

        #[Alias('Random')]
        public string $camelCase = 'camelCase';

        public string $secondCamelCase = 'secondCamelCase';
    };

    expect($model->serialize(byAlias: false))
        ->toBeArray()
        ->toBe([
            'camelCase' => 'camelCase',
            'secondCamelCase' => 'secondCamelCase',
        ]);
})
    ->group('serializer', 'options');
