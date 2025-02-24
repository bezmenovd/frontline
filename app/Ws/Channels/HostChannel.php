<?php

namespace App\Ws\Channels;

use App\Models\ChatMessage;
use App\Models\Game;
use App\Models\Host;
use App\Models\Host\Size;
use App\Models\Host\Water;
use App\Ws\Channel;
use App\Ws\Client as WsClient;
use App\Ws\Message;
use Illuminate\Support\Facades\Log;

class HostChannel extends Channel
{
    public function channel(): string
    {
        return "host";
    }

    public function initialize(): void
    {
        $this->redisClient->del("channel:host:*");

        $this->eventBus->on('lobby:new_host', function(WsClient $client, array $payload) {
            $user = $client->getUser();

            $host = new Host();
            $host->user_id = $user->id;
            $host->description = $payload['description'];
            $host->players = $payload['players'];
            $host->size = Size::x64;
            $host->water = Water::Low;
            $host->save();

            $user->host_id = $host->id;
            $user->save();

            $this->subscribe($client);
        });

        $this->eventBus->on("lobby:connect_to_host", function(WsClient $client, Host $host) {

            if (is_null($host)) {
                Log::error("host: lobby:connect_to_host: no such host: $host->id");
                return;
            }

            $user = $client->getUser();
            $user->host_id = $host->id;
            $user->save();

            $message = new ChatMessage();
            $message->text = "{$user->name} подключился к игре";
            $message->host_id = $host->id;
            $message->save();

            $this->broadcast($client, new Message("user_joined", [
                'message' => [
                    'id' => $message->id,
                    'datetime' => $message->created_at->addHours(3)->format("H:i"),
                    'user' => [
                        'id' => 0,
                        'name' => '',
                    ],
                    'text' => $message->text,
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ]
            ]));

            $this->subscribe($client);
        });
    }

    public function unsubscribe(WsClient $client): void
    {
        parent::unsubscribe($client);

        $user = $client->getUser();
        $host = $user->host;

        if (! is_null($host)) {
            if ($host->user->is($user)) {
                $this->eventBus->dispatch("host:host_deleted", $client, $host);
                $host->delete();
            } else {
                $message = new ChatMessage();
                $message->text = "{$user->name} вышел из игры";
                $message->host_id = $host->id;
                $message->save();

                $this->broadcast($client, new Message("user_left", [
                    'user_id' => $user->id,
                    'message' => [
                        'id' => $message->id,
                        'datetime' => $message->created_at->addHours(3)->format("H:i"),
                        'user' => [
                            'id' => 0,
                            'name' => '',
                        ],
                        'text' => $message->text,
                    ]
                ]), true);
            }
            $user->host_id = null;
            $user->save();
        }
    }

    public function getClients(WsClient $client): array
    {
        $host = $client->getUser()->host;

        if (is_null($host)) {
            return [];
        }

        $data = json_decode($this->redisClient->get("channel:" . $this->channel() . ":" . $host->id . ":clients"), true) ?? [];
        $data = array_filter($data, fn($item) => is_array($item));
        $clients = array_map(fn(array $item) => new WsClient($item['fd'], $item['token']), $data);
        
        return $clients;
    }

    /**
     * @param WsClient[] $clients
     */
    public function setClients(WsClient $client, array $clients): void
    {
        $host = $client->getUser()->host;

        if (is_null($host)) {
            return;
        }

        $this->redisClient->set("channel:" . $this->channel() . ":" . $host->id . ":clients", json_encode(array_map(fn(WsClient $c) => [
            'fd' => $c->fd,
            'token' => $c->token,
        ], $clients)));
    }

    public function handle(WsClient $client, string $type, mixed $payload): void
    {
        switch ($type) {
            case "delete_host":
                $this->deleteHost($client, $payload);
            break;
            case "leave_host":
                $this->leaveHost($client, $payload);
            break;
            case "update_host":
                $this->updateHost($client, $payload);
            break;
            case "new_message":
                $this->newMessage($client, $payload);
            break;
            case "start_game":
                $this->startGame($client, $payload);
            break;
        }
    }

    protected function deleteHost(WsClient $client, mixed $payload)
    {
        $user = $client->getUser();
        $host = $user->host;

        if (is_null($host) || $host->user->isNot($user)) {
            return;
        }

        $this->eventBus->dispatch("host:host_deleted", $client, $host);
        $host->delete();
    }

    protected function leaveHost(WsClient $client, mixed $payload)
    {
        $user = $client->getUser();
        $host = $user->host;

        if ($host->user->is($user)) {
            return;
        }
        
        $message = new ChatMessage();
        $message->text = "{$user->name} вышел из игры";
        $message->host_id = $host->id;
        $message->save();

        $this->broadcast($client, new Message("user_left", [
            'user_id' => $user->id,
            'message' => [
                'id' => $message->id,
                'datetime' => $message->created_at->addHours(3)->format("H:i"),
                'user' => [
                    'id' => 0,
                    'name' => '',
                ],
                'text' => $message->text,
            ]
        ]));

        $user->host_id = null;
        $user->save();

        $this->eventBus->dispatch("host:host_updated", $client, $host);
    }

    protected function updateHost(WsClient $client, mixed $payload)
    {
        if (! is_array($payload) || ! key_exists('size', $payload) || ! key_exists("water", $payload)) {
            Log::error("host: update_host: invalid ws message: " . json_encode($payload));
            return;
        }

        $user = $client->getUser();
        $host = $user->host;

        if (is_null($host) || $host->user->isNot($user)) {
            return;
        }

        $host->size = Size::from($payload['size']);
        $host->water = Water::from($payload['water']);
        $host->save();

        $this->eventBus->dispatch("host:host_updated", $client, $host);
    }

    protected function newMessage(WsClient $client, mixed $payload)
    {
        if (! is_array($payload) || ! key_exists('text', $payload) || ! is_string($payload['text']) || empty($payload['text'])) {
            Log::error("host: new_message: invalid ws message: " . json_encode($payload));
            return;
        }

        $host = $client->getUser()->host;

        if (is_null($host)) {
            return;
        }

        $message = new ChatMessage();
        $message->user_id = $client->getUser()->id;
        $message->text = $payload['text'];
        $message->host_id = $host->id;
        $message->save();

        $message->refresh();
        $message->load('user');

        $this->broadcast($client, new Message("new_message", [
            'id' => $message->id,
            'datetime' => $message->created_at->addHours(3)->format("H:i"),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
            ],
            'text' => $message->text,
        ]));
    }

    protected function startGame(WsClient $client, mixed $payload): void
    {
        $user = $client->getUser();
        $host = $user->host;

        if (is_null($host) || $host->user->isNot($user)) {
            return;
        }

        $clients = $this->getClients($client);

        $this->broadcast($client, new Message("game_started", [
            'host_id' => $host->id,
        ]));
        $this->setClients($client, []);

        $game = new Game();
        $game->players = $host->players;
        $game->size = $host->size;
        $game->water = $host->water;
        $game->save();

        foreach ($host->users as $user) {
            $user->game_id = $game->id;
            $user->host_id = null;
            $user->save();
        }

        $host->delete();

        $this->eventBus->dispatch("host:start_game", $game, $clients);
    }
}
