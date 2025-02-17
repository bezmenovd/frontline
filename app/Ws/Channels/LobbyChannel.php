<?php

namespace App\Ws\Channels;

use App\Models\ChatMessage;
use App\Ws\Channel;
use App\Ws\Client;
use App\Ws\Events;
use App\Ws\Message;
use App\Ws\State;
use Illuminate\Support\Facades\Log;

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
        switch ($type) {
            case "new_message":
                if (! is_array($payload) || ! key_exists("text", $payload)) {
                    Log::error("invalid ws message: " . json_encode($payload));
                    return;
                }

                $message = new ChatMessage();
                $message->user_id = $client->user->id;
                $message->text = $payload['text'];
                $message->save();

                $this->broadcast((new Message("lobby", "new_message", [
                    'id' => $message->id,
                    'datetime' => $message->created_at->addHours(3)->format("H:i"),
                    'user' => [
                        'id' => $client->user->id,
                        'name' => $client->user->name,
                    ],
                    'text' => $message->text,
                ])));
            break;
        }
    }
}
