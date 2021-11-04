<?php

namespace App\Transpiler;

use App\Lexer\KeywordToken\AndToken;
use App\Lexer\KeywordToken\ByToken;
use App\Lexer\KeywordToken\InToken;
use App\Lexer\KeywordToken\IsToken;
use App\Lexer\KeywordToken\NotToken;
use App\Lexer\KeywordToken\OrderToken;
use App\Lexer\KeywordToken\OrToken;
use App\Lexer\Lexer;
use App\Lexer\SimpleToken\CommaToken;
use App\Lexer\SimpleToken\DotToken;
use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\GreaterEqualToken;
use App\Lexer\SimpleToken\GreaterToken;
use App\Lexer\SimpleToken\LessEqualToken;
use App\Lexer\SimpleToken\LessToken;
use App\Lexer\SimpleToken\NotEqualToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Lexer\SimpleToken\PlusToken;
use App\Lexer\SimpleToken\StarToken;
use App\Lexer\SimpleToken\TildaToken;
use App\Lexer\TypeToken\EmptyToken;
use App\Lexer\TypeToken\FalseToken;
use App\Lexer\TypeToken\NullToken;
use App\Lexer\TypeToken\TrueToken;
use App\Parser\Node\FieldNode;
use App\Parser\Node\QueryNode;
use App\Parser\Parser;

class SuggestionManager
{

    public function __construct(
        protected Lexer $lexer,
        protected Parser $parser,
        protected FieldsBag $fieldsBag
    ) {
    }

    public function addSuggestions(QueryState $queryState): void
    {
        if ($queryState->caretPos > 0) {
            try {
                // cutting initial request to the caret position
                $rawQuery = substr($queryState->getQuery(), 0, $queryState->caretPos);


                $latestInput = null;
                if (preg_match('#([\S]+)$#', $rawQuery, $matches) === 1) {
                    $latestInput = $matches[0];
                    // cutting raw query to parse it without the latest input
                    $rawQuery = substr($rawQuery, 0, -strlen($latestInput));
                }

                $tokensList = $this->lexer->analyze($rawQuery);
                try {
                    // trying to parse the cut query without accepting. Stopping before the QueryNode
                    $this->parser->parse($tokensList, QueryNode::class);
                } catch (\Throwable $e) {
                }

                $expectedTokensList = $this->parser->getExpectedTokens();
                $expectedInputStrings = $this->makeExpectedInputStrings($expectedTokensList);

                $queryState->suggestionsList = $this->makeSuggestions($expectedInputStrings, $latestInput);
            } catch (\Throwable $e) {
            }
        }
    }

    protected function makeSuggestions(array $stringsList, string $input = null): array
    {
        $suggestionsList = [];

        if ($input !== null) {
            foreach ($stringsList as $string) {
                if ($string !== $input && stripos($string, $input) === 0) {
                    $suggestion = [
                        'label' => $string,
                        'value' => substr($string, strlen($input))
                    ];
                    $suggestionsList[] = $suggestion;
                }
            }
        } else {
            foreach ($stringsList as $string) {
                $suggestion = [
                    'label' => $string,
                    'value' => $string
                ];
                $suggestionsList[] = $suggestion;
            }
        }

        return $suggestionsList;
    }

    protected function makeExpectedInputStrings(array $tokensList): array
    {
        $expectedStrings = [];

        foreach ($tokensList as $tokenClass) {
            switch ($tokenClass) {
                case AndToken::class:
                case ByToken::class:
                case InToken::class:
                case IsToken::class:
                case NotToken::class:
                case OrderToken::class:
                case OrToken::class:
                case CommaToken::class:
                case DotToken::class:
                case EqualToken::class:
                case GreaterEqualToken::class:
                case GreaterToken::class:
                case LessEqualToken::class:
                case LessToken::class:
                case NotEqualToken::class:
                case ParenLeftToken::class:
                case ParenRightToken::class:
                case PlusToken::class:
                case StarToken::class:
                case TildaToken::class:
                case EmptyToken::class:
                case NullToken::class:
                case FalseToken::class:
                case TrueToken::class:
                    $expectedStrings[] = $tokenClass::LEXEME;
                    break;

                case FieldNode::class:
                    $possibleFieldNames = $this->fieldsBag->getPossibleNames();
                    asort($possibleFieldNames);
                    foreach ($this->fieldsBag->getPossibleNames() as $fieldName) {
                        array_unshift($expectedStrings, $fieldName);
                    }
                    break;
            }
        }

        return $expectedStrings;
    }

}