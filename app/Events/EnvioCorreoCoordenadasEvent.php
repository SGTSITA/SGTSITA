<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


    class EnvioCorreoCoordenadasEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sendMailCoordenadas;

    public function __construct(array $_sendMailCoordenadas)
    {
        $this->sendMailCoordenadas = $_sendMailCoordenadas;
    }
}

