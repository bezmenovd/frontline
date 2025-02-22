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
        if (key_exists($type, $this->handlers)) {
            foreach ($this->handlers[$type] as $handler) {
                $handler(...$args);
            }
        }
    }
}
