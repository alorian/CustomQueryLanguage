<?php

namespace App\Exception;

use App\Lexer\AbstractToken;
use App\ParserCompiler\Operation\OperationError;
use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ParserUnexpectedTokenException extends Exception
{
    /**
     * @param OperationError $operationError
     * @param AbstractToken|null $currentToken
     * @param Throwable|null $previous
     */
    #[Pure]
    public function __construct(
        protected OperationError $operationError,
        protected ?AbstractToken $currentToken = null,
        Throwable $previous = null
    ) {
        if ($this->currentToken !== null) {
            $message = 'Unexpected token "' . $this->currentToken->value . '" ';
            $message .= "[" . $this->currentToken::LEXEME . "]";
            $message .= ' at ' . $this->currentToken->pos;
        } else {
            $message = 'Unexpected end of string';
        }

        if (!empty($operationError->expectedTokensList)) {
            $message .= '. Expected ';
            $types = [];
            foreach ($this->operationError->expectedTokensList as $className) {
                $types[] = '"' . $className::LEXEME . '"';
            }
            $message .= ' on of the ' . implode(', ', $types) . ' tokens';
        }

        parent::__construct($message, 0, $previous);
    }
}