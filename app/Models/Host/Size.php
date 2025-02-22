<?php

namespace App\Models\Host;

enum Size: string
{
    case x64 = "64x64";
    case x128 = "128x128";
    case x256 = "256x256";
}
