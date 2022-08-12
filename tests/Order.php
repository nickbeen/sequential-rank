<?php

namespace NickBeen\SequentialRank\Tests;

enum Order: string
{
    case RED = 'red';
    case BLUE = 'blue';
    case YELLOW = 'yellow';

    public function order(): int
    {
        return match ($this) {
            self::RED => 1,
            self::BLUE => 2,
            self::YELLOW => 3,
        };
    }
}
