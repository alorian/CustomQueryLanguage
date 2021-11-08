<?php

namespace App\Parser\Node;

use App\Lexer\SimpleToken\MinusToken;
use App\Parser\AbstractNode;

class DateComparisonExpression extends AbstractNode
{

    public const RULES = [
        [
            FieldNode::class,
            ComparisonOperatorNode::class,
            DateValueNode::class
        ],
        [
            FieldNode::class,
            ComparisonOperatorNode::class,
            MinusToken::class,
            DateValueNode::class
        ]
    ];

}