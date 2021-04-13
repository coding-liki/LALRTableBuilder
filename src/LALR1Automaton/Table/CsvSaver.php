<?php
declare(strict_types=1);

namespace LALR1Automaton\Table;

class CsvSaver
{

    /**
     * CsvSaver constructor.
     * @param array $table
     */
    public function __construct(private array $table)
    {
    }


    public function save(string $filePath): void
    {
        $file = fopen($filePath, 'wb');

        $this->saveTableToFile($file);
        fclose($file);
    }

    private function saveTableToFile(mixed $file): void
    {

        $keys = array_keys($this->table[0]);
        sort($keys);

        fputcsv($file, $keys);

        foreach ($this->table as $row){
            ksort($row);
            fputcsv($file, $row);
        }
    }
}