<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function getSchedulesFromCaterings(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $schedules = Schedule::with('menu.user')->where('date', '=', $request->date)->get();

        return ResponseHelper::response(
            "Successfully get all menus on $request->date",
            200,
            ['schedules' => $schedules]
        );
    }

    public function getScheduleFromSpecificCatering($id, Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $catering = User::where('role', 1)->findOrFail($id);
        $schedule = Schedule::with('menu')
            ->whereHas('menu', function ($query) use ($catering) {
                $query->where('user_id', $catering->id);
            })
            ->where('date', '=', $request->date)
            ->first();
        return ResponseHelper::response(
            "Successfully get schedule of $catering->name on $request->date",
            200,
            ['schedule' => $schedule]
        );
    }

    public function getSchedulesFromDate($id, Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $start = $request->start;
        $end = $request->end;

        $catering = User::where('role', 1)->findOrFail($id);
        $schedules = Schedule::with('menu')
            ->whereHas('menu', function ($query) use ($catering) {
                $query->where('user_id', $catering->id);
            })
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $totalPrice = 0;
        foreach ($schedules as $schedule) {
            $totalPrice += $schedule->price;
        }

        return ResponseHelper::response(
            "Successfully get schedules of $catering->name on $start to $end",
            200,
            [
                'total' => count($schedules),
                'total_price' => $totalPrice,
                'schedules' => $schedules
            ]
        );
    }
}
