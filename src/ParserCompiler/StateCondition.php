<?php

namespace App\ParserCompiler;

use App\Parser\AbstractNode;

class StateCondition
{

    public function __construct(
        public GrammarRule $rule,
        public int $markerPos,
        public int $index
    ) {
    }

    public function getNextClassName(): ?string
    {
        return $this->rule->get($this->markerPos);
    }

    public function getPrevClassName(): ?string
    {
        return $this->rule->get($this->markerPos - 1);
    }

    public function markedIsNonTerminal(): bool
    {
        if (is_subclass_of($this->marked(), AbstractNode::class)) {
            return true;
        }

        return false;
    }

    public function marked(): ?string
    {
        return $this->rule->get($this->markerPos);
    }

}