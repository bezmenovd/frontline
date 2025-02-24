<?php

namespace App\Ws\Channels;

use App\Models\ChatMessage;
use App\Models\Game;
use App\Ws\Channel;
use App\Ws\Client as WsClient;
use App\Ws\Message;
use App\Game\Processor;
use Swoole\Coroutine;

class GameChannel extends Channel
{
    public function channel(): string
    {
        return "game";
    }

    public function initialize(): void
    {
        $this->redisClient->del("channel:game:*");

        $this->eventBus->on('host:start_game', function(Game $game, array $clients) {
            $this->setClients($clients[0], $clients);
            Coroutine::create(function() use ($game, $clients) {
                Coroutine::sleep(3);
                $gameProcessor = new Processor($game, $clients, $this->redisClient, $this->eventBus, $this);
                $gameProcessor->start();
            });
        });
    }

    public function unsubscribe(WsClient $client): void
    {
        parent::unsubscribe($client);

        $user = $client->getUser();
        $game = $user->game;

        if (is_null($game)) {
            return;
        }

        $message = new ChatMessage();
        $message->text = "{$user->name} вышел из игры";
        $message->game_id = $game->id;
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

        $user->game_id = null;
        $user->save();

        $this->eventBus->dispatch("game:" . $game->id . ":user_left", $client);
    }

    public function getClients(WsClient $client): array
    {
        $game = $client->getUser()->game;

        if (is_null($game)) {
            return [];
        }

        $data = json_decode($this->redisClient->get("channel:" . $this->channel() . ":" . $game->id . ":clients"), true) ?? [];
        $data = array_filter($data, fn($item) => is_array($item));
        $clients = array_map(fn(array $item) => new WsClient($item['fd'], $item['token']), $data);
        
        return $clients;
    }

    /**
     * @param WsClient[] $clients
     */
    public function setClients(WsClient $client, array $clients): void
    {
        $game = $client->getUser()->game;

        if (is_null($game)) {
            return;
        }

        $this->redisClient->set("channel:" . $this->channel() . ":" . $game->id . ":clients", json_encode(array_map(fn(WsClient $c) => [
            'fd' => $c->fd,
            'token' => $c->token,
        ], $clients)));
    }

    public function handle(WsClient $client, string $type, mixed $payload): void
    {
        switch ($type) {

        }
    }
}
