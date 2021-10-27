<?php

namespace App\Parser\Node;

use App\Lexer\TypeToken\EolToken;
use App\Parser\AbstractNode;

class QueryNode extends AbstractNode
{

    public const RULES = [
        [
            ConditionalExpressionNode::class,
        ]
    ];

}