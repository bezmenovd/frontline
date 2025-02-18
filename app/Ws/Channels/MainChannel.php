<?php

namespace App\Ws\Channels;

use App\Ws\Channel;
use App\Ws\Client;
use App\Ws\Message;

class MainChannel extends Channel
{
    public function channel(): string
    {
        return "main";
    }

    public function initialize(): void
    {}

    public function subscribe(Client $client): bool
    {
        $subscribed = parent::subscribe($client);
        print("client $client->token : $client->fd " . ($subscribed ? "subscribed" : "not subscribed") . ", clients: ".count($this->clients)."\n");
        if (! $subscribed) {
            $this->send($client, new Message("already_logged_in"));
            $this->server->disconnect($client->fd);
            return false;
        }

        $this->state->online = count($this->clients);
        $this->eventBus->dispatch('online_changed');

        return $subscribed;
    }

    public function unsubscribe(Client $client): void
    {
        parent::unsubscribe($client);

        $this->state->online = count($this->clients);
        $this->eventBus->dispatch('online_changed');
    }

    public function handle(Client $client, string $type, mixed $payload): void
    {}
}
