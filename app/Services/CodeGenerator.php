<?php

namespace App\Services;

class CodeGenerator
{


    public static function EMPLOYEE(): string
    {
        $text = "123456789";
        $shuffled = str_shuffle($text);
        return "EMP" . substr($shuffled, 1, 4);
    }
}
