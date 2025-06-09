<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Activitylog\Facades\Activity as LogActivity;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Only log if not in seeding context
        if (!app()->runningInConsole() || !app()->environment('local')) {
            return;
        }

        try {
            LogActivity::performedOn($user)
                ->withProperties([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ])
                ->log('user_registered');
        } catch (\Exception $e) {
            // Silently fail during seeding
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if status has changed
        if ($user->isDirty('status')) {
            $oldStatus = $user->getOriginal('status');

            try {
                LogActivity::performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'user_id' => $user->id,
                        'old_status' => $oldStatus,
                        'new_status' => $user->status,
                    ])
                    ->log('user_status_updated');
            } catch (\Exception $e) {
                // Silently fail if no authenticated user
            }
        }
    }
}
