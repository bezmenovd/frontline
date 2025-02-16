<?php

namespace App\Ws;

use App\Models\User;
use Websocket;

class Client
{
    public function __construct(
        public WebSocket\Connection $connection,
        public ?User $user = null, 
    ) {}
}
