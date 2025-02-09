<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenericNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $emailAccounts;
    public $emailSubject;
    public $emailMessage;

    public function __construct($emailAccounts,$emailSubject, $emailMessage)
    {
        $this->emailAccounts = $emailAccounts;
        $this->emailSubject = $emailSubject;
        $this->emailMessage = $emailMessage;
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
