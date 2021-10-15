<?php

use Carbon\Carbon;
use Illuminate\Http\Request;

function isOver18(string $date_of_birth)
{

    $dob = Carbon::parse($date_of_birth);
    $years = Carbon::now()->year - $dob->year;
    return ($years > 18);
}


if (
    !function_exists('config_path')
) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}



function log_activity($userModel, string $log, $contentModel = null)
{

    $activity =  activity()
        ->causedBy($userModel);


    if ($contentModel) {
        $activity->performedOn($contentModel);
    }

    return $activity->log($log);
}


/**
 * Employess request filter.
 * This will return columns in 0 index and where in 1 index.
 * @param Request $request
 * @return array
 */

function employeesExportReqFilter(Request $request)
{
    $filters = $request->only([
        "name", "code", "id", "position", "national_id",
        "status", "email", "phone",
        "dob", "created_at"
    ]);

    $where = [];

    foreach ($filters as $key => $value) {

        $key = trim($key);
        $value = trim($value);

        if (
            empty($value) ||
            empty($key)
        ) {
            continue;
        }

        if ($key == "dob") {
            array_push($where, [$key, "=",  Carbon::parse($value)->format("Y-m-d")]);
            continue;
        }

        if (is_numeric($value)) {
            array_push($where, [$key, "=", (int) $value]);
            continue;
        }

        array_push($where, [$key, "like", $value . "%"]);
    }

    $columns = ["*"];
    if ($request->columns) {
        $columns = explode(",", $request->columns);
        $columns = array_map(fn ($d) => trim($d), $columns);
    }

    $columns = array_filter($columns, fn ($v, $k) => !empty($v), ARRAY_FILTER_USE_BOTH);

    return [$columns, $where];
}
