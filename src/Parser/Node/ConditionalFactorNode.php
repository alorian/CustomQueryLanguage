<?php

namespace App\Parser\Node;

use App\Lexer\KeywordToken\NotToken;
use App\Parser\AbstractNode;

class ConditionalFactorNode extends AbstractNode
{

    public const RULES = [
        [
            NotToken::class,
            ConditionalPrimaryNode::class
        ],
        [
            ConditionalPrimaryNode::class
        ]
    ];

}