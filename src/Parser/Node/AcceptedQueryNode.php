<?php

namespace App\Parser\Node;

use App\Lexer\TypeToken\EolToken;
use App\Parser\AbstractNode;

class AcceptedQueryNode extends AbstractNode
{

    public const RULES = [
        [
            QueryNode::class,
        ]
    ];

}