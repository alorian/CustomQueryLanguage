<?php

namespace App\ParserCompiler;

/**
 * Deterministic finite-state machine
 * Build all possible states for given grammar rules set
 */
class FiniteStateMachine
{
    /** @var array|State[]  */
    protected array $statesList = [];

    /** @var array|StateCondition[] */
    protected array $stateConditionsList = [];

    protected array $transitionsList = [];

    protected array $stateConditionsMap = [];

    protected array $grammarRulesMap = [];

    protected int $nextStateIndex = 0;

    protected int $nextStateConditionIndex = 0;

    protected int $nextTransitionIndex = 0;

    protected array $grammarRulesList;

    public function setGrammarRulesList(array $grammarRulesList): void
    {
        $this->grammarRulesList = $grammarRulesList;
        foreach ($grammarRulesList as $grammarRule) {
            /** @var GrammarRule $grammarRule */
            $this->grammarRulesMap[$grammarRule->left][] = $grammarRule;
        }
    }

    /**
     * @return array|State[]
     */
    public function getStatesList(): array
    {
        return $this->statesList;
    }

    public function getTransitionsList(): array
    {
        return $this->transitionsList;
    }

    public function build(): void
    {
        $stateIndex = $this->nextStateIndex;
        $stateConditionIndex = $this->nextStateConditionIndex;
        $transitionIndex = $this->nextTransitionIndex;

        $initialStateCondition = $this->getOrCreateStateCondition(0, 0);
        $initialState = $this->getOrCreateState([$initialStateCondition->index => $initialStateCondition]);
        $statesList[] = $initialState;

        $alreadyProcessedStates = [];

        // do while there are new following states or transitions on the step
        while (
            $stateIndex !== $this->nextStateIndex
            || $stateConditionIndex !== $this->nextStateConditionIndex
            || $transitionIndex !== $this->nextTransitionIndex
        ) {
            $stateIndex = $this->nextStateIndex;
            $stateConditionIndex = $this->nextStateConditionIndex;
            $transitionIndex = $this->nextTransitionIndex;

            $followingStates = [];
            foreach ($statesList as $state) {
                // do not process same states twice
                if (!isset($alreadyProcessedStates[$state->index])) {
                    foreach ($this->getFollowingStates($state) as $followingState) {
                        $followingStates[$followingState->index] = $followingState;
                    }
                }
                $alreadyProcessedStates[$state->index] = $state->index;
            }
            $statesList = $followingStates;
        }

    }

    /**
     * All following states are grouped by consumed terminal or non-terminal.
     * That's why this finite-state machine is deterministic
     *
     * @param State $state
     * @return array
     */
    protected function getFollowingStates(State $state): array
    {
        $stateConditionsByMarked = [];
        foreach ($state->conditions as $stateCondition) {
            if ($stateCondition->marked()) {
                $stateConditionsByMarked[$stateCondition->marked()][$stateCondition->index] = $stateCondition;
            }
        }

        $followingStatesList = [];

        foreach ($stateConditionsByMarked as $symbol => $stateConditionsList) {
            $followingStateConditionsList = [];
            foreach ($stateConditionsList as $stateCondition) {
                /** @var StateCondition $stateCondition */
                $followingStateCondition = $this->getOrCreateStateCondition(
                    $stateCondition->rule->index,
                    ($stateCondition->markerPos + 1)
                );
                $followingStateConditionsList[$followingStateCondition->index] = $followingStateCondition;
            }

            $followingState = $this->getOrCreateState($followingStateConditionsList);
            $followingStatesList[$followingState->index] = $followingState;
            $this->createTransitionIfNotExists($state, $symbol, $followingState);
        }

        return $followingStatesList;
    }

    public function closeState(State $state): State
    {
        foreach ($state->conditions as $stateCondition) {
            if ($stateCondition->markedIsNonTerminal()) {
                foreach ($this->closeStateCondition($stateCondition) as $newStateCondition) {
                    $state->pushCondition($newStateCondition);
                }
            }
        }

        return $state;
    }

    /**
     * Getting all possible sub-conditions
     * If grammar
     * 1. E` := E
     * 2. E := E + T
     * 3. T := n
     * will return "2. E := E + T" and "3. T := n" on given "1. E `= E" incoming condition
     * @param StateCondition $stateCondition
     * @return array
     */
    protected function closeStateCondition(StateCondition $stateCondition): array
    {
        $newStatesList = [];

        if ($stateCondition->markedIsNonTerminal()) {
            foreach ($this->grammarRulesMap[$stateCondition->marked()] as $rule) {
                /** @var GrammarRule $rule */
                $newStateCondition = $this->getOrCreateStateCondition($rule->index, 0);
                $newStatesList[$newStateCondition->index] = $newStateCondition;
                if ($newStateCondition->markedIsNonTerminal() && $stateCondition->marked() !== $newStateCondition->marked()) {
                    foreach ($this->closeStateCondition($newStateCondition) as $newStateCondition) {
                        /** @var StateCondition $newStateCondition */
                        $newStatesList[$newStateCondition->index] = $newStateCondition;
                    }
                }
            }
        }

        return $newStatesList;
    }

    protected function createTransitionIfNotExists(State $state, string $symbol, State $newState): void
    {
        if (!isset($this->transitionsList[$state->index][$symbol])) {
            $this->transitionsList[$state->index][$symbol] = $newState;
            $this->nextTransitionIndex++;
        }
    }

    /**
     * @param array|StateCondition[] $stateConditionsList
     * @return State
     */
    protected function getOrCreateState(array $stateConditionsList): State
    {
        $newState = new State($stateConditionsList);
        $newState = $this->closeState($newState);

        $newConditionIndexes = array_keys($newState->conditions);
        foreach ($this->statesList as $existingState) {
            $existingConditionIndexes = array_keys($existingState->conditions);

            $commonConditions = array_intersect($newConditionIndexes, $existingConditionIndexes);
            if (count($commonConditions) === count($newState->conditions)) {
                return $existingState;
            }
        }

        $newState->index = $this->nextStateIndex;
        $this->nextStateIndex++;
        $this->statesList[] = $newState;
        return $newState;
    }

    protected function getOrCreateStateCondition(int $grammarRuleIndex, int $markerPos): StateCondition
    {
        if (isset($this->stateConditionsMap[$grammarRuleIndex][$markerPos])) {
            return $this->stateConditionsMap[$grammarRuleIndex][$markerPos];
        }

        $newStateCondition = new StateCondition(
            $this->grammarRulesList[$grammarRuleIndex],
            $markerPos,
            $this->nextStateConditionIndex
        );
        $this->nextStateConditionIndex++;
        $this->stateConditionsList[] = $newStateCondition;
        $this->stateConditionsMap[$grammarRuleIndex][$markerPos] = $newStateCondition;
        return $newStateCondition;
    }

}