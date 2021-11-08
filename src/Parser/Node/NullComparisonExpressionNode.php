<?php

namespace App\Parser\Node;

use App\Lexer\KeywordToken\InToken;
use App\Lexer\KeywordToken\IsToken;
use App\Lexer\KeywordToken\NotToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Lexer\TypeToken\EmptyToken;
use App\Lexer\TypeToken\NullToken;
use App\Parser\AbstractNode;

class NullComparisonExpressionNode extends AbstractNode
{

    public const RULES = [
        [
            FieldNode::class,
            IsToken::class,
            NullToken::class,
        ],
        [
            FieldNode::class,
            IsToken::class,
            NotToken::class,
            NullToken::class,
        ],
        [
            FieldNode::class,
            IsToken::class,
            EmptyToken::class,
        ],
        [
            FieldNode::class,
            IsToken::class,
            NotToken::class,
            EmptyToken::class,
        ],
    ];

}