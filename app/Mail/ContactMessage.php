<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessage extends Mailable
{
    /** @param array{nome:string,azienda:?string,email:string,telefono:?string,messaggio:string} $data */
    public function __construct(public array $data, public string $lang) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [new Address(config('site.company.email'), config('site.company.name'))],
            replyTo: [new Address($this->data['email'], $this->data['nome'])],
            subject: 'Richiesta dal sito - '.$this->data['nome'],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
