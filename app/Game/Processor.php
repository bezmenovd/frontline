<?php

namespace App\Game;

use App\Game\Generators\CellGenerator;
use App\Game\State\Members\Bot;
use App\Game\State\Members\Player;
use App\Models\User;
use App\Models\Game;
use App\Ws\Channels\GameChannel;
use App\Ws\EventBus;
use App\Ws\Message;
use App\Ws\Client as WsClient;
use Predis\Client;
use Swoole\Coroutine;

class Processor
{
    public State $state;
    
    public function __construct(
        public Game $game,
        public array $clients,
        public Client $redisClient,
        public EventBus $eventBus,
        public GameChannel $gameChannel,
    ) {
        $this->eventBus->on("game:" . $this->game->id . ":user_left", function (WsClient $client) {
            print("processor: event bus: user left\n");
            $this->clients = array_values(array_filter($this->clients, fn(WsClient $c) => $c->token !== $client->token));
            if (empty($this->clients)) {
                $this->end();
            }
        });

        $this->state = new State();
    }

    public function critical(string $error)
    {
        if (count($this->clients) > 0) {
            $this->gameChannel->broadcast($this->clients[0], new Message("critical", [
                'text' => $error
            ]));
        }

        foreach ($this->clients as $client) {
            /** @var WsClient $client */
            $this->gameChannel->unsubscribe($client);
        }

        $this->game->delete();
    }

    public function end()
    {

    }

    public function start()
    {
        $this->gameChannel->broadcast($this->clients[0], new Message("game_loading_status", [
            'text' => 'Распределение игроков'
        ]));

        $this->distributePlayers();

        $this->gameChannel->broadcast($this->clients[0], new Message("game_loading_status", [
            'text' => 'Генерация карты'
        ]));

        $this->generateMap();

        $this->gameChannel->broadcast($this->clients[0], new Message("game_loading_status", [
            'text' => 'Передача данных'
        ]));

        $this->gameChannel->broadcast($this->clients[0], new Message("game_data.players", [
            'players' => array_map(fn(Player|Bot $m) => $m->getName(), $this->state->players),
        ]));

        $chunks = array_chunk($this->state->map->ground, floor(count($this->state->map->ground[0]) / 16));
        for ($i = 0; $i < 16; $i++) {
            $this->gameChannel->broadcast($this->clients[0], new Message("game_data.map.ground.part", [
                'part' => array_map(fn(array $a) => array_values($a),$chunks[$i]),
            ]));
        }

        $this->gameChannel->broadcast($this->clients[0], new Message("game_data.finish"));


        while (true) {}
    }

    public function distributePlayers(): void
    {
        for ($i = 0; $i < count($this->clients); $i++) {
            while (true) {
                $randomIndex = random_int(0, $this->game->players);

                if (! isset($this->state->players[$randomIndex])) {
                    $this->state->players[$randomIndex] = new Player($this->clients[$i]);
                    break;
                }
            }
        }

        $botFakeIds = [];

        for ($i = 0; $i < $this->game->players; $i++) {
            if (! isset($this->state->players[$i])) {
                $fakeId = random_int(1, 999);
                while (in_array($fakeId, $botFakeIds)) {
                    $fakeId = random_int(1, 999);
                }

                $this->state->players[$i] = new Bot($fakeId);
            }
        }
    }

    public function generateMap(): void
    {
        $generator = new CellGenerator();
        $this->state->map->ground = $generator->run($this->game->size, $this->game->water);
    }
}
