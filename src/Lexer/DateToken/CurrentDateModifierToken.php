<?php

namespace App\Lexer\DateToken;

use App\Lexer\AbstractToken;

class CurrentDateModifierToken extends AbstractToken
{

    public const MODIFIERS = [
        'Y' => 'year',
        'y' => 'year',
        'M' => 'month',
        'D' => 'day',
        'd' => 'day',
        'W' => 'week',
        'w' => 'week',
        'H' => 'hour',
        'h' => 'hour',
        'm' => 'minute',
        'S' => 'second',
        's' => 'second',
    ];

    public const LEXEME = '';

}