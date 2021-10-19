<?php

namespace App\Lexer\TypeToken;

use App\Lexer\AbstractToken;

class NumberToken extends AbstractToken
{

    public const LEXEME = '';

    public function __construct(
        public ?string $literal = null,
        public ?int $pos = null,
    ){
        parent::__construct($literal, $pos);
        $this->literal = (float)$this->literal;
    }
}