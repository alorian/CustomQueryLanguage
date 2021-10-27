<?php

namespace App\Parser\Node;

use App\Lexer\KeywordToken\OrToken;
use App\Parser\AbstractNode;

class ConditionalExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            ConditionalTermNode::class
        ],
        [
            self::class,
            OrToken::class,
            ConditionalTermNode::class
        ]
    ];

}