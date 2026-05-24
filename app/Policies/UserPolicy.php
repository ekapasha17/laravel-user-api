<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $authUser, User $targetUser): bool
    {
        return match ($authUser->role) {
            'administrator' => true,
            'manager' => $targetUser->role === 'user',
            'user' => $authUser->id === $targetUser->id,
            default => false,
        };
    }
}
