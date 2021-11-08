<?php

namespace App\Parser\Node;

use App\Parser\AbstractNode;

class ContainsExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            FieldNode::class,
            ContainsOperatorNode::class,
            AlphanumericValueNode::class
        ]
    ];

}