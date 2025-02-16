<?php

namespace App\Ws;

use App\Models\User;
use App\Ws\Channels\MainChannel;
use App\Ws\Channels\LobbyChannel;
use Illuminate\Support\Facades\Log;
use WebSocket;

class ChannelManager
{
    public MainChannel $mainChannel;
    public LobbyChannel $lobbyChannel;

    public function __construct(
        protected WebSocket\Server $server,
    ) {
        $this->mainChannel = new MainChannel();
        $this->lobbyChannel = new LobbyChannel();

        $this->server->onText(function (WebSocket\Server $server, WebSocket\Connection $connection, WebSocket\Message\Message $message) {
            $data = json_decode($message->getContent(), true);

            if (! key_exists('token', $data) || ! key_exists('channel', $data) || ! key_exists('type', $data) || ! key_exists('payload', $data)) {
                Log::error("invalid ws message: " . $message->getContent());
                return;
            }

            $user = User::query()->where('token', $data['token'])->first();
            if (is_null($user)) {
                Log::error("user not authorized: " . $message->getContent());
                return;
            }

            $client = new Client($connection, $user);

            switch ($data['channel']) {
                case "main": 
                    if (! key_exists('channel', $data['payload'])) {
                        Log::error("invalid ws message: " . $message->getContent());
                        return;
                    }

                    switch ($data['payload']['channel']) {
                        case "main": 
                            if ($data['type'] === "subscribe") {
                                $this->mainChannel->subscribe($client);
                            } else if ($data['type'] === "unsubscribe") {
                                $this->mainChannel->unsubscribe($client);
                            }
                        break;
                        case "lobby": 
                            if ($data['type'] === "subscribe") {
                                $this->lobbyChannel->subscribe($client);
                            } else if ($data['type'] === "unsubscribe") {
                                $this->lobbyChannel->unsubscribe($client);
                            }
                    };
                break;
            };
        });

        $this->server->onClose(function (WebSocket\Server $server, WebSocket\Connection $connection, WebSocket\Message\Message $message) {
            $this->mainChannel->unsubscribe(new Client($connection));
            $this->lobbyChannel->unsubscribe(new Client($connection));
        });
    }
}
