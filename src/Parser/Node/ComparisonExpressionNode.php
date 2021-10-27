<?php

namespace App\Parser\Node;

use App\Parser\AbstractNode;

class ComparisonExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            FieldNode::class,
            ComparisonOperatorNode::class,
            PrimaryNode::class
        ]
    ];

}