<?php

namespace App\Transpiler;

class SuggestionManager
{

    public function addSuggestions(QueryState $queryState): void
    {
        if ($queryState->caretPos > 0) {
            $queryState->suggestionsList[] = 'test';
        }
    }

}