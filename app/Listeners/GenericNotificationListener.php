<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\GenericNotificationEvent;
use Mail;

class GenericNotificationListener
{
   
    

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(GenericNotificationEvent $event)
    {
        Mail::to($event->emailAccounts)
        ->send(new \App\Mail\GenericNotificationMail($event->emailSubject,$event->emailMessage));
    }
}
