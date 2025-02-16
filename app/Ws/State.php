<?php

namespace App\Ws;

class State
{
    public int $online = 0;

    
    protected static ?State $instance = null;

    public static function get(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new State();
        }

        return static::$instance;
    }
}
