<?php

namespace App\Transpiler;

use Exception;
use JetBrains\PhpStorm\ArrayShape;

class QueryState
{

    public bool $valid = true;

    /** @var array|string[]|Exception[] */
    public array $errorsList = [];

    /** @var array|string[] */
    public array $suggestionsList = [];

    public function __construct(
        protected string $query,
        public int $caretPos = 0
    ) {
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    #[ArrayShape([
        'valid' => "bool",
        'query' => "string",
        'caretPos' => "int",
        'suggestionsList' => "string[]",
        'errorsList' => "string[]"
    ])]
    public function toArray(): array
    {
        $result = [
            'valid' => $this->valid,
            'query' => $this->query,
            'caretPos' => $this->caretPos,
            'suggestionsList' => $this->suggestionsList,
            'errorsList' => []
        ];

        foreach ($this->errorsList as $error) {
            if ($error instanceof \Throwable) {
                $result['errorsList'][] = $error->getMessage();
            } else {
                $result['errorsList'][] = $error;
            }
        }


        return $result;
    }
}