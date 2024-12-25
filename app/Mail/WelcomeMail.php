<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Client;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;


    public $cliente;
    public $welcomePassword;

    public function __construct(Client $cliente, $welcomePassword)
    {
        $this->cliente = $cliente;
        $this->welcomePassword = $welcomePassword;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Bienvenido a SGT',
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
            view: 'emails.welcome-client',
            with: [
                'cliente' => $this->cliente,
                'welcomePassword' => $this->welcomePassword
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
