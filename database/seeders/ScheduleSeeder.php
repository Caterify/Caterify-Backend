<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::with('menus')->where('role', 1)->get();
        foreach ($users as $user) {
            $menus = $user->menus;
            $count = count($menus);

            if ($count == 0)
                continue;

            $start = Carbon::now()->firstOfMonth();
            $end = Carbon::now()->endOfMonth();

            while ($start <= $end) {
                $index = rand(0, $count - 1);

                Schedule::create([
                    'menu_id' => $menus[$index]->id,
                    'date' => $start,
                    'price' => rand(5, 50) * 1000
                ]);

                $start->addDay();
            }
        }
    }
}
