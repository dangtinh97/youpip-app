<?php

namespace App\Http\Controllers\Api;

use App\Helper\StrHelper;
use App\Http\Controllers\Controller;
use App\Repositories\ConfigRepository;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected readonly ChatService $chatService
    )
    {

    }

    public function chatGpt(Request $request)
    {
        $message = (string)$request->get('message');
        $this->chatService->chatGpt($message);
    }

    public function listChat(Request $request)
    {
        $lastOid = (string)$request->get('last_oid');
        if(!StrHelper::isObjectId($lastOid)){
            $lastOid = null;
        }

        $listChat = $this->chatService->listChat($lastOid);

        return response()->json($listChat->toArray());

    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinRoom(Request $request): JsonResponse
    {
        $userOid = (string)$request->get('user_oid');
        $room = $this->chatService->joinRoom($userOid);

        return response()->json($room->toArray());
    }

    /**
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function message(string $id, Request $request): JsonResponse
    {
        $lastOid = (string)$request->get('last_oid');
        if (!StrHelper::isObjectId($lastOid)) {
            $lastOid = null;
        }
        $message = $this->chatService->message($id, $lastOid);

        return response()->json($message->toArray());
    }

    /**
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(string $id, Request $request): JsonResponse
    {
        $send = $this->chatService->sendMessage($id, (string)$request->get('message'));

        return response()->json($send->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUser(Request $request): JsonResponse
    {
        $shortUserName = $request->get('username');
        $search = $this->chatService->searchUser($shortUserName);

        return response()->json($search->toArray());
    }
}
