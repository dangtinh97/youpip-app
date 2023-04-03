<?php

namespace App\Services;

use App\Enums\EStatusApi;
use App\Enums\ETypeMessage;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Models\Room;
use App\Models\User;
use App\Repositories\ConfigRepository;
use App\Repositories\LogRepository;
use App\Repositories\MessageRepository;
use App\Repositories\RoomRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use OpenAI;

class ChatService
{
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly ConfigRepository $configRepository,
        protected readonly RoomRepository $roomRepository,
        protected readonly MessageRepository $messageRepository,
        protected readonly LogRepository $logRepository
    )
    {

    }

    /**
     * @param string $roomOid
     * @param string $message
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function sendMessage(string $roomOid, string $message): ApiResponse
    {
        /** @var Room $room */
        $room = $this->roomRepository->first([
            '_id' => new ObjectId($roomOid)
        ]);
        $joins = $room->join;
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        if (($key = array_search($userId, $joins)) !== false) {
            unset($joins[$key]);
        }

        $toUserId = array_values($joins)[0];

        /** @var \App\Models\User $withUser */
        $withUser = $this->userRepository->first([
            'id' => $toUserId
        ]);

        /** @var \App\Models\Message $create */
        $create = $this->messageRepository->create([
            'room_id' => $room->id,
            'from_user_id' => $userId,
            'message' => $message,
            'type' => ETypeMessage::ONLY_TEXT->value,
        ]);
        $endUser = $user->id;
        $messageSave = $message;
        if ($withUser->username === 'OPEN_AI') {
            $message = $this->chatGpt($room->id, $userId, $withUser->id);
            $endUser = $withUser->id;
            $messageSave = $message;
        } else {
            $message = "";
        }
        $room->update([
            'last_message' => [
                'user_id' => $endUser,
                'message' => $messageSave,
                'time' => new UTCDateTime()
            ]
        ]);

        return new ResponseSuccess([
            'message_oid' => $create->_id,
            'message' => $message
        ]);
    }

    /**
     * @param int    $roomId
     * @param string $messsage
     * @param int    $userId
     * @param int    $botId
     *
     * @return string
     */
    public function chatGpt(int $roomId, int $userId, int $botId): string
    {
        try{
            /** @var \App\Models\Config $config */
            $config = $this->configRepository->first([
                'type' => 'OPENAI'
            ]);
            $data = $config->data ?? [];
            $time = Arr::get($data, 'max_time_wait', 300);

            $find = $this->messageRepository->find([
                'room_id' => $roomId,
                'created_at' => [
                    '$gt' => new UTCDateTime((time() - $time) * 1000)
                ]
            ]);
            $messages = $find->map(function ($item) use ($userId) {
                /** @var \App\Models\Message $item */
                $role = $item->from_user_id !== $userId ? "assistant" : "user";

                return [
                    'role' => $role,
                    'content' => $item->message
                ];
            })->toArray();
            $key = env('OPENAI_KEY', '');
            $keys = explode(",",Arr::get($data,'api_key',$key));
            shuffle($keys);
            $client = OpenAI::client($key = $keys[0] ?? $key);
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ])->toArray();

            $this->logRepository->create([
                'type' => 'OPEN_AI',
                'data' => array_merge($response, [
                    'use_key' => $key
                ])
            ]);

            $message = Arr::get($response, 'choices.0.message.content');

            if ($message) {
                $this->messageRepository->create([
                    'room_id' => $roomId,
                    'from_user_id' => $botId,
                    'message' => $message,
                    'type' => ETypeMessage::ONLY_TEXT->value,
                ]);
            }

            return $message ?? "Tôi đang gặp sự cố rồi!";
        }catch (\Exception $exception){
            return "Tôi đang gặp sự cố rồi!";
        }

    }

    public function listChat(?string $lastOid)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->roomRepository->listChat($user->id,$lastOid);
        $maps = $data->map(function ($item) use ($user){
            /** @var Room $item */

            $users = collect($item->getAttribute('users'));

            $last = $item->last_message ?? [];
            $userId = $user->id;
            $fromUserId = Arr::get($last,'user_id');
            $message = ($fromUserId==$userId ? 'Bạn: ' : ''). Arr::get($last,'message');

            /** @var UTCDateTime|null $time */
            if($time = Arr::get($last,'time')){
                $time = date('H:i:s',$time->toDateTime()->getTimestamp());
            }

            $user = $users->where('id','!=',$userId)->first();

            return [
                'room_oid' => $item->_id,
                'time' => $time ?? '',
                'message' => $message,
                'user_id' => $fromUserId ?? 0,
                'full_name' => $user['full_name'] ?? ($user['short_username'] ?? 'Người dùng')
            ];
        });

        return new ResponseSuccess([
            'list' => $maps->toArray()
        ]);
    }

    /**
     * @param string $userOid
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function joinRoom(string $userOid): ApiResponse
    {
        /** @var \App\Models\User $userFind */
        $userFind = $this->userRepository->first([
            '_id' => new ObjectId($userOid)
        ]);
        $withUserId = $userFind->id;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \App\Models\Room|null $room */
        $room = $this->roomRepository->first([
            '$or' => [
                ['join' => [$user->id, $withUserId]],
                ['join' => [$withUserId, $user->id]]
            ]
        ]);
        if (!$room instanceof Room) {
            /** @var \App\Models\Room $room */
            $room = $this->roomRepository->create([
                'id' => $this->roomRepository->getId(),
                'user_id_created' => $user->id,
                'join' => [$user->id, $withUserId],
                'last_message' => []
            ]);
        }

        return new ResponseSuccess([
            'room_oid' => $room->_id,
            'full_name' => $userFind->full_name ?? $userFind->short_username,
            'user_id' => $withUserId
        ]);
    }

    /**
     * @param string      $roomOid
     * @param string|null $lastOid
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function message(string $roomOid, ?string $lastOid): ApiResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        /** @var \App\Models\Room $room */
        $room = $this->roomRepository->first([
            '_id' => $roomOid
        ]);
        $roomId = $room->id;
        $result = $this->messageRepository
            ->message($roomId, $lastOid)
            ->map(function ($item) use ($userId) {
                /** @var \App\Models\Message $item */
                return [
                    'message_oid' => $item->_id,
                    'user_id' => $item->from_user_id,
                    'message' => $item->message,
                    'from_me' => $userId === $item->from_user_id,
                    'time' => date('H:i:s', $item->created_at->toDateTime()->getTimestamp())
                ];
            })
            ->reverse()
            ->toArray();

        return new ResponseSuccess([
            'list' => array_values($result)
        ]);
    }

    /**
     * @return string
     */
    public function getInfoChatGpt(): string
    {
        /** @var \App\Models\User $user */
        $user = $this->userRepository->first([
            'username' => 'OPEN_AI'
        ]);

        return $user->_id;
    }

    /**
     * @param string $value
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function searchUser(string $value):ApiResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $this->userRepository->first([
            'short_username' => $value
        ]);

        if(!$user instanceof User){
            return new ResponseError();
        }

        return new ResponseSuccess([
            'user_oid' => $user->_id,
            'user_id'  =>$user->id,
            'full_name' => $user->full_name ?? $user->short_username
        ]);
    }
}
