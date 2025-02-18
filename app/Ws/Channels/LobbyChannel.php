<?php

namespace App\Ws\Channels;

use App\Models\ChatMessage;
use App\Ws\Channel;
use App\Ws\Client as WsClient;
use App\Ws\Message;
use Illuminate\Support\Facades\Log;

class LobbyChannel extends Channel
{
    public function channel(): string
    {
        return "lobby";
    }

    public function initialize(): void
    {
        $this->eventBus->on("online_changed", function () {
            $this->broadcast(new Message("online", intval($this->redisClient->get('online'))));
        });
    }

    public function subscribe(WsClient $client): bool
    {
        $subscribed = parent::subscribe($client);

        if ($subscribed) {
            $this->send($client, new Message("online", intval($this->redisClient->get('online'))));
        }

        return $subscribed;
    }

    public function handle(WsClient $client, string $type, mixed $payload): void
    {
        switch ($type) {
            case "new_message":
                $this->newMessage($client, $payload);
            break;
        }
    }

    protected function newMessage(WsClient $client, mixed $payload)
    {
        if (! is_array($payload) || ! key_exists("text", $payload)) {
            Log::error("lobby: new_message: invalid ws message: " . json_encode($payload));
            return;
        }

        $user = $client->getUser();

        $message = new ChatMessage();
        $message->user_id = $user->id;
        $message->text = $payload['text'];
        $message->save();

        $this->broadcast((new Message("new_message", [
            'id' => $message->id,
            'datetime' => $message->created_at->addHours(3)->format("H:i"),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'text' => $message->text,
        ])));
    }
}
