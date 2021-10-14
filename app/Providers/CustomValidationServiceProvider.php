<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend("national_id", function ($attribute, $value, $parameters, $validator) {
            if (strlen($value) != 16) {
                return false;
            } else if (!is_numeric($value)) {
                return false;
            }
            $year = (int) substr($value, 1, 4);
            if ((Carbon::now()->year - $year) >= 18) {
                return true;
            }
            return false;
        });
        Validator::replacer("national_id", function ($message, $rule, $parameters) {
            return str_replace($message, "Invalid National ID", $message);
        });

        Validator::extend("phone", function ($attribute, $value, $parameters, $validator) {

            $value = trim($value);
            if (strlen($value) == 13) {
                $value = substr($value, 1, 13);
            }

            if (strlen($value) != 12) {
                return false;
            } else if (!is_numeric($value)) {
                return false;
            }

            if (
                Str::startsWith($value, "25078") ||
                Str::startsWith($value, "25079") ||
                Str::startsWith($value, "25072") ||
                Str::startsWith($value, "25073")
            ) {

                return true;
            }
            return false;
        });

        Validator::replacer("phone", function ($message, $rule, $parameters) {
            return str_replace($message, "Invalid Rwanda Phone number", $message);
        });
    }
}
