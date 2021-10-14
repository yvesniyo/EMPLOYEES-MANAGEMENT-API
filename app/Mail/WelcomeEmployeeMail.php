<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmployeeMail extends Mailable
{

    use Queueable, SerializesModels;


    public Employee  $employee;
    public $company_name;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
        $this->company_name = config("app.name");
    }


    public function build()
    {
        return $this->view("email.employees.welcome")
            ->with("employee", $this->employee)
            ->with("company_name", $this->company_name);
    }
}
