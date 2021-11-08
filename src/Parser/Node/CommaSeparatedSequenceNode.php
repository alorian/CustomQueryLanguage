<?php

namespace App\Parser\Node;

use App\Lexer\KeywordToken\InToken;
use App\Lexer\SimpleToken\CommaToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Parser\AbstractNode;

class CommaSeparatedSequenceNode extends AbstractNode
{

    public const RULES = [
        [
            AlphanumericValueNode::class
        ],
        [
            self::class,
            CommaToken::class,
            AlphanumericValueNode::class
        ]
    ];

}