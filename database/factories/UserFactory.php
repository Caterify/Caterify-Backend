<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $role = rand(1, 2);
        $address = $this->faker->address();
        $radius = $role == 1 ? rand(5,30) * 1000 : null;
        $latitude = $this->faker->latitude(-6.38, -6.111);
        $longitude = $this->faker->longitude(106.489, 106.918);
        $kasihImageJangan = rand(0,1);
        $image = $kasihImageJangan == 1 && $role == 1 ? $this->faker->image('storage/app/public/caterings') : null;
        $image = $image ? explode('/', $image)[4] : null;

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => $this->faker->e164PhoneNumber(),
            'password' => Hash::make('admin123'),
            'image' => $image,
            'role' => $role,
            'balance' => 0,
            'remember_token' => Str::random(10),
            'address' => $address,
            'radius' => $radius,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
