<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
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
            'short_username' => $user->short_username ?? '',
            'version_review' => ''
        ]);
    }

    /**
     * @param string $token
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function updateTokenPush(string $token): ApiResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->userRepository->update(['id' => $user->id], [
            'token_fcm' => $token
        ]);

        return new ResponseSuccess();
    }

    /**
     * @param array $data
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function workMemoLogin(array $data): ApiResponse
    {
        $email = Arr::get($data, 'email');
        /** @var User|null $user */
        $user = $this->userRepository->first([
            'email' => $email
        ]);
        if (!$user instanceof User) {
            /** @var User $user */
            $user = $this->userRepository->create([
                'id' => $this->userRepository->getId(),
                'email' => $email,
                'password' => Hash::make(Arr::get($data, 'password')),
                'verify_account' => true
            ]);
        } else {
            $login = Auth::attempt($data);
            if (!$login) {
                return new ResponseError(400, 'Thông tin đăng nhập không hợp lệ');
            }
        }
        $token = Auth::login($user);

        return new ResponseSuccess([
            'email' => $email,
            'token' => $token,
            '_id' => $user->_id,
            'id' => $user->id
        ]);
    }
}
