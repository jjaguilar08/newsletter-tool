<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriberStatus;
use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'email',
    'name',
    'status',
    'unsubscribe_token',
    'subscribed_at',
    'unsubscribed_at',
])]
class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriberStatus::class,
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<CampaignSend, $this>
     */
    public function campaignSends(): HasMany
    {
        return $this->hasMany(CampaignSend::class);
    }

    // Stored and looked up as plaintext, per CLAUDE.md: this is a low-value
    // secret (worst case an unauthorized unsubscribe, not a data breach),
    // and the plaintext must stay recoverable to embed in campaign emails.
    public static function generateUnsubscribeToken(): string
    {
        return Str::random(40);
    }
}
