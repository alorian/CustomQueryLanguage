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

    protected \SplStack $parsingStack;

    public function __construct(protected ParserCompiler $parserCompiler)
    {
        $parserCompiler->compile();
        $this->transitionTable = $parserCompiler->getTransitionTable();
    }

    /**
     * @throws ParserUnexpectedTokenException
     */
    public function parse(array $tokensList): QueryNode
    {
        $this->currentPos = 0;
        $this->tokensList = $tokensList;

        $this->parsingStack = new \SplStack();
        $this->parsingStack->push(0);

        $accepted = false;
        while (!$this->isAtEnd() && !$accepted) {
            $operation = $this->transitionTable->getTransition(
                $this->parsingStack->top(),
                $this->getCurrentToken()
            );

            switch ($operation::class) {
                case OperationAccept::class:
                    $accepted = true;
                    $this->parsingStack->pop();
                    break;

                case OperationShift::class:
                    $this->parsingStack->push($this->getCurrentToken());
                    $this->parsingStack->push($operation->nextState->index);
                    $this->next();
                    break;

                case OperationReduce::class:
                    /** @var AbstractNode $node */
                    $node = new $operation->rule->left();
                    for ($i = 0; $i < $operation->rule->length(); $i++) {
                        $this->parsingStack->pop();
                        $node->unshiftChildren($this->parsingStack->pop());
                    }

                    $operationGoTo = $this->transitionTable->getTransition($this->parsingStack->top(), $node);
                    $this->parsingStack->push($node);
                    $this->parsingStack->push($operationGoTo->nextState->index);
                    break;

                default:
                    throw new ParserUnexpectedTokenException($operation, $this->getCurrentToken());
            }
        }

        if ($accepted) {
            return $this->parsingStack->pop();//QueryNode
        }

        throw new ParserUnexpectedTokenException();
    }

    protected function getCurrentToken(): ?AbstractToken
    {
        return $this->tokensList[$this->currentPos] ?? null;
    }

    protected function next(): void
    {
        $this->currentPos++;
    }

    #[Pure]
    protected function isAtEnd(): bool
    {
        return $this->currentPos >= count($this->tokensList);
    }


}