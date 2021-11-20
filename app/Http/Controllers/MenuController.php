<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function getFromCatering($id)
    {
        $catering = User::where('id', $id)->where('role', 1)->firstOrFail();
        $menus = Menu::with('orders')->where('user_id', $id)->get();

        foreach ($menus as $menu) {
            $rating = 0;
            $count = 0;

            foreach ($menu->orders as $order) {
                if ($order->rating) {
                    $rating += $order->rating;
                    $count++;
                }
            }

            $menu->rating = $count == 0 ? 0 : $rating / $count;
        }
        return ResponseHelper::response(
            "Successfully get all menus from $catering->name",
            200,
            ['menus' => $menus]
        );
    }

    public function getMenuImage($fileName)
    {
        $file = storage_path('app/public/menus') . '/' . $fileName;
        if (file_exists($file)) {
            $file = file_get_contents($file);
            return response($file, 200)->header('Content-Type', 'images');
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required'
        ]);

        $user = Auth::id();

        $menu = new Menu();
        $menu->name = $request->name;
        $menu->description = $request->description;
        $menu->user_id = $user;

        if ($menu->save()) {
            return ResponseHelper::response(
                "Successfully create menu",
                201,
                ['menu' => $menu]
            );
        }
    }
}
