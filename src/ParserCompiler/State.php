<?php

namespace App\ParserCompiler;

use App\Parser\AbstractNode;

class State
{

    /**
     * @param array|StateCondition[] $conditions
     */
    public function __construct(
        public array $conditions,
        public ?int $index = null
    ) {
    }

    public function pushCondition(StateCondition $stateCondition): void
    {
        $this->conditions[$stateCondition->index] = $stateCondition;
    }

}