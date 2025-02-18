<?php

namespace App\Services;

class UserService
{
    public function generateToken(): string
    {
        return sha1(microtime(true) * random_int(1,100));
    }
}
