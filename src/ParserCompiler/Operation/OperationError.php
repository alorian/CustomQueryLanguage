<?php

namespace App\ParserCompiler\Operation;

class OperationError
{
    public function __construct(
        public array $expectedTokensList = [],
    )
    {
    }
}