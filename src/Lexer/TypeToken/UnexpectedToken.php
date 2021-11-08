<?php

namespace App\Lexer\TypeToken;

use App\Lexer\AbstractToken;

/**
 * Service auxiliary token
 * This token cannot be found in the real code.
 */
class UnexpectedToken extends AbstractToken
{

    public const LEXEME = 'UNEXPECTED';

}