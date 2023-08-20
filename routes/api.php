<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DropdownController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;

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

// dropdown
Route::group(['prefix' => 'dropdown'], function () {
    Route::get('religions', [DropdownController::class, 'getAllReligions']);
    Route::get('status', [DropdownController::class, 'getAllStatus']);
    Route::get('sexs', [DropdownController::class, 'getAllSexs']);
    Route::get('marital_status', [DropdownController::class, 'getAllMaritalStatus']);
});

Route::group([ 
    'middleware' => [
        'auth.jwt',
        'verified'
    ]
], function () {
    // firebase
    Route::post('tokens', [UserController::class, 'storeTokenFcm']);

    // categories
    Route::get('categories', [CategoryController::class, 'getAllCategories']);

    // faq
    Route::get('faq', [FaqController::class, 'index']);
    Route::post('faq', [FaqController::class, 'store']);
    Route::put('faq{id}', [FaqController::class, 'update']);
    Route::delete('faq/{id}', [FaqController::class, 'delete']);

    // profiles
    Route::get('profiles', [UserController::class, 'getProfile']);
    Route::post('profiles', [UserController::class, 'updateProfile']);

    // reports
    Route::get('reports', [ReportController::class, 'getReports']);
    Route::get('reports/{id}', [ReportController::class, 'getReportById']);
    Route::get('reports/status/{id}', [ReportController::class, 'getReportByStatus']);
    Route::get('reports/handler/{id}', [ReportController::class, 'getReportByHandler']);
    Route::get('reports/request/{id}', [ReportController::class, 'getReportByRequest']);
    Route::post('reports', [ReportController::class, 'createReport']);
    Route::post('reports/{id}', [ReportController::class, 'updateReport']);

    // update status
    Route::post('reports/status/{id}', [ReportController::class, 'updateReportStatus']);

    // auth
    Route::post('password/{id}', [AuthController::class, 'changePassword']);
    Route::post('refresh', [AuthController::class, 'refreshToken']);
    Route::post('logout', [AuthController::class, 'logout']);
});
