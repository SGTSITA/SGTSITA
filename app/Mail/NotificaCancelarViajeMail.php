<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Cotizaciones;


class NotificaCancelarViajeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cotizacion;
    public $numContenedor;

    public function __construct(Cotizaciones $cotiza, $contenedor)
    {
        $this->cotizacion = $cotiza;
        $this->numContenedor = $contenedor;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: "Viaje Cancelado - Contenedor: $this->numContenedor",
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
            view: 'emails.notifica-cancelar-viaje',
            with: [
                'cotizacion' => $this->cotizacion,
                'contenedor' => $this->numContenedor
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
