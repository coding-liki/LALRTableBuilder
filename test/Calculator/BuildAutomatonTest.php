<?php
declare(strict_types=1);

namespace Calculator;

use GrammarParser\GrammarRuleParser;
use LALR1Automaton\Automaton\State;
use LALR1Automaton\AutomatonBuilder;
use PHPUnit\Framework\TestCase;

class BuildAutomatonTest extends TestCase
{
    public function testBuild()
    {
        $builder = new AutomatonBuilder(GrammarRuleParser::parse(file_get_contents(__DIR__.'/../../grammar/calculator.grr')));

        $root = $builder->buildFromRules();

        self::assertInstanceOf(State::class, $root);
    }

}