<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\YoutubeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::controller(YoutubeController::class)
    ->prefix('youtube')
    ->middleware('api')
    ->group(function (){
        Route::get('/new','videoNew');
        Route::get('/link-video','linkVideo');
    });

Route::controller(AuthController::class)
    ->group(function (){
        Route::post('/login','attempt');
    });
