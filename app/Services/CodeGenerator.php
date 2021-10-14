<?php

namespace App\Services;

use App\Models\Employee;
use \Faker;

class CodeGenerator
{


    public static function EMPLOYEE(): string
    {
        $text = "123456789";
        $code = "EMP" . substr(str_shuffle($text), 1, 4);

        while (Employee::code($code)
            ->exists()
        ) {
            $code = "EMP" . substr(str_shuffle($text), 1, 4);
        }

        return $code;
    }

    public static function PHONE_NUMBER(): string
    {
        /** @var Faker */
        $faker = Faker\Factory::create();
        $text = "123456789123456789";
        $isp = $faker->randomElement([2, 3, 8, 9]);
        return "+2507" . $isp . substr(str_shuffle($text), 1, 7);
    }


    public static function NATIONAL_ID()
    {

        $numbers = "0123456789012345678901234567890123456789";
        $id = ("1" . mt_rand(1970, 2000) . substr(
            str_shuffle($numbers),
            1,
            11
        ));

        while (Employee::whereNationalId($id)->exists()) {
            $id = ("1" . mt_rand(1970, 2000) . substr(
                str_shuffle($numbers),
                1,
                11
            ));
        }

        return $id;
    }
}
