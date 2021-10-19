<?php

namespace App\Lexer;

abstract class AbstractToken
{

    public function __construct(
        public ?string $literal = null,
        public ?int $pos = null,
    ){
    }

}