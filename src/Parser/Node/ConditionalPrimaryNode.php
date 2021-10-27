<?php

namespace App\Parser\Node;

use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Parser\AbstractNode;

class ConditionalPrimaryNode extends AbstractNode
{

    public const RULES = [
        [
            SimpleCondExpressionNode::class
        ],
        [
            ParenLeftToken::class,
            ConditionalExpressionNode::class,
            ParenRightToken::class
        ]
    ];

}