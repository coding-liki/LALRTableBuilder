<?php
declare(strict_types=1);

namespace LALR1Automaton;

use CodingLiki\GrammarParser\Calculators\FollowSetCalculator;
use CodingLiki\GrammarParser\Rule\Rule;
use CodingLiki\GrammarParser\Rule\RulePart;
use CodingLiki\GrammarParser\RulesHelper;
use LALR1Automaton\Automaton\RuleStep;
use LALR1Automaton\Automaton\State;

class AutomatonBuilder
{
    private FollowSetCalculator $followSetCalculator;

    private array $nextStepsToStep = [];

    /**
     * AutomatonBuilder constructor.
     * @param Rule[] $rules
     */
    public function __construct(public array $rules)
    {
        if (!empty($this->rules)) {
            array_unshift($this->rules, RulesHelper::buildRootRule($this->rules));
        }

        $this->followSetCalculator = new FollowSetCalculator($this->rules);
    }

    public function buildFromRules(): State
    {
        if (empty($this->rules)) {
            return new State([], [], RulesHelper::ROOT_SYMBOL);
        }

        $rootState =  $this->buildState([new RuleStep($this->rules[0], 0, ['$'])], RulesHelper::ROOT_SYMBOL);
        return $rootState;
    }

    /**
     * @param RuleStep[] $initSteps
     * @param string $symbol
     * @return State
     */
    private function buildState(array $initSteps, string $symbol): State
    {
        $key = md5(serialize([$initSteps, $symbol]));
        if (isset($this->nextStepsToStep[$key])) {
            return $this->nextStepsToStep[$key];
        }


        $steps = $this->calculateRuleSteps($initSteps, $symbol);

        $state = new State($steps, [], $symbol);

        $this->nextStepsToStep[$key] = $state;
        $sortedSteps = $this->sortStepsByNextPart($steps);

        foreach ($sortedSteps as $nextPart => $nextPartSteps) {

            $nextSteps = [];
            foreach ($nextPartSteps as $step) {
                $rule = $step->getRule();
                $nextStep = (clone $step);
                if ($rule->getParts()[$step->getPosition()]->getType() !== RulePart::TYPE_MUST_BE_ONCE_OR_MORE) {
                    $nextStep->advance(1);
                }
                $nextSteps[] = $nextStep;
            }
            //$firstRule = $nextSteps[0]->getRule();
            //$firstRuleNextPart = $firstRule->getParts()[$nextSteps[0]->getPosition() - 1];

            //$reduceMany = $firstRuleNextPart->getType() === RulePart::TYPE_MUST_BE_ONCE_OR_MORE && $nextSteps[0]->getPosition() > 0;
            if ($nextPart === $symbol) {
                $nextState = $state;
            } else {
                $nextState = $this->buildState($nextSteps, $nextPart);
            }

            $state->addChild($nextPart, $nextState);
        }

        return $state;
    }

    /**
     * @param RuleStep[] $initSteps
     * @return array
     */
    private function calculateRuleSteps(array $initSteps, string $symbol): array
    {
        $allSteps = $initSteps;

        foreach ($initSteps as $initStep) {
            $closureSteps = $this->closureStep($initStep, $symbol);
            array_push($allSteps, ...$closureSteps);
        }


        return $this->normalizeSteps($allSteps);
    }

    private function closureStep(RuleStep $initStep, string $symbol): array
    {
        $closureSteps = [];

        if ($initStep->canAdvance(1)) {
            $nextPart = $initStep->getRule()->getParts()[$initStep->getPosition()];
            $closureRules = RulesHelper::getRulesByName($nextPart->getData(), $this->rules);
            $closureFirstSet = $this->calculateClosureFirstSet($initStep);

            if (!empty($closureRules)) {
                foreach ($closureRules as $closureRule) {
                    $newStep = new RuleStep($closureRule, 0, $closureFirstSet);
                    $advancedClosureSet = $this->closureStep($newStep, $symbol);
                    array_push($closureSteps, $newStep, ...$advancedClosureSet);
                }
            }

            if ($nextPart->getType() === RulePart::TYPE_MUST_BE_ONCE_OR_MORE && $nextPart->getData() === $symbol) {
                $newStep = (clone $initStep)->advance(1);
                $advancedClosureSet = $this->closureStep($newStep, $symbol);
                array_push($closureSteps, $newStep, ...$advancedClosureSet);
            }
        }

        return $closureSteps;
    }


    private function calculateClosureFirstSet(RuleStep $initStep): array
    {
        $closureFirstSet = $initStep->getFirstSet();

        if ($initStep->canAdvance(1)) {
            $closureFirstSet = $this->followSetCalculator->calculate($initStep->getRule()->getParts()[$initStep->getPosition()]->getData());
        }

        return $closureFirstSet;
    }

    /**
     * @param RuleStep[] $steps
     * @return array<RuleStep[]>
     */
    private function sortStepsByNextPart(array $steps): array
    {
        $sortedSteps = [];
        foreach ($steps as $step) {
            if ($step->canAdvance(1)) {
                $nextPart = $step->getRule()->getParts()[$step->getPosition()];
                $sortedSteps[$nextPart->getData()][] = $step;
            }
        }

        return $sortedSteps;
    }

    /**
     * @param RuleStep[] $closureSteps
     * @return array
     */
    private function normalizeSteps(array $closureSteps): array
    {
        $closureSteps = array_values(array_unique($closureSteps, SORT_REGULAR));

        $stepsMass = count($closureSteps);
        $normalizedSteps = [];
        $notToAddIndexes = [];
        for ($i = 0; $i < $stepsMass; $i++) {
            if (in_array($i, $notToAddIndexes, true)) {
                continue;
            }
            $currentStep = $closureSteps[$i];
            for ($j = $i; $j < $stepsMass; $j++) {
                $checkStep = $closureSteps[$j];
                if ($i !== $j && $currentStep->getRule() === $checkStep->getRule() && $currentStep->getPosition() === $checkStep->getPosition()) {
                    $notToAddIndexes[] = $j;
                    foreach ($checkStep->getFirstSet() as $first) {
                        $currentStep->addFirstToSet($first);
                    }
                }
            }
            $normalizedSteps[] = $currentStep;
        }


        return empty($normalizedSteps) ? $closureSteps : $normalizedSteps;
    }
}