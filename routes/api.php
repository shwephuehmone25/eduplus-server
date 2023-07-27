<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Teacher\MeetingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Teacher\GoogleAuthController;

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

/*Teacher Routes*/
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::post('/meeting/create', [MeetingController::class,'create']);

/*User Routes*/
Route::post('/phone/register', [AuthController::class, 'getStart']);
Route::post('/otp/verify', [AuthController::class, 'verify']);
Route::post('/user/create', [AuthController::class, 'createUser']);

/**Admin Routes*/
Route::post('admin/register', [AuthController::class, 'registerAsAdmin']);
Route::post('admin/login', [LoginController::class, 'loginAsAdmin']);