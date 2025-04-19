<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\ConfirmarDocumentosEvent::class =>[
            \App\Listeners\ConfirmarDocumentosListener::class,
        ],
        \App\Events\NotificaNuevoDocumentoEvent::class =>[
            \App\Listeners\NotificaNuevoDocumentoListener::class,
        ],
        \App\Events\GenericNotificationEvent::class =>[
            \App\Listeners\GenericNotificationListener::class,
        ],
        \App\Events\EnvioCorreoCoordenadasEvent::class =>[
            \App\Listeners\EnviarCorreoCoordenadasListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
