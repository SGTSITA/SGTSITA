<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Cotizaciones;

class NotificaNuevoDocumentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cotizacion;
    public $documento;

    public function __construct(Cotizaciones $cotiza, $document)
    {
        $this->cotizacion = $cotiza;
        $this->documento = $document;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {   //cotizacion->DocCotizacion->num_contenedor

        return new Envelope(
            subject: 'Nuevo documento cargado: '. ucfirst(strtolower($this->cotizacion->cliente->nombre)).' - '.strtoupper($this->cotizacion->DocCotizacion->num_contenedor),
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
            view: 'emails.notifica-nuevo-documento',
            with: [
                'cotizacion' => $this->cotizacion,
                'documento' => $this->documento
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
