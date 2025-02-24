<?php

namespace App\Game;

use App\Game\State\Map;

class State
{
    public array $players;
    public Map $map;

    public function __construct()
    {
        $this->map = new Map();
    }
}
