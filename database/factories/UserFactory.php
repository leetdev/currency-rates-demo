<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    static $password;

    return [
        'provider' => 'google',
        'provider_id' => (string)mt_rand() * mt_rand(),
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'avatar' => trim(str_replace('.html', '', $faker->url), '/') . '/' . mt_rand() . '/' . mt_rand() . '.jpg',
        'remember_token' => str_random(10),
    ];
});
