<?php

namespace App\Ws\Channels;

use App\Ws\Channel;
use App\Ws\Client as WsClient;
use App\Ws\Message;

class MainChannel extends Channel
{
    public function channel(): string
    {
        return "main";
    }

    public function initialize(): void
    {
        $this->redisClient->del("channel:main:clients");
    }

    public function subscribe(WsClient $client): bool
    {
        parent::subscribe($client);

        $this->redisClient->set("online", count($this->getClients($client)));
        $this->eventBus->dispatch("main:online_increased", $client);

        return true;
    }

    public function unsubscribe(WsClient $client): void
    {
        parent::unsubscribe($client);

        $this->redisClient->set("online", count($this->getClients($client)));
        $this->eventBus->dispatch("main:online_decreased", $client);
    }

    public function handle(WsClient $client, string $type, mixed $payload): void
    {}
}
