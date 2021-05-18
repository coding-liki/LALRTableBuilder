<?php
declare(strict_types=1);

namespace unit\LALR1Automaton;

use CodingLiki\GrammarParser\GrammarRuleParser;
use LALR1Automaton\AutomatonBuilder;
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

        $rules = $automatonBuilder->rules;

        $file = fopen($testFileName."_rules.csv", 'wb');
        fputcsv($file, ['number', 'rule']);

        foreach ($rules as $number => $rule){
            fputcsv($file, [
                 $number,
                 $rule
            ]);
        }

        fclose($file);
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
                "$,A,a,~\ne,s1,s2,0\nacc,e,e,1\nr1,e,e,2\n"
            ],
            '3 rules' => [
                '
                B: b A*;
                A: a B;
                ',
                '$,A,B,a,b,~
e,e,s1,e,s2,0
acc,e,e,e,e,1
r2,s3,e,s4,e,2
r1,s3,e,s4,e,3
e,e,s5,e,s2,4
e,e,e,r3,e,5
'
            ],
            'rules with *' => [
                'A: a B*;
                B: b (s* c)+;',
                '$,A,B,B_subrule_1,a,b,c,s,~
e,s1,e,e,s2,e,e,e,0
acc,e,e,e,e,e,e,e,1
r2,e,s3,e,e,s4,e,e,2
r1,e,s3,e,e,s4,e,e,3
e,e,e,s5,e,e,s8,s6,4
r3,e,e,s5,e,r3,s8,s6,5
e,e,e,e,e,e,s7,s6,6
r4,e,e,e,e,r4,r4,r4,7
r5,e,e,e,e,r5,r5,r5,8
'
            ]
        ];
    }
}