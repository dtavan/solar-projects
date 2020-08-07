<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;

use App\Models\SolarProject;

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

$factory->define(SolarProject::class, function (Faker $faker) {
    $maxLat = -33.495231;
    $maxLon = 151.254283;
    $minLat = -34.118263;
    $minLon = 150.619823;
    $created = $faker->dateTimeBetween('-2 years', 'now');
    $person = $faker->name;
    $address = $faker->streetAddress;
    $company = $faker->company . ' ' . $faker->companySuffix;
    $title = $faker->randomElement([$person, $address, $company]);
    $systemDetails = implode(', ', [
        $faker->randomElement(['Longi', 'SunPower', 'Jinko', 'LG NeON']) . ' panels',
        $faker->randomElement(['SolaX', 'ABB', 'Fronius', 'Siemens']) . ' inverter',
        $faker->randomElement(['Tesla PowerWall', 'LG Chem', 'Redflow', 'Samsung']) . ' battery',
    ]);
    return [
        'uuid' => $faker->uuid,
        'system_size' => $faker->randomElement([null, $faker->randomFloat(2, 1, 99)]),
        'system_details' => $systemDetails,
        'site_latitude' => $faker->randomFloat(null, $minLat, $maxLat),
        'site_longitude' => $faker->randomFloat(null, $minLon, $maxLon),
        'title' => $title,
        'created_at' => $created,
        'updated_at' => $faker->dateTimeBetween($created, 'now'),
    ];
});
