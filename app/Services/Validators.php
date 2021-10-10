<?php

namespace App\Services;

class Validators
{
    public static function IsRwandaPhoneNumber(String $number): bool
    {

        $number = str_replace("+", "", $number);
        $number = str_replace(" ", "", $number);
        if (strlen($number) != 12) return false;
        if (!is_numeric($number)) return false;
        if (!str_starts_with($number, "250")) return false;

        return true;
    }
}
