<?php

namespace App\ParserCompiler;

class GrammarRule
{

    public function __construct(
        public string $left,
        public array $right,
        public int $index
    ) {
    }

    public function hasItem(string $className): bool
    {
        return in_array($className, $this->right, true);
    }

    public function nextItemAfter(string $className): ?string
    {
        foreach ($this->right as $i => $item) {
            if ($item === $className && isset($this->right[$i + 1])) {
                return $this->right[$i + 1];
            }
        }

        return null;
    }

    public function first(): string
    {
        return reset($this->right);
    }

    public function last(): string
    {
        return end($this->right);
    }

    public function get(int $index): ?string
    {
        return $this->right[$index] ?? null;
    }

    public function length(): int
    {
        return count($this->right);
    }

}