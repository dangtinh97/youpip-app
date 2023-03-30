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
            $user = $this->userRepository->create([
                'id' => $this->userRepository->getId(),
                'username' => $username,
                'password' => Hash::make((string)time())
            ]);
        }

        return $this->user($user);
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
            'full_name'=> $user->full_name,
            'token' => $token,
        ]);
    }
}
