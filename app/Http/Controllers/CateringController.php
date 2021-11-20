<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CateringController extends Controller
{
    /**
     * Get all caterings, literally all
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $caterings = User::with(['schedules' => function ($query) {
            $query->where('date', '>', Carbon::now());
        }])
            ->where('role', 1)
            ->get();

        foreach ($caterings as $catering) {
            $total = 0;
            $count = 0;

            foreach ($catering->schedules as $schedule) {
                $total += $schedule->price;
                $count++;
            }

            $catering->average_price = $count > 0 ? $total / $count : 0;

            unset($catering->schedules);
            unset($catering->balance);
        }

        return ResponseHelper::response(
            "Successfully get all caterings",
            200,
            ["caterings" => $caterings]
        );
    }

    /**
     * Get caterings within range
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getNearBy(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $caterings = User::with(['schedules' => function ($query) {
            $query->where('date', '>', Carbon::now());
        }])
            ->select()
            ->addSelect(
                DB::raw("6371000 * acos(cos(radians(" . $latitude . "))
                * cos(radians(users.latitude))
                * cos(radians(users.longitude) - radians(" . $longitude . "))
                + sin(radians(" . $latitude . "))
                * sin(radians(users.latitude))) AS distance")
            )
            ->where('role', 1)
            ->get();

        foreach ($caterings as $key => $catering) {
            if ($catering->distance > $catering->radius) {
                unset($caterings[$key]);
                continue;
            }

            $total = 0;
            $count = 0;

            foreach ($catering->schedules as $schedule) {
                $total += $schedule->price;
                $count++;
            }

            $catering->average_price = $count > 0 ? $total / $count : 0;

            unset($catering->schedules);
            unset($catering->balance);
        }

        return ResponseHelper::response("Successfully get nearby caterings", 200, ['caterings' => $caterings->values()]);
    }

    /**
     * Get best caterings within range
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getFeatured(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $caterings = User::with(['schedules.orders' => function ($query) {
            $query->where('rating', '!=', 'NULL');
        }])
            ->select()
            ->addSelect(
                DB::raw("6371000 * acos(cos(radians(" . $latitude . "))
                * cos(radians(users.latitude))
                * cos(radians(users.longitude) - radians(" . $longitude . "))
                + sin(radians(" . $latitude . "))
                * sin(radians(users.latitude))) AS distance")
            )
            ->where('role', 1)
            ->get();

        foreach ($caterings as $key => $catering) {
            if ($catering->distance > $catering->radius) {
                unset($caterings[$key]);
                continue;
            }

            $totalRating = 0;
            $totalPrice = 0;
            $countRating = 0;
            $countPrice = 0;

            foreach ($catering->schedules as $schedule) {
                $totalPrice += $schedule->price;
                $countPrice++;
                foreach ($schedule->orders as $order) {
                    $totalRating += $order->rating;
                    $countRating++;
                }
            }

            $catering->average_price = $countPrice > 0 ? $totalPrice / $countPrice : 0;
            $catering->average_rating = $countRating > 0 ? $totalRating / $countRating : 0;

            unset($catering->schedules);
            unset($catering->balance);
        }

        $caterings = $caterings->sortByDesc('average_rating');

        return ResponseHelper::response("Successfully get best caterings nearby", 200, ['caterings' => $caterings->values()]);
    }

    public function getCategoryImage($fileName)
    {
        $file = storage_path('app/public/caterings') . '/' . $fileName;
        if (file_exists($file)) {
            $file = file_get_contents($file);
            return response($file, 200)->header('Content-Type', 'images');
        }

        return abort(404);
    }
}
