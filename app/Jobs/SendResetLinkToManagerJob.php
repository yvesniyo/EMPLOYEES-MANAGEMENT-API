<?php

namespace App\Jobs;

use App\Models\Employee;

class SendResetLinkToManagerJob extends Job
{


    public Employee $employee;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
