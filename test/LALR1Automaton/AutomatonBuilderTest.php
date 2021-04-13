<?php
declare(strict_types=1);

namespace LALR1Automaton;

use GrammarParser\GrammarRuleParser;
use GrammarParser\Rule;
use GrammarParser\RulesHelper;
use LALR1Automaton\Automaton\RuleStep;
use LALR1Automaton\Automaton\State;
use PHPUnit\Framework\TestCase;

class AutomatonBuilderTest extends TestCase
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
                    new RuleStep(new Rule('S\'', ['A']), 0, ['$']),
                    new RuleStep(new Rule('A', ['a']), 0, ['$'])
                ],
                    [
                        'A' => new State(
                            [
                                new RuleStep(new Rule('S\'', ['A']), 1, ['$']),
                            ],
                            [],
                            'A'
                        ),
                        'a' => new State(
                            [
                                new RuleStep(new Rule('A', ['a']), 1, ['$']),
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
                        new RuleStep(new Rule('S\'', ['S']), 0, ['$']),
                        new RuleStep(new Rule('S', ['E']), 0, ['$']),
                        new RuleStep(new Rule('E', ['A', 'c']), 0, ['$']),
                        new RuleStep(new Rule('A', ['a']), 0, ['c']),
                        new RuleStep(new Rule('E', ['b', 'l']), 0, ['$']),
                    ],
                    [
                        'S' => new State(
                            [
                                new RuleStep(new Rule('S\'', ['S']), 1, ['$']),
                            ],
                            [],
                            'S'
                        ),
                        'E' => new State(
                            [
                                new RuleStep(new Rule('S', ['E']), 1, ['$']),
                            ],
                            [],
                            'E'
                        ),
                        'A' => new State(
                            [
                                new RuleStep(new Rule('E', ['A', 'c']), 1, ['$']),
                            ],
                            [
                                'c' => new State(
                                    [
                                        new RuleStep(new Rule('E', ['A', 'c']), 2, ['$']),
                                    ],
                                    [],
                                    'c'
                                )
                            ],
                            'A'
                        ),
                        'b' => new State(
                            [
                                new RuleStep(new Rule('E', ['b', 'l']), 1, ['$']),
                            ],
                            [
                                'l' => new State(
                                    [
                                        new RuleStep(new Rule('E', ['b', 'l']), 2, ['$']),
                                    ],
                                    [],
                                    'l'
                                )
                            ],
                            'b'
                        ),
                        'a' => new State(
                            [
                                new RuleStep(new Rule('A', ['a']), 1, ['c']),
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