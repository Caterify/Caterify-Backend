<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function getActiveOrders(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;
        $user = Auth::user();

        $orders = Order::whereHas('schedule', function ($query) use ($user, $date) {
            $query->where('date', '=', $date)->wherehas('menu', function ($query1) use ($user) {
                $query1->where('user_id', $user->id);
            });
        })
            ->with('schedule.menu')
            ->with('user')
            ->get();

        return ResponseHelper::response(
            "Successfully get all orders on $date",
            200,
            [
                "total" => count($orders),
                "orders" => $orders
            ]
        );
    }

    public function getAllOrders()
    {
        $user = Auth::user();
        $orders = null;

        if ($user->role == 1) {
            $orders = Order::whereHas('schedule', function ($query) use ($user) {
                $query->whereHas('menu', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            })
            ->with('schedule.menu')
            ->with('user')
            ->get();
        } else {
            $orders = Order::where('user_id', $user->id)->with('schedule.menu.user')->get();
        }

        return ResponseHelper::response(
            "Successfully get all orders",
            200,
            [
                "total" => count($orders),
                "orders" => $orders
            ]
        );
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'catering_id' => 'required|numeric'
        ]);

        $start = $request->start;
        $end = $request->end;
        $userId = Auth::id();
        $id = $request->catering_id;

        $catering = User::where('role', 1)->findOrFail($id);

        $schedules = Schedule::whereHas('menu', function ($query) use ($id) {
            $query->where('user_id', $id);
        })
            ->whereBetween('date', [$start, $end])
            ->get();

        $result = DB::transaction(function () use ($schedules, $userId) {
            try {
                foreach ($schedules as $schedule) {
                    $order = new Order();
                    $order->schedule_id = $schedule->id;
                    $order->user_id = $userId;
                    $order->status = 0;
                    $order->save();
                }

                return ResponseHelper::response(
                    "Successfully create orders",
                    201
                );
            } catch (Exception $e) {
                DB::rollBack();
                return ResponseHelper::response($e->getMessage(), 500);
            }
        });

        return $result;
    }
}
