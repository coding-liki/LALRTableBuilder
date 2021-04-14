<?php
declare(strict_types=1);

namespace unit\LALR1Automaton;

use Codeception\Test\Unit;
use CodingLiki\GrammarParser\GrammarRuleParser;
use LALR1Automaton\AutomatonBuilder;
use LALR1Automaton\Table\LALR1TableBuilder;

class LR1TableBuilderTest extends Unit
{

    public function testBuild(): void
    {
        $rules = GrammarRuleParser::parse(file_get_contents(__DIR__.'/../../../grammar/calculator.grr'));
        $builder = new AutomatonBuilder($rules);

        $state = $builder->buildFromRules();


        $builder = new LALR1TableBuilder($state, $builder->rules);

        $table = $builder->build();


        self::assertCount(22, $table);
    }
}
