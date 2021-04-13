<?php
declare(strict_types=1);

namespace LALR1Automaton\Table;

use GrammarParser\Rule;
use GrammarParser\RulesHelper;
use LALR1Automaton\Automaton\State;

class LALR1TableBuilder
{

    /**
     * @var State[]
     */
    private array $allStates = [];

    /**
     * LR1TableBuilder constructor.
     * @param State $rootState
     * @param Rule[] $rules
     */
    public function __construct(private State $rootState, private array $rules)
    {
    }

    public function build(): array
    {
        $this->pickAllStates();

        $table = [];

        foreach ($this->allStates as $stateNumber => $state){
            $table[$stateNumber] = [];

            foreach ($state->children as $child){
                $childNumber = array_keys($this->allStates, $child)[0];
                $table[$stateNumber][$child->symbol] = sprintf("s%s", $childNumber);
            }

            foreach ($state->ruleSteps as $step){
                if(!$step->canAdvance(1)){
                    foreach ($step->getFirstSet() as $first){
                        $ruleNumber = array_keys($this->rules, $step->getRule())[0];
                        $result = sprintf("r%s", $ruleNumber);
                        if($step->getRule()->name === RulesHelper::ROOT_RULE_NAME){
                            $result = 'acc';
                        }
                        $table[$stateNumber][$first] = $result;
                    }
                }
            }
        }

        return $this->normalizeTable($table);
    }

    private function pickAllStates()
    {
        $this->allStates[] = $this->rootState;
        foreach ($this->rootState->children as $child) {
            if (!in_array($child, $this->allStates, true)) {
                $oldState = $this->rootState;
                $this->rootState = $child;
                $this->pickAllStates();
                $this->rootState = $oldState;
            }
        }
    }

    private function normalizeTable(array $table): array
    {
        $fullKeys = [];
        foreach ($table as $row){
            $rowKeys = array_keys($row);
            array_push($fullKeys, ...$rowKeys);
        }

        $fullKeys = array_values(array_unique($fullKeys));

        foreach ($table as $number => &$row){
            foreach ($fullKeys as $key){
                if(!isset($row[$key])){
                    $row[$key] = 'ERROR';
                }
            }
            $row['~'] = $number;
        }


        return $table;
    }
}