<?php

namespace App\Game\State\Members;

use App\Ws\Client as WsClient;

class Player
{
    public function __construct(
        public WsClient $client,
    ) {}

    public function getName(): string
    {
        return $this->client->getUser()->name;
    }
}
