#!/usr/bin/env php
<?php

declare(strict_types=1);

use CodingLiki\GrammarParser\GrammarRuleParser;
use LALR1Automaton\AutomatonBuilder;
use LALR1Automaton\Table\LALR1TableBuilder;

include_once __DIR__ . "/../vendor/CodingLiki/OoAutoloader/Autoloader.php";

if ($argc < 2) {
    echo "Недостаточно аргументов.
Укажите путь к файлу грамматик!
";
    exit(1);
}
$path = $argv[1];

if (!file_exists($path)) {
    echo "Указанный файл ($path) не существует.
";
    exit(1);
}
$rules = GrammarRuleParser::parse(file_get_contents($path));
$builder = new AutomatonBuilder($rules);
$state = $builder->buildFromRules();

$tableBuilder = new LALR1TableBuilder($state, $builder->rules);

$table = $tableBuilder->build();

$saver = new \LALR1Automaton\Table\CsvSaver($table);

$saver->save(dirname($path) . "/" . basename($path) . ".lrt");

__HALT_COMPILER();