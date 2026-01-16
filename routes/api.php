<?php

use App\Http\Controllers\ApiInfoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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


Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

Route::group(['prefix' => 'auth'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
