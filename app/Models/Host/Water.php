<?php

namespace App\Models\Host;

enum Water: string
{
    case Low = "низкий";
    case Medium = "средний";
    case High = "высокий";
}
