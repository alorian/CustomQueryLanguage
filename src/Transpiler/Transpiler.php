<?php

namespace App\Transpiler;

use App\Lexer\Lexer;
use App\Parser\Parser;

class Transpiler
{

    public function __construct(
        protected Lexer $lexer,
        protected Parser $parser,
        protected SqlVisitor $sqlVisitor
    ) {
    }


    public function transpile(CustomQueryState $customQueryState): string
    {
        $sqlQueryPart = '';

        try {
            $tokensList = $this->lexer->analyze($customQueryState->getQuery());
            $queryNode = $this->parser->parse($tokensList);

            $this->sqlVisitor->resetExceptionsList();
            $sqlQueryPart = $queryNode->accept($this->sqlVisitor);

            foreach ($this->sqlVisitor->getExceptionsList() as $exception) {
                $customQueryState->pushError($exception);
            }
        } catch (\Throwable $exception) {
            $customQueryState->pushError($exception);
        }

        return $sqlQueryPart;
    }

}