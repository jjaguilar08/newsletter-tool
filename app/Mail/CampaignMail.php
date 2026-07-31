<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Campaign;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public Subscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function content(): Content
    {
        $unsubscribeUrl = route('unsubscribe', ['token' => $this->subscriber->unsubscribe_token]);

        // A saved GrapesJS design takes priority over the plain-text content
        // template - body_html is only ever set once the design editor has
        // been used, so its presence is the signal, not campaign.content
        // (which stays populated either way; that column is left untouched
        // by design, see the campaigns migration).
        if ($this->campaign->body_html !== null) {
            return new Content(
                view: 'emails.campaign-html',
                with: [
                    'bodyHtml' => $this->campaign->body_html,
                    'unsubscribeUrl' => $unsubscribeUrl,
                ],
            );
        }

        return new Content(
            view: 'emails.campaign',
            with: [
                'content' => $this->campaign->content,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
