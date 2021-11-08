<?php

namespace App\Parser\Node;

use App\Parser\AbstractNode;

class SimpleCondExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            AlphanumericComparisonExpressionNode::class
        ],
        [
            DateComparisonExpression::class
        ],
        [
            InExpressionNode::class
        ],
        [
            ContainsExpressionNode::class
        ],
        [
            NullComparisonExpressionNode::class
        ]
    ];

}