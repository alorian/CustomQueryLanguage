<?php

namespace App\Transpiler;

use App\Exception\LexerUnexpectedCharacterException;
use App\Exception\LexerUnterminatedStringException;
use App\Exception\ParserUnexpectedTokenException;
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
     */
    public function transpile(string $rawQuery): string
    {
        $tokensList = $this->lexer->analyze($rawQuery);
        $queryNode = $this->parser->parse($tokensList);

        return $queryNode->accept($this->sqlVisitor);
    }

}