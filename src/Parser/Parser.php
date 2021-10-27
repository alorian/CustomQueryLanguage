<?php

namespace App\Parser;

use App\Exception\ParserUnexpectedTokenException;
use App\Lexer\AbstractToken;
use App\Parser\Node\QueryNode;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;
use App\ParserCompiler\ParserCompiler;
use App\ParserCompiler\TransitionTable;
use JetBrains\PhpStorm\Pure;

class Parser
{
    /** @var AbstractToken[] */
    protected array $tokensList;

    protected int $currentPos;

    protected TransitionTable $transitionTable;

    public function __construct(protected ParserCompiler $parserCompiler)
    {
        $parserCompiler->compile();
        $this->transitionTable = $parserCompiler->getTransitionTable();
    }

    /**
     * @throws ParserUnexpectedTokenException
     */
    public function parse(array $tokensList): ?QueryNode
    {
        $this->currentPos = 0;
        $this->tokensList = $tokensList;

        $parsingStack = new \SplStack();
        $parsingStack->push(0);

        $valuesStack = new \SplStack();

        $accepted = false;
        $token = $this->advance();
        while (!$accepted) {
            $operation = $this->transitionTable->getTransition(
                $parsingStack->top(),
                $token ?? $valuesStack->top()
            );

            switch ($operation::class) {
                case OperationAccept::class:
                    $accepted = true;
                    break;

                case OperationShift::class:
                    $parsingStack->push($operation->nextState->index);
                    $valuesStack->push($token);
                    $token = $this->advance();
                    break;

                case OperationReduce::class:
                    /** @var AbstractNode $node */
                    $node = new $operation->rule->left();
                    for ($i = 0; $i < $operation->rule->length(); $i++) {
                        $node->unshift($valuesStack->pop());
                        $parsingStack->pop();
                    }

                    $valuesStack->push($node);
                    $operationGoTo = $this->transitionTable->getTransition($parsingStack->top(), $node);
                    $parsingStack->push($operationGoTo->nextState->index);
                    break;

                default:
                    throw new ParserUnexpectedTokenException($operation, $token);
            }
        }
        $valuesStack->pop();//EolToken

        return $valuesStack->pop();//QueryNode
    }

    protected function advance(): ?AbstractToken
    {
        return $this->tokensList[$this->currentPos++] ?? null;
    }

    #[Pure]
    protected function isAtEnd(): bool
    {
        return $this->currentPos >= count($this->tokensList);
    }


}