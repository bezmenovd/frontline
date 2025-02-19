<?php

namespace App\Ws;

use Illuminate\Support\Facades\Log;
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
        } catch (\Exception $e) {
            print("ws channel: client not connected: " . $e->getMessage() . "\n");
            Log::error("ws channel: client not connected: " . $e->getMessage(), ['client' => $client]);
        }
    }

    public function broadcast(Message $message): void
    {
        $clients = $this->getClients();

        foreach ($clients as $client) {
            /** @var WsClient $client */
            $this->send($client, $message);
        }
    }

    public function subscribe(WsClient $client): bool 
    {
        $clients = $this->getClients();

        foreach ($clients as $existingClient) {
            /** @var WsClient $client */
            if ($existingClient->token === $client->token) {
                $this->send($client, new Message("already_subscribed"));
                return false;
            }
        }
        
        $clients[] = $client;

        $this->setClients($clients);

        return true;
    }

    public function unsubscribe(WsClient $client): void 
    {
        $clients = $this->getClients();

        $clients = array_filter($clients, fn(WsClient $c) => $c->token !== $client->token);

        $this->setClients($clients);
    }

    public function getClients(): array
    {
        $data = json_decode($this->redisClient->get("channel:" . $this->channel() . ":clients"), true) ?? [];
        
        return array_map(fn(array $item) => new WsClient($item['fd'], $item['token']), array_filter($data, fn($item) => is_array($item)));
    }

    /**
     * @param WsClient[] $clients
     */
    public function setClients(array $clients): void
    {
        $this->redisClient->set("channel:" . $this->channel() . ":clients", json_encode(array_map(fn(WsClient $client) => [
            'fd' => $client->fd,
            'token' => $client->token,
        ], $clients)));
    }
}
