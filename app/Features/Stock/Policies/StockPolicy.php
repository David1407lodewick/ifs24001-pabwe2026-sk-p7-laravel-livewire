<?php

namespace App\Features\Stock\Policies;

use App\Features\Stock\Models\Stock;
use App\Models\User;

class StockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Admin can view any user's stock (read-only); member can only view their own.
     */
    public function view(User $user, Stock $stock): bool
    {
        return $user->isAdmin() || $user->id === $stock->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Only the owner can update their stock (admin cannot edit).
     */
    public function update(User $user, Stock $stock): bool
    {
        return $user->id === $stock->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only the owner can delete their stock (admin cannot delete).
     */
    public function delete(User $user, Stock $stock): bool
    {
        return $user->id === $stock->user_id;
    }
}

