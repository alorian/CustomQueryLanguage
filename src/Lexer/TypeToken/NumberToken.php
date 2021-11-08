<?php

namespace App\Lexer\TypeToken;

use App\Lexer\AbstractToken;

class NumberToken extends AbstractToken
{

    public const LEXEME = 'NUMBER';

    public function __construct(
        public ?string $value = null,
        public ?int $pos = null,
    ){
        parent::__construct($value, $pos);
        $this->value = (float)$this->value;
    }
}