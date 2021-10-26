<?php

namespace App\Lexer;

abstract class AbstractToken
{

    public const LEXEME = 'abstract';

    public function __construct(
        public ?string $value = null,
        public ?int $pos = null,
    ){
    }

}