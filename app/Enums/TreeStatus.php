<?php

namespace App\Enums;

enum TreeStatus: string
{
    case Alive = 'alive';
    case Dead = 'dead';
    case NeedReplacement = 'need_replacement';
}
