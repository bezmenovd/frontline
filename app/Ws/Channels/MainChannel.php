<?php

namespace App\Ws\Channels;

use App\Ws\Channel;
use App\Ws\Client;
use App\Ws\Events;
use App\Ws\State;

class MainChannel extends Channel
{
    public function subscribe(Client $client): void
    {
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
