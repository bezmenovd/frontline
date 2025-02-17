<?php

namespace App\Ws;

class Message
{
    public function __construct(
        public string $channel,
        public string $type,
        public mixed $payload = null,
    ) {}

    public function toString()
    {
        return json_encode([
            'channel' => $this->channel,
            'type' => $this->type,
            'payload' => $this->payload,
        ]);
    }
}