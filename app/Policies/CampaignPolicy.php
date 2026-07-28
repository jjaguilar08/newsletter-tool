<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->hasRole(UserRole::Staff)
            && in_array($campaign->status, [CampaignStatus::Draft, CampaignStatus::Scheduled], true);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function send(User $user, Campaign $campaign): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function schedule(User $user, Campaign $campaign): bool
    {
        return $user->hasRole(UserRole::Staff);
    }
}
