<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatBotService;
use Illuminate\Contracts\Console\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatbotController extends Controller
{
    public function __construct(
        protected readonly ChatBotService $chatBotService
    )
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function verifyWebhook(Request $request): Application|ResponseFactory|Response
    {
        $mode = $request->get('hub_mode','');
        $token = $request->get('hub_verify_token','');
        $challenge = $request->get('hub_challenge');

        if($mode==="subscribe" && $token===env('CHATBOT_VERIFICATION_CODE')){
            return response($challenge,200);
        }

        return response("",403);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        $response = $this->chatBotService->onWebhook($request->all());
        return response()->json($response);
    }

}
