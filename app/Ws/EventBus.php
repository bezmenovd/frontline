<?php

namespace App\Ws;

class EventBus
{
    public array $handlers = [];

    public function on(string $event, callable $callback)
    {
        $this->handlers[$event][] = $callback;
    }

    public function dispatch(string $event)
    {
        foreach ($this->handlers[$event] as $handler) {
            $handler();
        }
    }
}
