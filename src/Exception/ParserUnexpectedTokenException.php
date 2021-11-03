<?php

namespace App\Exception;

use App\Lexer\AbstractToken;
use App\Lexer\TypeToken\EolToken;
use App\ParserCompiler\Operation\OperationError;
use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ParserUnexpectedTokenException extends Exception
{
    /**
     * @param OperationError|null $operationError
     * @param AbstractToken|null $currentToken
     * @param Throwable|null $previous
     */
    #[Pure]
    public function __construct(
        public ?OperationError $operationError = null,
        public ?AbstractToken $currentToken = null,
        Throwable $previous = null
    ) {
        if ($this->currentToken === null || $this->currentToken instanceof EolToken) {
            $message = 'Unexpected end of line';
        } else {
            $message = 'Unexpected token "' . $this->currentToken->value . '" ';
            $message .= "[" . $this->currentToken::LEXEME . "]";
            $message .= ' at ' . $this->currentToken->pos;
        }

        if ($operationError !== null && !empty($operationError->expectedTokensList)) {
            $message .= '. Expected ';
            $types = [];
            foreach ($this->operationError->expectedTokensList as $className) {
                if (is_subclass_of($className, AbstractToken::class)) {
                    $types[] = '"' . $className::LEXEME . '"';
                }
            }
            $message .= ' one of the ' . implode(', ', $types) . ' tokens';
        }

        parent::__construct($message, 0, $previous);
    }
}