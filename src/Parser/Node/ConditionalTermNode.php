<?php

namespace App\Parser\Node;

use App\Lexer\KeywordToken\AndToken;
use App\Parser\AbstractNode;

class ConditionalTermNode extends AbstractNode
{

    public const RULES = [
        [
            ConditionalFactorNode::class
        ],
        [
            self::class,
            AndToken::class,
            ConditionalFactorNode::class
        ]
    ];

}