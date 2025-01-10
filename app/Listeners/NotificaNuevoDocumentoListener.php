<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\NotificaNuevoDocumentoEvent;
use Auth;
use Mail;

class NotificaNuevoDocumentoListener
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
     * @param  object  $event
     * @return void
     */
    public function handle(NotificaNuevoDocumentoEvent $event)
    {
        $emailList = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
        Mail::to($emailList)->send(new \App\Mail\NotificaNuevoDocumentoMail($event->cotizacion,$event->documento));
    }
}
