<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Envía una factura de venta o una cotización con su PDF adjunto.
 */
class DocumentoComercialMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $asunto,
        public string $mensaje,
        public string $empresaNombre,
        public string $pdfContenido,
        public string $pdfNombre
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asunto);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.documento-comercial');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContenido, $this->pdfNombre)->withMime('application/pdf'),
        ];
    }
}
