<?php

namespace App\Ws;

use App\Models\User;
use App\Ws\Channels\LobbyChannel;
use App\Ws\Channels\MainChannel;
use App\Ws\Client as WsClient;
use Illuminate\Support\Facades\Log;
use Predis\Client;
use Swoole\WebSocket\Server as WsServer;
use Swoole\WebSocket\Frame;

class GameServer
{
    public WsServer $wsServer;
    public Client $redisClient;
    public EventBus $eventBus;

    public MainChannel $mainChannel;
    public LobbyChannel $lobbyChannel;

    public function __construct(
        WsServer $wsServer,
        Client $redisClient,
    ) {
        $this->wsServer = $wsServer;
        $this->redisClient = $redisClient;
        $this->eventBus = new EventBus();

        $this->mainChannel = new MainChannel($this->wsServer, $this->redisClient, $this->eventBus);
        $this->lobbyChannel = new LobbyChannel($this->wsServer, $this->redisClient, $this->eventBus);
    }

    public function start()
    {
        $this->wsServer->on("message", function (WsServer $server, Frame $frame){
            $data = json_decode($frame->data, true);

            if (! key_exists('token', $data) || ! key_exists('type', $data) || ! key_exists('payload', $data)) {
                Log::error("invalid ws message: " . $frame->data);
                return;
            }

            $user = User::query()->where('token', $data['token'])->first();
            if (is_null($user)) {
                Log::error("user not authorized: " . $frame->data);
                return;
            }

            $client = new WsClient($frame->fd, $data['token']);
            
            $clients = json_decode($this->redisClient->get("clients"), true) ?? [];
            $clients[$frame->fd] = $data['token'];

            $this->redisClient->set("clients", json_encode($clients));

            switch ($data['type']) {
                case "subscribe":
                    if (! key_exists('channel', $data['payload'])) {
                        Log::error("invalid ws message: " . $frame->data);
                        return;
                    }

                    switch ($data['payload']['channel']) {
                        case "main": 
                            $this->mainChannel->subscribe($client);
                        break;
                        case "lobby":
                            $this->lobbyChannel->subscribe($client);
                        break;
                    };
                break;
                case "unsubscribe":
                    if (! key_exists('channel', $data['payload'])) {
                        Log::error("invalid ws message: " . $frame->data);
                        return;
                    }

                    switch ($data['payload']['channel']) {
                        case "main": 
                            $this->mainChannel->unsubscribe($client);
                        break;
                        case "lobby": 
                            $this->lobbyChannel->unsubscribe($client);
                        break;
                    };
                break;
                default:
                    if (! key_exists('channel', $data)) {
                        Log::error("invalid ws message: " . $frame->data);
                        return;
                    }

                    switch ($data['channel']) {
                        case "lobby":
                            $this->lobbyChannel->handle($client, $data['type'], $data['payload']);
                        break;
                    }
                break;
            };
        });

        $this->wsServer->on("close", function(WsServer $wsServer, int $fd) {
            $clients = json_decode($this->redisClient->get("clients"), true) ?? [];

            if (key_exists($fd, $clients)) {
                $client = new WsClient($fd, $clients[$fd]);

                $this->lobbyChannel->unsubscribe($client);
                $this->mainChannel->unsubscribe($client);

                unset($clients[$fd]);

                $this->redisClient->set("clients", json_encode($clients));
            }
        });

        $this->wsServer->on('finish', function() {});

        $this->wsServer->start();
    }

    public function __destruct()
    {
        $clients = json_decode($this->redisClient->get("clients"), true) ?? [];

        foreach ($clients as $fd => $token) {
            $client = new WsClient($fd, $token);

            $this->lobbyChannel->unsubscribe($client);
            $this->mainChannel->unsubscribe($client);
        }

        $this->redisClient->set("clients", '[]');
    }
}
