<?php

namespace App\Exception;

use Throwable;

class LexerUnexpectedCharacterException extends \Exception
{

    /**
     * @param int $stringStartPos
     * @param Throwable|null $previous
     */
    public function __construct(
        protected string $char,
        protected int $charPos,
        Throwable $previous = null
    ) {
        parent::__construct('Unexpected character "' . $this->char . '" at: ' . $this->charPos, 0, $previous);
    }

}