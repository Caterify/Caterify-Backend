<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

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
        $kasihImageJangan = rand(0,1);
        $image = $kasihImageJangan == 1 ? $this->getImages() : null;

        return [
            'name' => $this->getName($user->id),
            'image' => $image,
            'description' => $this->faker->realText(200),
            'user_id' => $user
        ];
    }

    public function getImages()
    {
        $imageName = rand(1, 9) . ".jpg";
        $fullPath = resource_path("image/menus/") .  $imageName;

        $destinationPath = storage_path('app/public/menus/') . $imageName;

        if (!file_exists($destinationPath)) {
            File::copy($fullPath, $destinationPath);
        }

        return $imageName;
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
