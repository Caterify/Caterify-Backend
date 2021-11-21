<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('docs');
});

Route::get('/try', function () {
    return redirect('https://testflight.apple.com/join/3iYlzjcw');
});

Route::get('/figma', function () {
    return redirect('https://www.figma.com/file/WognDOSpMbX1yTHgRgmU8u/Caterify');
});

Route::get('/images/placeholder', function () {
    $path = resource_path('/image/placeholder.jpeg');
    if (file_exists($path)) {
        $file = file_get_contents($path);
        return response($file, 200)->header('Content-Type', 'images');
    }

    return ResponseHelper::response("Image not found", 404);
});

Route::get('/images/caterings/{fileName}', [CateringController::class, 'getCategoryImage']);
Route::get('/images/menus/{fileName}', [MenuController::class, 'getMenuImage']);

Route::view('/docs', 'documentation')->name('docs');
