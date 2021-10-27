<?php

namespace App\Parser\Node;

use App\Lexer\TypeToken\StringToken;
use App\Parser\AbstractNode;

class FieldNode extends AbstractNode
{

    public const RULES = [
        [
            StringToken::class
        ]
    ];

}