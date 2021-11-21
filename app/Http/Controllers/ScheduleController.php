<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Menu;
use App\Models\Schedule;
use App\Models\User;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getScheduleFromCateringSide(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $catering = Auth::user();
        $schedule = Schedule::with('menu')
            ->whereHas('menu', function ($query) use ($catering) {
                $query->where('user_id', $catering->id);
            })
            ->where('date', '=', $request->date)
            ->first();

        return ResponseHelper::response(
            "Successfully get schedule of your catering on $request->date",
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

    public function getSchedulesWithMenus()
    {
        $user = Auth::user();
        $schedules = Schedule::with(['orders', 'menu'])->whereHas('menu', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return ResponseHelper::response("Successfully get all schedules", 200, ['schedules' => $schedules]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'menu_id' => 'required|numeric',
            'price' => 'required|numeric'
        ]);

        $date = $request->date;
        $price = $request->price;
        $menuId = $request->menu_id;
        $catering = Auth::user();

        $authorized = Menu::where('user_id', $catering->id)->findOrFail($menuId);

        $exists = Schedule::where('date', $date)->whereHas('menu', function ($query) use ($catering) {
            $query->where('user_id', $catering->id);
        })->exists();

        if ($exists) {
            $responses = [
                'message' => "The given data was invalid",
                'errors' => [
                    'date' => ["You already set menu for this day."]
                ],
            ];

            return response()->json($responses, 422);
        }

        $schedule = new Schedule();
        $schedule->date = $date;
        $schedule->price = (int) $price;
        $schedule->menu_id = $menuId;

        if ($schedule->save()) {
            $schedule->load(['menu']);

            return ResponseHelper::response(
                "Successfully create schedule",
                201,
                ['schedule' => $schedule]
            );
        }

        return ResponseHelper::response(
            "Something went wrong",
            500
        );
    }
}
