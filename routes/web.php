<?php

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
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('verify/{id}', [App\Http\Controllers\Api\AuthController::class, 'verify'])->name('pendaftaran.verify');
Route::get('verified', [App\Http\Controllers\HomeController::class, 'verified'])->name('verified');
Route::get('password/{encrypted}', [App\Http\Controllers\Api\AuthController::class, 'updatePassword'])->name('update.password');

Route::post('device-key', [App\Http\Controllers\HomeController::class, 'updateDeviceKey'])->name('store.token');
Route::post('send-notification', [App\Http\Controllers\HomeController::class, 'sendNotification'])->name('send.notification');