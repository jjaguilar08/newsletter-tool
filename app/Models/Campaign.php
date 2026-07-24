<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'subject',
    'content',
    'status',
    'scheduled_at',
    'sent_at',
    'created_by',
])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<CampaignSend, $this>
     */
    public function campaignSends(): HasMany
    {
        return $this->hasMany(CampaignSend::class);
    }

    // Atomic conditional update shared by the send-now endpoint and the
    // scheduled dispatcher: only a campaign currently in $from can be
    // claimed, so two concurrent claimants (a double-click, or the
    // dispatcher command overlapping itself) can never both succeed.
    public static function claimForSending(int $id, CampaignStatus $from): bool
    {
        return static::whereKey($id)
            ->where('status', $from)
            ->update(['status' => CampaignStatus::Sending]) > 0;
    }
}
