<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AuthController;

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

// Route::get('/products', function () {
//     return 'products';
// });

Route::group(['prefix' => 'v1'], function () {
    Route::resource('meeting', MeetingController::class)->except([
        'edit', 'create'
    ]);
    
    Route::resource('meeting/registration', RegistrationController::class)->only([
        'store', 'update', 'destroy'
    ]);
    
    Route::post('user', [AuthController::class, 'store']);
    
    Route::post('user/signin', [AuthController::class, 'signin']);
});
