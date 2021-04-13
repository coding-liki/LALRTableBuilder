<?php
declare(strict_types=1);
ini_set('phar.readonly', '0');

echo "Собираем phar\n";

$phar = new Phar(__DIR__.'/bin/LALRTableBuilder.phar');

$command = 'cp -r '.__DIR__.'/src '.__DIR__ . '/build/src';

exec($command);
$command = 'cp -r '.__DIR__.'/vendor '.__DIR__ . '/build/vendor';
exec($command);


//copy(__DIR__.'/src', __DIR__ . '/build/src');
//
//copy(__DIR__.'/vendor', __DIR__ . '/build/vendor');

if (!@mkdir($concurrentDirectory = __DIR__ . '/build') && !is_dir($concurrentDirectory)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
}



$phar->buildFromDirectory(__DIR__.'/build');

$phar->setStub(file_get_contents(__DIR__.'/index.php'));
