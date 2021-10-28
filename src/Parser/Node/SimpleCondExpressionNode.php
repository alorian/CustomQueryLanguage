<?php

namespace App\Parser\Node;

use App\Parser\AbstractNode;

class SimpleCondExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            ComparisonExpressionNode::class
        ],
        [
            ContainsExpressionNode::class
        ]
    ];

}