<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Cotizaciones;

class NotificaNuevoDocumentoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cotizacion;
    public $documento;

    public function __construct(Cotizaciones $cotiza, $docum)
    {
        $this->cotizacion = $cotiza;
        $this->documento = $docum;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
