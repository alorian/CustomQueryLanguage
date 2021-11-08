<?php

namespace App\Parser\Node;

use App\Parser\AbstractNode;

class AlphanumericComparisonExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            FieldNode::class,
            ComparisonOperatorNode::class,
            AlphanumericValueNode::class
        ]
    ];

}