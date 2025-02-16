<?php

namespace App\Ws;

class Events
{
    public array $handlers = [];


    protected static ?Events $instance = null;

    public static function get(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new Events();
        }

        return static::$instance;
    }

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
