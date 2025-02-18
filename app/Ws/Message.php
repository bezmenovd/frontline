<?php

namespace App\Ws;

class Message
{
    public string $channel;

    public function __construct(
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