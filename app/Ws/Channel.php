<?php

namespace App\Ws;

abstract class Channel 
{
    public array $clients = [];

    public function subscribe(Client $client): void 
    {
        if (count(array_filter($this->clients, fn(Client $c) => spl_object_id($c->connection) === spl_object_id($client->connection))) == 0) {
            $this->clients[] = $client;
        }
    }

    public function unsubscribe(Client $client): void 
    {
        $this->clients = array_filter($this->clients, fn(Client $c) => spl_object_id($c->connection) !== spl_object_id($client->connection));
    }

    public function broadcast(Message $message): void
    {
        foreach ($this->clients as $client) {
            /** @var Client $client */
            $client->connection->text($message->toString());
        }
    }

    abstract public function handle(Client $client, string $type, mixed $payload): void;
}
