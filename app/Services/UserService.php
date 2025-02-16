<?php

namespace App\Services;

class UserService
{
    public function generateToken(): string
    {
        return md5(microtime(true) * random_int(1,100));
    }
}
