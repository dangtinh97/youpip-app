<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseSuccess;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    const MAX_SHORT_NAME = 10;
    public function __construct(protected readonly UserRepository $userRepository)
    {
    }


    /**
     * @param string $username
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function login(string $username): ApiResponse
    {

        if (!$username) {
            $username = Str::uuid()->toString();
        }

        /** @var \App\Models\User|null $user */
        $user = $this->userRepository->first([
            'username' => $username
        ]);

        if (!$user instanceof User) {
            $id = $this->userRepository->getId();

            $user = $this->userRepository->create([
                'id' => $id,
                'short_username' => $this->createShortUserName($id),
                'username' => $username,
                'password' => Hash::make((string)time())
            ]);
        }

        return $this->user($user);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    private function createShortUserName(int $id): string
    {
        $chars = range('A', 'Z');
        shuffle($chars);
        $result = "";
        $arrayFirst = range(0, 9);
        $keys = [];
        $values = [];
        for ($i = 0; $i < strlen($id); $i++) {
            $pos = array_rand($arrayFirst);
            $keys[] = $arrayFirst[$pos];
            $values[] = ((string)$id)[$i];
            unset($arrayFirst[$pos]);
        }
        sort($keys);
        $arrayInt = array_combine($keys, $values);

        for ($i = 0; $i < self::MAX_SHORT_NAME; $i++) {
            $value = $arrayInt[$i] ?? null;
            if (is_numeric($value)) {
                $result .= ($arrayInt[$i]);
            } else {
                $result .= $chars[$i];
            }
        }

        return $result;
    }

    /**
     * @param \App\Models\User $user
     *
     * @return \App\Http\Response\ApiResponse
     */
    private function user(User $user): ApiResponse
    {
        $token = Auth::login($user);
        return new ResponseSuccess([
            '_id' => $user->_id,
            'username' => $user->username,
            'user_id' => $user->id,
            'full_name'=> $user->full_name ?? '',
            'token' => $token,
            'short_username' => $user->short_username ?? ''
        ]);
    }
}
