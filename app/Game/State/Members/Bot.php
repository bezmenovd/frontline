<?php

namespace App\Game\State\Members;

class Bot
{
    public function __construct(
        public int $fakeId,
    ) {}

    public function getName(): string
    {
        return "Бот #" . $this->fakeId;
    }
}
