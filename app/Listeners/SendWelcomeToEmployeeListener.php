<?php

namespace App\Listeners;

use App\Events\EmployeeCreatedEvent;
use App\Mail\WelcomeEmployeeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeToEmployeeListener implements ShouldQueue
{

    use InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }


    /**
     * Handle the event.
     *
     * @param  \App\Events\EmployeeCreatedEvent  $event
     * @return void
     */
    public function handle(EmployeeCreatedEvent $event)
    {
        Mail::to($event->employee)
            ->send(new WelcomeEmployeeMail($event->employee));
    }
}
