<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SampleNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'See what Beacon can do for your newsletters');
    }

    public function content(): Content
    {
        // Reuses the same view GET /api/campaigns/default-template serves -
        // its placeholder branding ("Brandname" etc.) represents the
        // customer's eventual newsletter, not Beacon, so it's left as-is.
        // The view's footer links are already inert ("#" hrefs, no real
        // token) - there's no Subscriber behind this send to generate a
        // real unsubscribe token for, so that's left untouched too.
        return new Content(view: 'emails.campaign-default-template');
    }
}
