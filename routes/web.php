<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/vieon',function (){
    return view('get-vieon');
});
Route::get('/iot',function (){
    return view('iot');
});

Route::get('/test-schedule',function (){
    $user = \App\Models\User::query()->first();
    dd($user);
   dd("a");
});


Route::group([
    'prefix' => 'calls'
],function (){
    Route::get("/",[\App\Http\Controllers\CallController::class,'index']);
    Route::get("/send-notification",[\App\Http\Controllers\CallController::class,'sendNotification'])->name('call.send-notification');
    Route::get('/answer',[\App\Http\Controllers\CallController::class,'answer']);
    Route::delete('/{id}',[\App\Http\Controllers\CallController::class,'destroyCall'])->name('call.destroy');
});
