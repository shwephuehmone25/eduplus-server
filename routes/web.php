<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VideoController;

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

Route::get('video/{id}', [VideoController::class, 'show']);
Route::get('video/', [VideoController::class, 'VideoUpload'])->name('video.upload');

Route::post('video/store', [VideoController::class, 'store'])->name('video.store');