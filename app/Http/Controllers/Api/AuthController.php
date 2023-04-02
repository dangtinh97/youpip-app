<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        public readonly AuthService $authService,
        protected readonly ChatService $chatService
    )
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attempt(Request $request): JsonResponse
    {

        $username = (string)$request->get('username');
        if(strlen($username)<5){
            $username = '';
        }
        $login = $this->authService->login($username);
        if(!$username){
            $this->createChatWithOpenAI();
        }
        return response()->json($login->toArray());
    }

    /**
     * @return void
     */
    private function createChatWithOpenAI(): void
    {
        $userOid = $this->chatService->getInfoChatGpt();
        $this->chatService->joinRoom($userOid);
    }


}
