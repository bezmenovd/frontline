<?php

namespace App\Ws\Channels;

use App\Ws\Channel;
use App\Ws\Client;
use App\Ws\Events;
use App\Ws\Message;
use App\Ws\State;

class MainChannel extends Channel
{
    public function subscribe(Client $client): void
    {
        foreach ($this->clients as $existingClient) {
            /** @var Client $existingClient */
            if ($existingClient->user->is($client->user)) {
                $client->connection->text((new Message('main', 'already_logged_in'))->toString());
                $client->connection->close();
                return;
            }
        }

        parent::subscribe($client);

        State::get()->online = count($this->clients);
        Events::get()->dispatch("online_changed");
    }

    public function unsubscribe(Client $client): void
    {
        parent::unsubscribe($client);
        State::get()->online = count($this->clients);
        Events::get()->dispatch("online_changed");
    }

    public function handle(Client $client, string $type, mixed $payload): void
    {}
}
