#!/usr/bin/env php
<?php

declare(strict_types=1);

use CodingLiki\GrammarParser\GrammarRuleParser;

include_once __DIR__."/../vendor/CodingLiki/OoAutoloader/Autoloader.php";

if($argc < 2){
    echo "Недостаточно аргументов.
Укажите путь к файлу грамматик!
";
    exit(1);
}
$path = $argv[1];

if(!file_exists($path)){
    echo "Указанный файл ($path) не существует.
";
    exit(1);
}
$rules = GrammarRuleParser::parse(file_get_contents($path));

print_r($rules);
__HALT_COMPILER();