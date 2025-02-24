<?php

namespace App\Ws;

use Swoole\WebSocket\Server;
use Predis\Client;
use App\Ws\Client as WsClient;

abstract class Channel 
{
    public function __construct(
        public Server $server,
        public Client $redisClient,
        public EventBus $eventBus,
    ) {
        $this->initialize();
    }

    abstract public function channel(): string;
    abstract public function initialize(): void;
    abstract public function handle(WsClient $client, string $type, mixed $payload): void;

    public function send(WsClient $client, Message $message): void
    {
        $message->channel = $this->channel();

        try {
            $this->server->push($client->fd, $message->toString());
        } catch (\Exception $e) {}
    }

    public function broadcast(WsClient $client, Message $message, $others = false): void
    {
        $clients = $this->getClients($client);

        foreach ($clients as $c) {
            /** @var WsClient $c */
            if ($others && $c->fd == $client->fd) {
                continue;
            }
            $this->send($c, $message);
        }
    }

    public function subscribe(WsClient $client): bool 
    {
        $clients = $this->getClients($client);

        foreach ($clients as $key => $existingClient) {
            /** @var WsClient $existingClient */
            if ($existingClient->token === $client->token) {
                // print("already subscribed\n");
                try {
                    $this->server->disconnect($existingClient->fd);
                } catch (\Exception $e) {}
                unset($clients[$key]);
            }
        }
        
        $clients[] = $client;

        $this->setClients($client, $clients);

        return true;
    }

    public function unsubscribe(WsClient $client): void 
    {
        $clients = $this->getClients($client);

        $clients = array_filter($clients, fn(WsClient $c) => $c->token !== $client->token);

        $this->setClients($client, $clients);
    }

    public function getClients(WsClient $client): array
    {
        $data = json_decode($this->redisClient->get("channel:" . $this->channel() . ":clients"), true);

        if (! is_array($data)) {
            return [];
        }
        
        return array_map(fn(array $item) => new WsClient($item['fd'], $item['token']), array_filter($data, fn($item) => is_array($item)));
    }

    /**
     * @param WsClient[] $clients
     */
    public function setClients(WsClient $client, array $clients): void
    {
        $this->redisClient->set("channel:" . $this->channel() . ":clients", json_encode(array_map(fn(WsClient $c) => [
            'fd' => $c->fd,
            'token' => $c->token,
        ], $clients)));
    }
}
