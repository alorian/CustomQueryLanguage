<?php

namespace App\Parser\Node;

use App\Lexer\SimpleToken\TildaToken;
use App\Parser\AbstractNode;

class ContainsOperatorNode extends AbstractNode
{

    public const RULES = [
        [
            TildaToken::class,
        ]
    ];

}