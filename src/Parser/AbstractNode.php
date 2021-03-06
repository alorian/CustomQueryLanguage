<?php

namespace App\Parser;

use App\Lexer\AbstractToken;
use App\Transpiler\VisitorInterface;

class AbstractNode
{
    public const RULES = [];

    /** @var array|AbstractNode[]|AbstractToken[] */
    public array $children = [];

    public function __construct() {}

    public function unshiftChildren(AbstractToken|AbstractNode $node): void
    {
        array_unshift($this->children, $node);
    }

    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visit($this);
    }

}