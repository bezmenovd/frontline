<?php

namespace App\Ws\Channels;

use App\Ws\Channel;
use App\Ws\Client;
use App\Ws\Events;
use App\Ws\Message;
use App\Ws\State;

class LobbyChannel extends Channel
{
    public function __construct() 
    {
        Events::get()->on('online_changed', function() {
            $this->broadcast(new Message("lobby", "online", ['online' => State::get()->online]));
        });  
    }

    public function subscribe(Client $client): void
    {
        parent::subscribe($client);
    }

    public function unsubscribe(Client $client): void
    {
        parent::unsubscribe($client);
    }

    public function handle(Client $client, string $type, mixed $payload): void
    {
        
    }
}
