<?php

namespace App\Listeners;

use App\Events\ManagerCreatedEvent;
use App\Mail\WelcomeEmployeeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeToMangerListener implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ManagerCreatedEvent  $event
     * @return void
     */
    public function handle(ManagerCreatedEvent $event)
    {
        Mail::to($event->manager)
            ->send(new WelcomeEmployeeMail($event->manager));
    }
}
