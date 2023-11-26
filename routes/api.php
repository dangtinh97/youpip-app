<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\WorkMemoController;
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
    ->group(function () {
        Route::get('/new', 'videoNew');
        Route::get('/link-video', 'linkVideo');
        Route::get('/suggest', 'suggest');
        Route::get('/search', 'search');
        Route::get('/suggest-by-video-id', 'videoSuggest');
        Route::get('/detail', 'detailVideo');
    });

Route::controller(PostController::class)
    ->prefix('posts')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/feed', 'postMe');
        Route::post('/', 'create');
        Route::post("/{id}/reaction", 'reaction');
        Route::delete("/{id}", 'delete');
        Route::delete("/{id}", 'show');
        Route::post("/{id}/comment", 'comment');
        Route::get("/{id}/comment", 'listComment');
    });

Route::controller(AuthController::class)
    ->group(function () {
        Route::post('/login', 'attempt2');
    });

Route::post('/attachment',
    [\App\Http\Controllers\Api\AttachmentController::class, 'create'])
    ->middleware('auth');
Route::post('/attachments',
    [\App\Http\Controllers\Api\AttachmentController::class, 'store']);
Route::get('/vtv-go/link-play',
    [\App\Http\Controllers\Api\VtvGoController::class, 'linkPlay']);
Route::post('/vieon/link-play',
    [\App\Http\Controllers\Api\VtvGoController::class, 'linkPlay']);
//    ->middleware('auth');

Route::controller(ChatController::class)
    ->prefix('/chats')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', 'listChat');
        Route::get("/chat-gpt", 'chatGpt');
        Route::get('/join-room', 'joinRoom');
        Route::get('/search-user', 'searchUser');
        Route::get('/{id}', 'message');
        Route::post('/{id}', 'sendMessage');
    });

Route::post('/vtv-vieon',
    [\App\Http\Controllers\Api\VtvGoController::class, 'update'])
    ->name('api.vtv-vieon.update');
Route::get('/vtv-vieon-channel',
    [\App\Http\Controllers\Api\VtvGoController::class, 'index'])
    ->name('api.vtv-vieon.list');

Route::controller(ChatbotController::class)
    ->prefix('chatbot')
    ->group(function () {
        Route::get('/webhooks', 'verifyWebhook');
        Route::post('/webhooks', 'webhook');
    });

Route::post('/webhook', [ChatbotController::class, 'webhook']);
Route::post('/token-fcm', [AuthController::class, 'tokenFCM'])
    ->middleware('auth');


Route::post('/work-memo/login', [AuthController::class, 'workMemoLogin']);
Route::prefix('/work-memo')
    ->middleware('auth')
    ->group(function () {
        Route::post('/boards', [WorkMemoController::class, 'storeBoard']);
        Route::get('/boards', [WorkMemoController::class, 'index']);
        Route::post('/boards/{id}/list-work',
            [WorkMemoController::class, 'storeListWork']);
        Route::get('/boards/{id}', [WorkMemoController::class, 'detailBoard']);
        Route::post('/list-work/{id}/works',
            [WorkMemoController::class, 'storeWork']);
    });

Route::get('curl-locamos', function () {
    $data = \App\Models\Log::query()
        ->where(['type' => 'curl-locamos'])
        ->orderByDesc('_id')
        ->limit(10)
        ->get()
        ->toArray();
    return response()->json($data);
});


Route::post("/eat-lunch",[\App\Http\Controllers\EatLunchController::class,'store'])->name('eat-lunch.api');
Route::post('/mophong-score',function (Request $request){

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://mophong.laixehaivan.edu.vn/test/submit/ajax_chamdiem.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('check_chamdiem' => 'yes','stt_cauhoi' => '1','id_cauhoi' => $request->get('question'),'second_traloi' => $request->get('time'),'percent_traloi' => '49.53497775980'),
        CURLOPT_HTTPHEADER => array(
            'Cookie: PHPSESSID=0ff036b0457dc8b4d1a616eae8a87fad'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $res = str_replace(["\n","\r"],"",$response);
    $json = json_decode($res,true);
    \App\Models\SimulatorAns::query()->create([
       'question_id' => (int)$request->get('question'),
        'result_0' => \Illuminate\Support\Arr::get($json,'ketqua_dapan0'),
        'result_1' => \Illuminate\Support\Arr::get($json,'ketqua_dapan1'),
        'result_2' => \Illuminate\Support\Arr::get($json,'ketqua_dapan2'),
        'result_3' => \Illuminate\Support\Arr::get($json,'ketqua_dapan3'),
        'result_4' => \Illuminate\Support\Arr::get($json,'ketqua_dapan4'),
        'result_5' => \Illuminate\Support\Arr::get($json,'ketqua_dapan5'),
        'score' => \Illuminate\Support\Arr::get($json,'chamdiem'),
        'duration'  => (float)$request->get('duration')
    ]);

    return response()->json($json);
});
