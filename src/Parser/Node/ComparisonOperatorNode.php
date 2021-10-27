<?php

namespace App\Parser\Node;

use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\GreaterEqualToken;
use App\Lexer\SimpleToken\GreaterToken;
use App\Lexer\SimpleToken\LessEqualToken;
use App\Lexer\SimpleToken\LessToken;
use App\Lexer\SimpleToken\NotEqualToken;
use App\Parser\AbstractNode;

class ComparisonOperatorNode extends AbstractNode
{

    public const RULES = [
        [
            EqualToken::class
        ],
        [
            NotEqualToken::class
        ],
        [
            LessToken::class
        ],
        [
            LessEqualToken::class
        ],
        [
            GreaterToken::class
        ],
        [
            GreaterEqualToken::class
        ]
    ];

}