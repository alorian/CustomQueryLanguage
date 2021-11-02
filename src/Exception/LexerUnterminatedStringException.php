<?php

namespace App\Exception;

use Exception;
use Throwable;

class LexerUnterminatedStringException extends Exception
{

    /**
     * @param int $stringStartPos
     * @param Throwable|null $previous
     */
    public function __construct(
        public int $stringStartPos,
        Throwable $previous = null
    ) {
        parent::__construct('String started at: ' . $this->stringStartPos, 0, $previous);
    }

}