<?php

namespace App\Ws;

use App\Models\User;

class Client
{
    public function __construct(
        public int $fd,
        public string $token,
    ) {}

    public function getUser(): User
    {
        return User::query()->where('token', $this->token)->first();
    }
}
