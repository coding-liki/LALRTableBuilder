<?php
declare(strict_types=1);

namespace LALR1Automaton;

use CodingLiki\GrammarParser\GrammarRuleParser;
use LALR1Automaton\Table\CsvSaver;
use LALR1Automaton\Table\LALR1TableBuilder;
use PHPUnit\Framework\TestCase;

class LALR1TableSaverTest extends TestCase
{

    /**
     * @dataProvider saveProvider
     * @param string $rulesScript
     * @param string $resultString
     */
    public function testSave(string $rulesScript, string $resultString)
    {
        $testFileName = 'test.csv';
        $rules = GrammarRuleParser::parse($rulesScript);
        $automatonBuilder = new AutomatonBuilder($rules);
        $root  = $automatonBuilder->buildFromRules();
        $tableBuilder = new LALR1TableBuilder($root, $automatonBuilder->rules);

        $table = $tableBuilder->build();
        $saver = new CsvSaver($table);

        $saver->save($testFileName);

       self::assertStringEqualsFile($testFileName, $resultString);
    }

    public function saveProvider(): array
    {
        return [
            'void' => [
                '',
                '~
0
'
            ],
            '1 rule' => [
                'A: a;',
                "$,A,a,~\nERROR,s1,s2,0\nacc,ERROR,ERROR,1\nr1,ERROR,ERROR,2\n"
            ],
            '3 rules' => [
                'A: a B;
                B: b A;',
                '$,A,B,a,b,~
ERROR,s1,ERROR,s2,ERROR,0
acc,ERROR,ERROR,ERROR,ERROR,1
ERROR,ERROR,s3,ERROR,s4,2
r1,ERROR,ERROR,ERROR,ERROR,3
ERROR,s5,ERROR,s2,ERROR,4
r2,ERROR,ERROR,ERROR,ERROR,5
'
            ]
        ];
    }
}