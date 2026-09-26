<?php

namespace App\Observers;

use App\Models\Directory\UserNetworkIndex;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(User $user): void
    {
        UserNetworkIndex::where('user_id', $user->id)
            ->update([
                'unique_id' => $user->unique_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: null,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'whatsapp' => $user->whatsapp,
                'dob' => optional($user->client)->dob,
                'user_group_id' => optional($user->client)->user_group_id,
            ]);
    }

    public function deleted(User $user)
    {
        UserNetworkIndex::where('user_id', $user->id)->delete();
    }
}
