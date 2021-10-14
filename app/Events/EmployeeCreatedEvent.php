<?php

namespace App\Events;

use App\Models\Employee;

class EmployeeCreatedEvent extends Event
{
    public Employee $employee;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }
}
