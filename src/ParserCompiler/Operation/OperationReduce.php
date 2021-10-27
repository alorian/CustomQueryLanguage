<?php

namespace App\ParserCompiler\Operation;

use App\ParserCompiler\GrammarRule;

class OperationReduce
{

    public function __construct(
        public GrammarRule $rule
    ) {
    }

}