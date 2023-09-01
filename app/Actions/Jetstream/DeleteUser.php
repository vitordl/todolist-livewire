<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        Log::info('[DELETE]'.now().' - O usuário '.auth()->user()->id. ' de e-mail '.auth()->user()->email.' deletou a conta');
        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
        // dd($user->id);

    }
}
