<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
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
    ->middleware('auth')
    ->group(function (){
        Route::get('/new','videoNew');
        Route::get('/link-video','linkVideo');
        Route::get('/suggest','suggest');
        Route::get('/search','search');
        Route::get('/suggest-by-video-id','videoSuggest');
    });

Route::controller(PostController::class)
    ->prefix('posts')
    ->middleware('auth')
    ->group(function (){
        Route::get('/','index');
    });

Route::controller(AuthController::class)
    ->group(function (){
        Route::post('/login','attempt');
    });

Route::post('/attachment',[\App\Http\Controllers\Api\AttachmentController::class,'create'])->middleware('auth');
