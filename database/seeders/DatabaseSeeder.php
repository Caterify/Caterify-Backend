<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(40)->create();
        Menu::factory(40)->create();
        $this->call(ScheduleSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
