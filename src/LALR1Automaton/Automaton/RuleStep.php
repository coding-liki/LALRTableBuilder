<?php
declare(strict_types=1);

namespace LALR1Automaton\Automaton;

use CodingLiki\GrammarParser\Rule\Rule;

class RuleStep
{
    public function __construct(private Rule $rule, private int $position, private array $firstSet)
    {
    }

    public function addFirstToSet(string $first): self
    {
        if(!in_array($first, $this->firstSet, true)) {
            $this->firstSet[] = $first;
        }

        return $this;
    }

    public function advance(int $steps): self
    {
        $this->position++;
        return $this;
    }

    public function canAdvance(int $steps): bool
    {
        return $this->position + $steps <= count($this->rule->getParts());
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return array
     */
    public function getFirstSet(): array
    {
        return $this->firstSet;
    }

    public function __toString(): string
    {
        return implode('@', [implode('%', $this->firstSet), $this->position, $this->rule]);
    }
}