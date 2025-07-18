<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\NotificaNuevoDocumentoEvent;
use App\Models\Correo;
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
        $emailList1 = Correo::where('nuevo_documento',1)->where('referencia',Auth::User()->id_cliente)
                              ->get()
                              ->pluck('correo')
                              ->toArray();
       
        $emailList = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
        foreach($emailList1 as $m){
            array_push($emailList,$m);
        }

        Mail::to($emailList1)->send(new \App\Mail\NotificaNuevoDocumentoMail($event->cotizacion,$event->documento));
    }
}
