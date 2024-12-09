<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ConfirmarDocumentosEvent;
use App\Http\Controllers\ExternosController;
class ConfirmarDocumentosListener
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
    public function handle(ConfirmarDocumentosEvent $event)
    {
        ExternosController::confirmarDocumentos($event->cotizacion);
    }
}
