<?php

namespace App\ParserCompiler\Operation;

use App\ParserCompiler\State;

class OperationGoTo
{
    public function __construct(
        public State $currentState,
        public State $nextState
    )
    {
    }
}