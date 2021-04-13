<?php
declare(strict_types=1);

namespace LALR1Automaton;

use GrammarParser\GrammarRuleParser;
use LALR1Automaton\Table\LALR1TableBuilder;
use PHPUnit\Framework\TestCase;

class LR1TableBuilderTest extends TestCase
{

    public function testBuild()
    {
        $rules = GrammarRuleParser::parse(file_get_contents(__DIR__.'/../../grammar/calculator.grr'));
        $builder = new AutomatonBuilder($rules);

        $state = $builder->buildFromRules();


        $builder = new LALR1TableBuilder($state, $builder->rules);

        $table = $builder->build();


        self::assertCount(22, $table);
    }
}
