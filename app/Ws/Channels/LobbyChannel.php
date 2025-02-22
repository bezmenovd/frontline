<?php

namespace App\Ws\Channels;

use App\Models\ChatMessage;
use App\Models\Host;
use App\Models\User;
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
        $this->redisClient->del("channel:lobby:clients");

        $this->eventBus->on("main:online_increased", function (WsClient $client) {
            $online = ($this->redisClient->get('online') instanceof \Predis\Response\Status) ? 0 : intval($this->redisClient->get('online'));
            $this->broadcast($client, new Message("online", ['online' => $online]));
        });

        $this->eventBus->on("main:online_decreased", function (WsClient $client) {
            $online = ($this->redisClient->get('online') instanceof \Predis\Response\Status) ? 0 : intval($this->redisClient->get('online'));
            $this->broadcast($client, new Message("online", ['online' => $online]), true);
        });

        $this->eventBus->on("host:host_deleted", function(WsClient $client, Host $host) {
            $this->broadcast($client, new Message("host_deleted", $host->id));
        });

        $this->eventBus->on("host:host_updated", function(WsClient $client, Host $host) {
            $host->load('users');
            $this->broadcast($client, new Message("host_updated", $host));
        });
    }

    public function handle(WsClient $client, string $type, mixed $payload): void
    {
        switch ($type) {
            case "new_message":
                $this->newMessage($client, $payload);
            break;
            case "new_host":
                $this->newHost($client, $payload);
            break;
            case "connect_to_host":
                $this->connectToHost($client, $payload);
            break;
        }
    }

    protected function newMessage(WsClient $client, mixed $payload): void
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

        $this->broadcast($client, (new Message("new_message", [
            'id' => $message->id,
            'datetime' => $message->created_at->addHours(3)->format("H:i"),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'text' => $message->text,
        ])));
    }

    protected function newHost(WsClient $client, mixed $payload): void
    {
        if (! is_array($payload) || ! key_exists("description", $payload) || ! key_exists("players", $payload)) {
            Log::error("lobby: new_host: invalid ws message: " . json_encode($payload));
            return;
        }

        $this->eventBus->dispatch('lobby:new_host', $client, $payload);

        $user = $client->getUser();
        $user->refresh();
        $user->load('host');

        $this->broadcast($client, new Message("new_host", [
            'id' => $user->host->id,
            'user' => [
                'id' => $user->host->user_id,
                'name' => $user->host->user->name,
            ],
            'description' => $user->host->description,
            'players' => $user->host->players,
            'size' => $user->host->size->value,
            'water' => $user->host->water->value,
            'users' => $user->host->users->map(fn(User $u) => [
                'id' => $u->id,
                'name' => $u->name
            ])->toArray(),
            'chatMessages' => [],
        ]));
    }

    protected function connectToHost(WsClient $client, mixed $payload): void
    {
        if (! is_array($payload) || ! key_exists('id', $payload)) {
            Log::error("lobby: connect_to_host: invalid ws message: " . json_encode($payload));
            return;
        }

        $host = Host::query()
            ->where('id', intval($payload['id']))
            ->first();
        
        $this->eventBus->dispatch("lobby:connect_to_host", $client, $host);

        $user = $client->getUser();
        $user->refresh();
        $user->load('host');

        $this->send($client, new Message("connected_to_host", [
            'id' => $user->host->id,
            'user' => [
                'id' => $user->host->user_id,
                'name' => $user->host->user->name,
            ],
            'description' => $user->host->description,
            'players' => $user->host->players,
            'size' => $user->host->size->value,
            'water' => $user->host->water->value,
            'users' => $user->host->users->map(fn(User $u) => [
                'id' => $u->id,
                'name' => $u->name
            ])->toArray(),
            'chatMessages' => $user->host->chatMessages->map(fn(ChatMessage $cm) => [
                'id' => $cm->id,
                'datetime' => $cm->created_at->addHours(3)->format("H:i"),
                'user' => $cm->user ? [
                    'id' => $cm->user->id,
                    'name' => $cm->user->name,
                ] : [
                    'id' => 0,
                    'name' => '',
                ],
                'text' => $cm->text,
            ]),
        ]));
    }
}
