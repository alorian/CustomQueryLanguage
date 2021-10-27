<?php

namespace App\ParserCompiler;

use App\Lexer\AbstractToken;
use App\Parser\AbstractNode;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationError;
use App\ParserCompiler\Operation\OperationGoTo;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;

class TransitionTable
{

    protected array $operationsMap = [];

    public function pushTransition(
        State $state,
        string $tokenClass,
        OperationAccept|OperationReduce|OperationShift|OperationGoTo $operation
    ): void {
        if (isset($this->operationsMap[$state->index][$tokenClass])) {
            throw new \RuntimeException('Map is already set for state: "' . $state->index . '", token:"' . $tokenClass . '"');
        }
        $this->operationsMap[$state->index][$tokenClass] = $operation;
    }

    public function getTransition(
        int $stateIndex,
        AbstractToken|AbstractNode $token
    ): OperationAccept|OperationReduce|OperationShift|OperationGoTo|OperationError {
        if (isset($this->operationsMap[$stateIndex][$token::class])) {
            return $this->operationsMap[$stateIndex][$token::class];
        }

        return $this->makeOperationError($stateIndex);
    }

    protected function makeOperationError(int $stateIndex): OperationError
    {
        $tokenClassesList = [];
        if (isset($this->operationsMap[$stateIndex])) {
            foreach ($this->operationsMap[$stateIndex] as $tokenClass => $operation) {
                if (is_subclass_of($tokenClass, AbstractToken::class)) {
                    $tokenClassesList[] = $tokenClass;
                }
            }
        }

        return new OperationError($tokenClassesList);
    }

}