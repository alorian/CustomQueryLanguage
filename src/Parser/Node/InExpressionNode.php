<?php

namespace App\Parser\Node;

use App\Lexer\KeywordToken\InToken;
use App\Lexer\KeywordToken\NotToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Parser\AbstractNode;

class InExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            FieldNode::class,
            InToken::class,
            ParenLeftToken::class,
            CommaSeparatedSequenceNode::class,
            ParenRightToken::class
        ],
        [
            FieldNode::class,
            NotToken::class,
            InToken::class,
            ParenLeftToken::class,
            CommaSeparatedSequenceNode::class,
            ParenRightToken::class
        ]
    ];

}