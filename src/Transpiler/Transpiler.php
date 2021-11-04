<?php

namespace App\Transpiler;

use App\Exception\LexerUnexpectedCharacterException;
use App\Exception\LexerUnterminatedStringException;
use App\Exception\ParserUnexpectedTokenException;
use App\Exception\TranspilerUnknownFieldException;
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

    /**
     * @param string $rawQuery
     * @return string
     * @throws LexerUnexpectedCharacterException
     * @throws LexerUnterminatedStringException
     * @throws ParserUnexpectedTokenException
     * @throws TranspilerUnknownFieldException
     */
    public function transpile(string $rawQuery): string
    {
        $tokensList = $this->lexer->analyze($rawQuery);
        $queryNode = $this->parser->parse($tokensList);

        $sqlQuery = $queryNode->accept($this->sqlVisitor);

        $exceptionsList = $this->sqlVisitor->getExceptionsList();
        if (!empty($exceptionsList)) {
            throw $exceptionsList[0];
        }

        return $sqlQuery;
    }

}