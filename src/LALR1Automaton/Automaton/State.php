<?php
declare(strict_types=1);

namespace LALR1Automaton\Automaton;

use JsonSerializable;

class State implements JsonSerializable
{
    /**
     * State constructor.
     * @param RuleStep[] $ruleSteps
     * @param State[] $children
     * @param string $symbol
     */
    public function __construct(public array $ruleSteps, public array $children, public string $symbol)
    {
    }

    public function addRuleStep(RuleStep $ruleStep)
    {
        $this->ruleSteps[] = $ruleStep;
    }

    public function addChild(string $name, State $state)
    {
        $this->children[$name] = $state;
    }

    public function jsonSerialize()
    {
        $checkedChildren = [];
        $me = $this;
        $myNextPart = $this->ruleSteps[0]->getRule()->parts[$this->ruleSteps[0]->getPosition()];
        foreach ($this->children as $nextPart => $child){
            if(!isset($checkedChildren[$nextPart])){
                $checkedChildren[$nextPart] = [];
            }

            $checkedChild = $child;
            if($myNextPart === $nextPart){
                $checkedChild = 'self';
            }

            $checkedChildren[$nextPart][] =  $checkedChild;
        }

        return [
            'steps' => $this->ruleSteps,
            'children' =>  $checkedChildren
        ];
    }
}