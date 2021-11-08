<?php

namespace App\Parser\Node;

use App\Lexer\SimpleToken\ContainToken;
use App\Lexer\SimpleToken\NotContainToken;
use App\Parser\AbstractNode;

class ContainsOperatorNode extends AbstractNode
{

    public const RULES = [
        [
            ContainToken::class,
        ],
        [
            NotContainToken::class,
        ]
    ];

}