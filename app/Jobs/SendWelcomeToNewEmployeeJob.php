<?php

namespace App\Jobs;

use App\Mail\WelcomeEmployeeMail;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;

class SendWelcomeToNewEmployeeJob extends Job
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

        Mail::to($this->employee)
            ->send(new WelcomeEmployeeMail($this->employee));
    }
}
