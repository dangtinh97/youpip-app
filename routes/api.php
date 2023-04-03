<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
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
        Route::get('/feed','postMe');
        Route::post('/','create');
        Route::post("/{id}/reaction",'reaction');
        Route::delete("/{id}",'delete');
        Route::delete("/{id}",'show');
        Route::post("/{id}/comment",'comment');
        Route::get("/{id}/comment",'listComment');
    });

Route::controller(AuthController::class)
    ->group(function (){
        Route::post('/login','attempt');
    });

Route::post('/attachment',[\App\Http\Controllers\Api\AttachmentController::class,'create'])->middleware('auth');

Route::controller(ChatController::class)
    ->prefix('/chats')
    ->middleware('auth')
    ->group(function (){
        Route::get('/','listChat');
        Route::get("/chat-gpt",'chatGpt');
        Route::get('/join-room','joinRoom');
        Route::get('/{id}','message');
        Route::post('/{id}','sendMessage');
    });
