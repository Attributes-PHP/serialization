# Attributes Serialization

<p align="center">
    <a href="https://codecov.io/gh/Attributes-PHP/serialization"><img alt="Code Coverage" src="https://codecov.io/gh/Attributes-PHP/serialization/graph/badge.svg?token=GtpW0Lrgqq"/></a>
    <a href="https://packagist.org/packages/Attributes-PHP/serialization"><img alt="Latest Version" src="https://img.shields.io/packagist/v/Attributes-PHP/serialization"></a>
    <a href="https://packagist.org/packages/Attributes-PHP/serialization"><img alt="Software License" src="https://img.shields.io/badge/Licence-MIT-brightgreen"></a>
</p>

**Attributes Serialization** is a library that serializes any object into a PHP primitive type

## Features

- Converts any type of object into a primitive type
- Support for alias, alias generators and more via [Attributes](https://github.com/Attributes-PHP/options)

## Requirements

- PHP 8.1+
- [Attributes-PHP/options](https://github.com/Attributes-PHP/options)

We aim to support versions that haven't reached their end-of-life.

## How it works?

```php
<?php

use Attributes\Serialization\Serializer;

class Person
{
    public float|int $age;
    public ?DateTime $birthday;
}

$person = new Person; 
$person->age = 30;
$person->birthday = new DateTime("2023-10-27T14:30:00Z");

$serializer = new Serializer;
$data = $serializer->serialize($person);

var_dump($data);  // array(2) { ["age"]=> int(30) ["birthday"]=> string(25) "2023-10-27T14:30:00+00:00" }
```

## Installation

```bash
composer require attributes-php/serialization
```

Attributes Validation was created by **[André Gil](https://www.linkedin.com/in/andre-gil/)** and is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
