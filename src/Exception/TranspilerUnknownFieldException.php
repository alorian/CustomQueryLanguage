<?php

namespace App\Exception;

use App\Lexer\TypeToken\StringToken;
use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class TranspilerUnknownFieldException extends Exception
{
    /**
     * @param StringToken $token
     * @param Throwable|null $previous
     */
    #[Pure]
    public function __construct(
        protected StringToken $token,
        Throwable $previous = null
    ) {
        $message = 'Field "' . $token->value . '" [' . $this->token->pos . '] does not exist';
        parent::__construct($message, 0, $previous);
    }
}