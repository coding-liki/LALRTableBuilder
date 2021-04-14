<?php
declare(strict_types=1);

namespace LALR1Automaton;

use Codeception\Test\Unit;
use CodingLiki\GrammarParser\GrammarRuleParser;
use CodingLiki\GrammarParser\Rule\Rule;
use CodingLiki\GrammarParser\Rule\RulePart;
use CodingLiki\GrammarParser\RulesHelper;
use LALR1Automaton\Automaton\RuleStep;
use LALR1Automaton\Automaton\State;

class AutomatonBuilderTest extends Unit
{
    /**
     * @dataProvider buildProvider
     * @param string $rulesScript
     * @param State $rootState
     */
    public function testBuild(string $rulesScript, State $rootState): void
    {
        $builder = new AutomatonBuilder(GrammarRuleParser::parse($rulesScript));
        $state = $builder->buildFromRules();

        self::assertEquals($rootState, $state);
    }

    public function buildProvider(): array
    {
        return [
            'void' => [
                '',
                new State([], [], RulesHelper::ROOT_SYMBOL)
            ],
            'one rule' => [
                'A: a;',
                new State(
                    [
                    new RuleStep(new Rule('S\'', [new RulePart('A', '')], 'A'), 0, ['$']),
                    new RuleStep(new Rule('A', [new RulePart('a', '')], 'a'), 0, ['$'])
                ],
                    [
                        'A' => new State(
                            [
                                new RuleStep(new Rule('S\'', [new RulePart('A', '')], 'A'), 1, ['$']),
                            ],
                            [],
                            'A'
                        ),
                        'a' => new State(
                            [
                                new RuleStep(new Rule('A', [new RulePart('a', '')], 'a'), 1, ['$']),
                            ],
                            [],
                            'a'
                        )
                    ],
                    RulesHelper::ROOT_SYMBOL
                )
            ],
            '4 related rules' => [
                '
                S: E;
                E: A c | b l;
                A: a;
                ',
                new State(
                    [
                        new RuleStep(new Rule('S\'', [new RulePart('S', '')], 'S'), 0, ['$']),
                        new RuleStep(new Rule('S', [new RulePart('E', '')], 'E'), 0, ['$']),
                        new RuleStep(new Rule('E', [new RulePart('A', ''), new RulePart('c', '')], 'A c'), 0, ['$']),
                        new RuleStep(new Rule('A', [new RulePart('a', '')], 'a'), 0, ['c']),
                        new RuleStep(new Rule('E', [new RulePart('b', ''), new RulePart('l', '')], 'b l'), 0, ['$']),
                    ],
                    [
                        'S' => new State(
                            [
                                new RuleStep(new Rule('S\'', [new RulePart('S', '')], 'S'), 1, ['$']),
                            ],
                            [],
                            'S'
                        ),
                        'E' => new State(
                            [
                                new RuleStep(new Rule('S', [new RulePart('E', '')], 'E'), 1, ['$']),
                            ],
                            [],
                            'E'
                        ),
                        'A' => new State(
                            [
                                new RuleStep(new Rule('E', [new RulePart('A', ''), new RulePart('c', '')], 'A c'), 1, ['$']),
                            ],
                            [
                                'c' => new State(
                                    [
                                        new RuleStep(new Rule('E', [new RulePart('A', ''), new RulePart('c', '')], 'A c'), 2, ['$']),
                                    ],
                                    [],
                                    'c'
                                )
                            ],
                            'A'
                        ),
                        'b' => new State(
                            [
                                new RuleStep(new Rule('E', [new RulePart('b', ''), new RulePart('l', '')], 'b l'), 1, ['$']),
                            ],
                            [
                                'l' => new State(
                                    [
                                        new RuleStep(new Rule('E', [new RulePart('b', ''), new RulePart('l', '')], 'b l'), 2, ['$']),
                                    ],
                                    [],
                                    'l'
                                )
                            ],
                            'b'
                        ),
                        'a' => new State(
                            [
                                new RuleStep(new Rule('A', [new RulePart('a', '')], 'a'), 1, ['c']),
                            ],
                            [],
                            'a'
                        ),
                    ],
                    RulesHelper::ROOT_SYMBOL
                )
            ]
        ];
    }
}