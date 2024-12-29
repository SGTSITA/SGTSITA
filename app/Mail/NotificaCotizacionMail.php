<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;

class NotificaCotizacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contenedor;
    public $cliente;

    public function __construct($contenedor, Client $cliente)
    {
        $this->contenedor = $contenedor;
        $this->cliente = $cliente;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Nuevo viaje solicitado: '.$this->cliente->nombre.' - '.$this->contenedor->num_contenedor,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.notifica-cotizacion-externo',
            with: [
                'contenedor' => $this->contenedor,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
