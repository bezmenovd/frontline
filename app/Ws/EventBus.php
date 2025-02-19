<?php

namespace App\Ws;

class EventBus
{
    public array $handlers = [];

    public function on(string $type, callable $callback): void
    {
        $this->handlers[$type][] = $callback;
    }

    public function dispatch(string $type, ...$args): void
    {
        foreach ($this->handlers[$type] as $handler) {
            $handler(...$args);
        }
    }
}
