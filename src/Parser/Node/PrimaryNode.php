<?php

namespace App\Parser\Node;

use App\Lexer\TypeToken\FalseToken;
use App\Lexer\TypeToken\NumberToken;
use App\Lexer\TypeToken\StringToken;
use App\Lexer\TypeToken\TrueToken;
use App\Parser\AbstractNode;

class PrimaryNode extends AbstractNode
{

    public const RULES = [
        [
            NumberToken::class,
        ],
        [
            StringToken::class,
        ],
        [
            TrueToken::class
        ],
        [
            FalseToken::class
        ]
    ];

}