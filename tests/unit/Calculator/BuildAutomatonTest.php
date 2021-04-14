<?php
declare(strict_types=1);

namespace unit\Calculator;

use Codeception\Test\Unit;
use CodingLiki\GrammarParser\GrammarRuleParser;
use LALR1Automaton\Automaton\State;
use LALR1Automaton\AutomatonBuilder;

class BuildAutomatonTest extends Unit
{
    public function testBuild()
    {
        $builder = new AutomatonBuilder(GrammarRuleParser::parse(file_get_contents(__DIR__.'/../../../grammar/calculator.grr')));

        $root = $builder->buildFromRules();

        self::assertInstanceOf(State::class, $root);
    }

}