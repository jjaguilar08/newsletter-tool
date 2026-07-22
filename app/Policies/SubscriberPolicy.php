<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;

class SubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function view(User $user, Subscriber $subscriber): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function update(User $user, Subscriber $subscriber): bool
    {
        return $user->hasRole(UserRole::Staff);
    }

    public function delete(User $user, Subscriber $subscriber): bool
    {
        return $user->hasRole(UserRole::Staff);
    }
}
