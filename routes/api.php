<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/temp', function () {
    return env('API_KEY');
});

Route::group(["middleware" => "apiKey"], function () {
    Route::group(["prefix" => "auth"], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

    });

    Route::group(['prefix' => 'public'], function () {
        Route::group(['prefix' => 'caterings'], function () {
            Route::get('/', [CateringController::class, 'getAll']);
            Route::get('/nearby', [CateringController::class, 'getNearBy']);
            Route::get('/featured', [CateringController::class, 'getFeatured']);
        });

        Route::group(['prefix' => 'menus'], function () {
            Route::get('/{id}', [MenuController::class, 'getFromCatering']);
        });

        Route::group(['prefix' => 'schedules'], function () {
            Route::get('/', [ScheduleController::class, 'getSchedulesFromCaterings']);
            Route::get('/catering/{catering_id}', [ScheduleController::class, 'getScheduleFromSpecificCatering']);
            Route::get('/catering/{catering_id}/range', [ScheduleController::class, 'getSchedulesFromDate']);
        });
    });

    Route::group(['prefix' => 'private', 'middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'orders'], function () {
            Route::post('/', [OrderController::class, 'createOrder']);
            Route::get('/', [OrderController::class, 'getAllOrders']);
            Route::get('/active', [OrderController::class, 'getActiveOrders']);
        });

        Route::group(['prefix' => 'menus'], function () {
            Route::post('/', [MenuController::class, 'store']);
        });

        Route::group(['prefix' => 'schedules'], function () {
            Route::post('/', [ScheduleController::class, 'store']);
            Route::get('/', [ScheduleController::class, 'getSchedulesWithMenus']);
            Route::get('/date', [ScheduleController::class, 'getScheduleFromCateringSide']);
        });
    });
});
