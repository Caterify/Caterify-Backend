<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    private $namaPaket = ["Paket Hemat", "Paket Standar", "Value Set"];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users = User::where('role', 1)->get();
        $count = count($users);
        $index = rand(0, $count - 1);
        $user = $users[$index];
        $image = $this->faker->image('storage/app/public/menus');
        $image = explode('/', $image)[4];

        return [
            'name' => $this->getName($user->id),
            'image' => $image,
            'description' => $this->faker->realText(200),
            'user_id' => $user
        ];
    }

    private function getName($userId)
    {
        $menu = $this->namaPaket[array_rand($this->namaPaket)] . ' ' . rand(1, 4);

        $exists = Menu::where('user_id', $userId)->where('name', $menu)->exists();
        if ($exists) {
            return $this->getName($userId);
        }

        return $menu;
    }
}
