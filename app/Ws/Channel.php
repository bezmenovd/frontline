<?php

namespace App\Ws;

use Illuminate\Support\Facades\Log;
use Swoole\WebSocket\Server;

abstract class Channel 
{
    public array $clients = [];

    public function __construct(
        public Server $server,
        public State $state,
        public EventBus $eventBus,
    ) {
        $this->initialize();
    }

    abstract public function channel(): string;
    abstract public function initialize(): void;
    abstract public function handle(Client $client, string $type, mixed $payload): void;

    public function send(Client $client, Message $message): void
    {
        $message->channel = $this->channel();

        try {
            $this->server->push($client->fd, $message->toString());
        } catch (\Exception $e) {
            Log::error("ws channel: client not connected: " . $e->getMessage(), ['client' => $client]);
        }
    }

    public function broadcast(Message $message): void
    {
        foreach ($this->clients as $client) {
            /** @var Client $client */
            $this->send($client, $message);
        }
    }

    public function subscribe(Client $client): bool 
    {
        foreach ($this->clients as $existingClient) {
            /** @var Client $client */
            if ($existingClient->token === $client->token) {
                $this->send($client, new Message("already_subscribed"));
                return false;
            }
        }
        
        $this->clients[] = $client;

        return true;
    }

    public function unsubscribe(Client $client): void 
    {
        $this->clients = array_filter($this->clients, fn(Client $c) => $c->fd !== $client->fd);
    }
}
