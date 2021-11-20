<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', 2)->get();
        $today = Carbon::now();

        foreach ($users as $user) {
            $catering = User::with('schedules')->where('role', 1)->inRandomOrder()->first();

            foreach ($catering->schedules as $schedule) {
                $rating = $schedule->date < $today ? rand(1,5) : null;
                $status = $schedule->date < $today ? 2 : 0;
                Order::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'status' => $status
                ]);

                $updateCatering = $schedule->menu->user;
                $updateCatering->balance += $schedule->price;
                $updateCatering->update();
            }
        }
    }
}
