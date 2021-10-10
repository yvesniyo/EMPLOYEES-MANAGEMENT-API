<?php

use Carbon\Carbon;


function isOver18(string $date_of_birth)
{
    $dob = Carbon::parse($date_of_birth);
    $years = Carbon::now()->year - $dob->year;
    return ($years < 18);
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
