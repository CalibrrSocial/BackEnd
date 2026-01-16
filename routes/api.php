<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;

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

Route::group(['prefix' => 'auth'], function () {
  Route::post('/register', [AuthController::class, 'register'])->name('register');
  Route::post('/login', [AuthController::class, 'login'])->name('login');
  Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
  Route::post('/forgotpassword', [AuthController::class, 'forgotPasswordSendMail'])->name('forgotPasswordSendMail');

  Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/changepassword', [AuthController::class, 'changePassword'])->name('changePasswordForgot');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
  });
});

Route::group(['prefix' => 'profile'], function () {
  Route::group(['middleware' => 'auth:api'], function () {
    //Profile Information
    Route::get('/{id}', [ProfileController::class, 'getUser'])->name('getUser');
    Route::post('/{id}', [ProfileController::class, 'updateUserProfile'])->name('updateUserProfile');
    Route::delete('/{id}', [ProfileController::class, 'removeUserAccount'])->name('removeUserAccount');
    Route::post('/{id}/location', [ProfileController::class, 'updateUserLocation'])->name('updateUserLocation');

    //Relationship Information
    Route::get('/{id}/relationships', [ProfileController::class, 'getUserRelationships'])->name('getUserRelationships');
    Route::get('/{id}/relationships/{friendId}', [ProfileController::class, 'getRelationship'])->name('getRelationship');
    Route::put('/{id}/relationships/{friendId}', [ProfileController::class, 'requestFriend'])->name('requestFriend');
    Route::post('/{id}/relationships/{friendId}', [ProfileController::class, 'updateFriend'])->name('updateFriend');
    Route::delete('/{id}/relationships/{friendId}', [ProfileController::class, 'unblockAndDeleteUserRelationship'])->name('unblockAndDeleteUserRelationship');

    //Like Information
    Route::get('/{id}/likes', [ProfileController::class, 'getLikes'])->name('getLikes');
    Route::post('/{id}/likes', [ProfileController::class, 'likeProfile'])->name('likeProfile');
    Route::delete('/{id}/likes', [ProfileController::class, 'unlikeProfile'])->name('unlikeProfile');

    //Report Information
    Route::get('/{id}/reports', [ProfileController::class, 'getUserReports'])->name('getUserReports');
    Route::put('/{id}/reports', [ProfileController::class, 'reportUser'])->name('reportUser');
  });
});

Route::group(['prefix' => 'search'], function () {
  Route::post('/distance', [SearchController::class, 'searchByDistance'])->name('searchByDistance');
  Route::post('/name', [SearchController::class, 'searchByName'])->name('searchByName');
});
