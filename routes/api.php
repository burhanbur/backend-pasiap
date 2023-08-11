<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::group([ 
    'middleware' => [
        // 'auth.jwt',
        'verified'
    ]
], function () {
    // categories
    Route::get('categories', [CategoryController::class, 'getAllCategories']);

    Route::post('refresh', [AuthController::class, 'refreshToken']);
    Route::post('logout', [AuthController::class, 'logout']);
});
