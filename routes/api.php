<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\GoogleAuthController;


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

/*Teacher Route*/
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::post('/meeting/create', [MeetingController::class,'create']);

/*User Route*/
Route::post('/phone/register', [AuthController::class, 'getStart'])->name('phone.register');
Route::post('/otp/verify', [AuthController::class, 'verify'])->name('otp.verify');
Route::post('/user/create', [AuthController::class, 'createUser'])->name('user.create');

