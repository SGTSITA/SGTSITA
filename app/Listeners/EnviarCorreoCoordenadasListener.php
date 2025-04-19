<?php

namespace App\Listeners;

use App\Events\EnvioCorreoCoordenadasEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail; 
use App\Mail\CorreoCoordenadasMail;

class EnviarCorreoCoordenadasListener
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
     * @param  \App\Events\EnvioCorreoCoordenadasEvent  $event
     * @return void
     */

    public function handle(EnvioCorreoCoordenadasEvent $event)
    {
        $datos = $event->sendMailCoordenadas;
        \Log::info('Listener ejecutado. Datos del correo: ', $datos);
        Mail::to($datos['correo'])->send(new CorreoCoordenadasMail($datos));
    }
}

