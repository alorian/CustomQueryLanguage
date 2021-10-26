<?php

namespace App\Lexer;

use App\Exception\LexerUnexpectedCharacterException;
use App\Exception\LexerUnterminatedStringException;
use App\Lexer\KeywordToken\NotToken;
use App\Lexer\SimpleToken\CommaToken;
use App\Lexer\DateToken\CurrentDateModifierToken;
use App\Lexer\SimpleToken\DotToken;
use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\GreaterEqualToken;
use App\Lexer\SimpleToken\GreaterToken;
use App\Lexer\SimpleToken\LessEqualToken;
use App\Lexer\SimpleToken\LessToken;
use App\Lexer\SimpleToken\MinusToken;
use App\Lexer\SimpleToken\NotEqualToken;
use App\Lexer\TypeToken\EolToken;
use App\Lexer\TypeToken\NumberToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Lexer\SimpleToken\PlusToken;
use App\Lexer\SimpleToken\StarToken;
use App\Lexer\TypeToken\StringToken;
use App\Lexer\SimpleToken\TildaToken;
use JetBrains\PhpStorm\Pure;

class Lexer
{

    protected array $tokensList;
    protected int $lexemeStartPos;
    protected int $currentPos;
    protected string $code;

    public const KEYWORDS = [
        // keywords
        KeywordToken\AndToken::LEXEME => KeywordToken\AndToken::class,
        KeywordToken\ByToken::LEXEME => KeywordToken\ByToken::class,
        KeywordToken\InToken::LEXEME => KeywordToken\InToken::class,
        KeywordToken\IsToken::LEXEME => KeywordToken\IsToken::class,
        KeywordToken\NotToken::LEXEME => KeywordToken\NotToken::class,
        KeywordToken\OrderToken::LEXEME => KeywordToken\OrderToken::class,
        KeywordToken\OrToken::LEXEME => KeywordToken\OrToken::class,

        // types
        TypeToken\EmptyToken::LEXEME => TypeToken\EmptyToken::class,
        TypeToken\NullToken::LEXEME => TypeToken\NullToken::class,
        TypeToken\TrueToken::LEXEME => TypeToken\TrueToken::class,
        TypeToken\FalseToken::LEXEME => TypeToken\FalseToken::class,
    ];

    /**
     * @throws LexerUnterminatedStringException
     * @throws LexerUnexpectedCharacterException
     */
    public function analyze(string $code): array
    {
        $this->tokensList = [];
        $this->lexemeStartPos = 0;
        $this->currentPos = 0;
        $this->code = $code;

        while (!$this->isAtEnd()) {
            $this->lexemeStartPos = $this->currentPos;
            $this->scanToken();
        }
        $this->addToken(EolToken::class);

        return $this->tokensList;
    }

    /**
     * @throws LexerUnterminatedStringException
     * @throws LexerUnexpectedCharacterException
     */
    protected function scanToken(): void
    {
        $char = $this->advance();
        switch ($char) {
            case CommaToken::LEXEME:
                $this->addToken(CommaToken::class);
                break;

            case DotToken::LEXEME:
                $this->addToken(DotToken::class);
                break;

            case EqualToken::LEXEME:
                $this->addToken(EqualToken::class);
                break;

            case MinusToken::LEXEME:
                $this->addToken(MinusToken::class);
                break;

            case ParenLeftToken::LEXEME:
                $this->addToken(ParenLeftToken::class);
                break;

            case ParenRightToken::LEXEME:
                $this->addToken(ParenRightToken::class);
                break;

            case PlusToken::LEXEME:
                $this->addToken(PlusToken::class);
                break;

            case StarToken::LEXEME:
                $this->addToken(StarToken::class);
                break;

            case TildaToken::LEXEME:
                $this->addToken(TildaToken::class);
                break;

            case '!':
                $this->addToken($this->match('=') ? NotEqualToken::class : NotToken::class);
                break;

            case '<':
                $this->addToken($this->match('=') ? LessEqualToken::class : LessToken::class);
                break;

            case '>':
                $this->addToken($this->match('=') ? GreaterEqualToken::class : GreaterToken::class);
                break;

            case ' ':
            case "\r":
            case "\t":
            case "\n":
                // Ignore whitespace.
                break;

            case '"':
            case "'":
            case '`':
                $this->string($char);
                break;

            default:
                if ($this->isDigit($char)) {
                    $this->number();
                } elseif ($this->isAlpha($char)) {
                    $this->identifier();
                } else {
                    throw new LexerUnexpectedCharacterException($char, $this->currentPos);
                }
        }
    }

    protected function advance(): ?string
    {
        return $this->code[$this->currentPos++] ?? null;
    }

    protected function prevSymbol(): ?string
    {
        return $this->code[$this->currentPos - 1] ?? null;
    }

    protected function nextSymbol(): ?string
    {
        return $this->code[$this->currentPos] ?? null;
    }

    protected function nextSecondSymbol(): ?string
    {
        return $this->code[$this->currentPos + 1] ?? null;
    }

    protected function identifier(): void
    {
        while (!$this->isAtEnd() && $this->isAlphaNumeric($this->nextSymbol())) {
            $this->advance();
        }

        $lexeme = substr($this->code, $this->lexemeStartPos, ($this->currentPos - $this->lexemeStartPos));
        $lexeme = strtolower($lexeme);
        if (isset($this::KEYWORDS[$lexeme])) {
            $this->addToken($this::KEYWORDS[$lexeme]);
        } else {
            $this->addToken(StringToken::class);
        }
    }

    /**
     * @throws LexerUnexpectedCharacterException
     */
    protected function number(): void
    {
        while ($this->isDigit($this->nextSymbol())) {
            $this->advance();
        }

        if ($this->nextSymbol() === '.' && $this->isDigit($this->nextSecondSymbol())) {
            // float number
            $this->advance();
            while ($this->isDigit($this->nextSymbol())) {
                $this->advance();
            }
        } elseif ($this->isAlpha($this->nextSymbol())) {
            if (isset(CurrentDateModifierToken::MODIFIERS[$this->nextSymbol()])) {
                $this->advance();
                $this->addToken(CurrentDateModifierToken::class);
            } else {
                throw new LexerUnexpectedCharacterException($this->nextSymbol(), $this->currentPos);
            }
        } else {
            // integer
            $this->addToken(NumberToken::class);
        }
    }

    /**
     * @throws LexerUnterminatedStringException
     */
    protected function string(string $terminator): void
    {
        while (true) {
            if ($this->nextSymbol() === $terminator && $this->prevSymbol() !== '\\') {
                break;
            }

            if ($this->isAtEnd()) {
                break;
            }

            $this->advance();
        }

        if ($this->isAtEnd()) {
            throw new LexerUnterminatedStringException($this->lexemeStartPos);
        }

        $this->advance();// adding terminator symbol

        $literal = substr($this->code, $this->lexemeStartPos, ($this->currentPos - $this->lexemeStartPos));
        $literal = substr($literal, 1, -1);

        $this->addToken(StringToken::class, $literal);
    }

    protected function match(string $char): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }

        if (($this->code[$this->currentPos] ?? null) !== $char) {
            return false;
        }

        $this->currentPos++;
        return true;
    }

    protected function isAtEnd(): bool
    {
        return $this->currentPos >= strlen($this->code);
    }

    protected function addToken(string $tokenClass, string $value = null): void
    {
        if ($value === null) {
            $value = substr($this->code, $this->lexemeStartPos, ($this->currentPos - $this->lexemeStartPos));
        }
        $this->tokensList[] = new $tokenClass($value, $this->lexemeStartPos);
    }

    protected function isDigit(?string $char): bool
    {
        if ($char === null) {
            return false;
        }
        return $char >= '0' && $char <= '9';
    }

    protected function isAlpha(?string $char): bool
    {
        if ($char === null) {
            return false;
        }
        return ($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z') || $char === '_';
    }

    #[Pure]
    protected function isAlphaNumeric(?string $char): bool
    {
        if ($char === null) {
            return false;
        }
        return $this->isAlpha($char) || $this->isDigit($char);
    }

}