<?php
declare(strict_types=1);

namespace LALR1Automaton;

use GrammarParser\FirstSetCalculator;
use GrammarParser\FollowSetCalculator;
use GrammarParser\Rule;
use GrammarParser\RulesHelper;
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
        if(!empty($this->rules)) {
            array_unshift($this->rules, RulesHelper::buildRootRule($this->rules));
        }

        $this->followSetCalculator = new FollowSetCalculator($this->rules);
    }

    public function buildFromRules(): State
    {
        if(empty($this->rules)){
            return new State([],[], RulesHelper::ROOT_SYMBOL);
        }

        return $this->buildState([new RuleStep($this->rules[0], 0, ['$'])], RulesHelper::ROOT_SYMBOL);
    }

    /**
     * @param RuleStep[] $ruleSteps
     * @param string $symbol
     * @return State
     */
    private function buildState(array $ruleSteps, string $symbol): State
    {
        $key = md5(serialize($ruleSteps));
        if(isset($this->nextStepsToStep[$key])){
            return $this->nextStepsToStep[$key];
        }

        $steps = $this->calculateRuleSteps($ruleSteps);

        $state = new State($steps, [], $symbol);

        $this->nextStepsToStep[$key] = $state;
        $sortedSteps = $this->sortStepsByNextPart($steps);

        foreach ($sortedSteps as $nextPart => $nextPartSteps){

            $nextSteps = [];
            foreach ($nextPartSteps as $step){
                $nextSteps[] = (clone $step)->advance(1);
            }

            if($nextPart === $symbol){
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
     * @param string $symbol
     * @return array
     */
    private function calculateRuleSteps(array $initSteps): array
    {
        $allSteps = $initSteps;

        foreach ($initSteps as $initStep){
            $closureSteps = $this->closureStep($initStep);
            array_push($allSteps, ...$closureSteps);
        }


        return $this->normalizeSteps($allSteps);
    }

    private function closureStep(RuleStep $initStep): array
    {
        $closureSteps = [];

        if($initStep->canAdvance(1)){
            $nextPart = $initStep->getRule()->parts[$initStep->getPosition()];
            $closureRules = RulesHelper::getRulesByName($nextPart, $this->rules);
            $closureFirstSet = $this->calculateClosureFirstSet($initStep);

            if(!empty($closureRules)){
                foreach ($closureRules as $closureRule){
                    $newStep = new RuleStep($closureRule, 0, $closureFirstSet);
                    $advancedClosureSet = $this->closureStep($newStep);
                    array_push($closureSteps, $newStep, ...$advancedClosureSet);
                }
            }
        }

        return $closureSteps;
    }


    private function calculateClosureFirstSet(RuleStep $initStep): array
    {
        $closureFirstSet = $initStep->getFirstSet();
        if ($initStep->canAdvance(2)) {
            $closureFirstSet = $this->followSetCalculator->calculate($initStep->getRule()->parts[$initStep->getPosition()]);
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
        foreach ($steps as $step){
            if($step->canAdvance(1)) {
                $nextPart = $step->getRule()->parts[$step->getPosition()];
                $sortedSteps[$nextPart][] = $step;
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
        for($i = 0; $i < $stepsMass; $i++){
            if(in_array($i, $notToAddIndexes, true)){
                continue;
            }
            $currentStep = $closureSteps[$i];
            for($j = $i; $j < $stepsMass; $j++){
                $checkStep = $closureSteps[$j];
                if( $i !== $j && $currentStep->getRule() === $checkStep->getRule() && $currentStep->getPosition() === $checkStep->getPosition()){
                    $notToAddIndexes[] = $j;
                    foreach ($checkStep->getFirstSet() as $first){
                        $currentStep->addFirstToSet($first);
                    }
                }
            }
            $normalizedSteps[] = $currentStep;
        }


        return empty($normalizedSteps) ? $closureSteps : $normalizedSteps;
    }
}