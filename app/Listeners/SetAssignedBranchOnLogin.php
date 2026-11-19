<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class SetAssignedBranchOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        if ($user->id_sucursal !== null) {
            session(['id_sucursal' => $user->id_sucursal]);
        } else {
            session()->forget('id_sucursal');
        }
    }
}