<?php

/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */

use App\Entities\Groups\Models\Group;

$factory->define(Group::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name(),
        'alias' => $faker->name(),
        'is_default' => false,
        'is_staff' => false,
        'is_admin' => false,
        'discourse_name' => $faker->name(),
    ];
});

$factory->state(Group::class, 'as-default', [
    'is_default' => true,
]);