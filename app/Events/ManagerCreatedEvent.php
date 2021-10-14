<?php

namespace App\Events;

use App\Models\Employee;

class ManagerCreatedEvent extends Event
{

    public Employee $manager;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Employee $manager)
    {
        $this->manager = $manager;
    }
}
