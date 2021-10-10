<?php

namespace App\Exceptions;

use Exception;


class CustomExcelImportException extends Exception
{
    public $failures = array();
    public $successes = array();

    public function __construct(array $failures, array $successes)
    {
        $this->failures = collect($failures)->flatten(1)->toArray();
        $this->successes = $successes;
    }

    public function report()
    {
    }

    public function successes()
    {
        return $this->successes;
    }

    public function failures()
    {
        $rows = $this->failures;
        $failures = array();
        foreach ($rows  as $row) {
            foreach ($row as $column) {
                $failures[] = $column;
            }
        }
        return $failures;
    }
}
