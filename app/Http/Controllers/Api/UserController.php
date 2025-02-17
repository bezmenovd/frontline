<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\LoginRequest;
use App\Http\Requests\Api\User\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController
{
    public function __construct(
        protected UserService $userService,
    ) {}

    public function getUser()
    {
        /** @var User $user */
        $user = Auth::user();

        return [
            'user' => [
                'name' => $user->name,
                'rating' => $user->rating,
            ],
        ];
    }

    public function login(LoginRequest $request)
    {
        $user = User::query()
            ->where('name', $request->name)
            ->first();

        if (is_null($user)) {
            return response()->json([
                'error' => 'Неверные имя пользователя и/или пароль'
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Неверные имя пользователя и/или пароль'
            ]);
        }

        $user->token = $this->userService->generateToken();
        $user->save();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'rating' => $user->rating,
            ],
            'token' => $user->token,
        ]);
    }

    public function register(RegisterRequest $request)
    {
        if (User::query()->where('email', $request->email)->count() > 0) {
            return response()->json([
                'error' => 'Пользователь с таким адресом электронной почты уже существует'
            ]);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->token = $this->userService->generateToken();
        $user->save();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'rating' => $user->rating,
            ],
            'token' => $user->token,
        ]);        
    }
}
