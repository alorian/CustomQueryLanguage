<?php

namespace App\Parser\Node;

use App\Lexer\DateToken\CurrentDateModifierToken;
use App\Parser\AbstractNode;

class DateValue extends AbstractNode
{

    public const RULES = [
        [
            CurrentDateModifierToken::class,
        ],
    ];

}