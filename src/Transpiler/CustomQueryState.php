<?php

namespace App\Transpiler;

use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class CustomQueryState
{

    protected bool $valid = true;

    /** @var array|string[]|Throwable[] */
    protected array $errorsList = [];

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

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errorsList;
    }

    public function pushError(string|Throwable $error): void
    {
        $this->valid = false;
        $this->errorsList[] = $error;
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
            if ($error instanceof Throwable) {
                $result['errorsList'][] = $error->getMessage();
            } else {
                $result['errorsList'][] = $error;
            }
        }

        return $result;
    }
}