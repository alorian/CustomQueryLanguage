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
        public string $char,
        public int $charPos,
        Throwable $previous = null
    ) {
        parent::__construct('Unexpected character "' . $this->char . '" at: ' . $this->charPos, 0, $previous);
    }

}