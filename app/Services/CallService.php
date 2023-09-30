<?php

namespace App\Services;

use App\Enums\ECall;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseSuccess;
use App\Models\CbUser;
use App\Repositories\CallRepository;
use \Firebase\JWT\JWT;
use Illuminate\Support\Str;

class CallService
{
    public function __construct(protected CallRepository                           $callRepository,
                                protected \App\Repositories\Chatbot\UserRepository $userRepository,
                                protected ChatBotService                           $chatBotService)
    {
    }

    public function index(string $fbid): array
    {
        /** @var CbUser $user */
        $user = $this->userRepository->first([
            'fbid' => $fbid
        ]);

        if (empty($user->fbid_connect)) {
            return [];
        }
        $payload = [
            "sub" => $user->_id, // Replace with your user ID or any custom data
            "email" => "example@example.com", // Replace with user email or other data
            "exp" => strtotime("+1 day") // Set the expiration time of the token (e.g., 1 day from now)
        ];
        /** @var CbUser $userConnect */
        $userConnect = $this->userRepository->first([
            'fbid' => $user->fbid_connect
        ]);
        $uuid = Str::uuid()->toString();
        $uuid = 'dangtinh1997';

        $this->callRepository->create([
            'from_user_id' => $user->id,
            'with_user_id' => $userConnect->id,
            'uuid' => $uuid,
            'status' => ECall::START->value]);

        // Define your payload data


        // Generate the JWT token
        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return [
            'token' => $token,
            'room_id' => $uuid,
            'connect_with' => $user->fbid_connect
        ];
    }

    public function sendNotification(string $fbid, string $roomId): ApiResponse
    {
        $this->chatBotService->sendNotificationCall($fbid, $roomId);
        return new ResponseSuccess();
    }

    /**
     * @param string $fbid
     * @param string $roomId
     * @return array
     */
    public function answer(string $fbid, string $roomId): array
    {
        $user = $this->userRepository->first([
            'fbid' => $fbid
        ]);

        if (empty($user->fbid_connect)) {
            return [];
        }
        $payload = [
            "sub" => $user->_id, // Replace with your user ID or any custom data
            "email" => "example@example.com", // Replace with user email or other data
            "exp" => strtotime("+1 day") // Set the expiration time of the token (e.g., 1 day from now)
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        $this->callRepository->update([
            'uuid' => $roomId
        ], [
            'status' => ECall::IN_CALL->value
        ]);

        return [
            'token' => $token,
            'room_id' => $roomId
        ];
    }

    /**
     * @param string $callId
     * @return ApiResponse
     */
    public function destroyCall(string $callId): ApiResponse
    {
        $this->callRepository->deleteWhere([
            'uuid' => $callId
        ]);
        return new ResponseSuccess();
    }

}
