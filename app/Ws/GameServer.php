<?php

namespace App\Ws;

use App\Models\User;
use App\Ws\Channels\LobbyChannel;
use App\Ws\Channels\MainChannel;
use Illuminate\Support\Facades\Log;
use Swoole\WebSocket\Server as WsServer;
use Swoole\WebSocket\Frame;
use Swoole\Process;

class GameServer
{
    public WsServer $wsServer;

    public State $state;
    public EventBus $eventBus;

    public MainChannel $mainChannel;
    public LobbyChannel $lobbyChannel;

    public array $clients = [];

    public function __construct(
        WsServer $wsServer,
    ) {
        $this->wsServer = $wsServer;
        
        $this->state = new State();
        $this->eventBus = new EventBus();

        $this->mainChannel = new MainChannel($this->wsServer, $this->state, $this->eventBus);
        $this->lobbyChannel = new LobbyChannel($this->wsServer, $this->state, $this->eventBus);
    }

    public function start()
    {
        $this->wsServer->on("message", function (WsServer $server, Frame $frame) {
            (new Process(function() use ($server, $frame) {

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
    
                $client = new Client($frame->fd, $data['token']);

                $this->clients[$frame->fd] = $data['token'];
    
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
            }))->start();
        });

        $this->wsServer->on("close", function(WsServer $wsServer, int $fd) {
            if (key_exists($fd, $this->clients)) {
                $client = new Client($fd, $this->clients[$fd]);
                $this->lobbyChannel->unsubscribe($client);
                $this->mainChannel->unsubscribe($client);
            }
        });

        $this->wsServer->start();
    }
}
