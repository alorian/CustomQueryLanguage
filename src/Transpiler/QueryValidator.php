<?php

namespace App\Transpiler;

use App\Lexer\Lexer;
use App\Parser\Parser;

class QueryValidator
{
    public function __construct(
        protected Lexer $lexer,
        protected Parser $parser,
        protected SqlVisitor $sqlVisitor
    ) {
    }

    public function validate(QueryState $queryState): QueryState
    {
        try {
            $tokensList = $this->lexer->analyze($queryState->getQuery());
            $queryNode = $this->parser->parse($tokensList);
            $queryNode->accept($this->sqlVisitor);
        } catch (\Throwable $exception) {
            $queryState->valid = false;
            $queryState->errorsList[] = $exception;
        }

        return $queryState;
    }
}