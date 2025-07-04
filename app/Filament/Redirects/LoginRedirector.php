<?php

namespace App\Filament\Redirects;

use Filament\Http\Redirects\LoginRedirector as BaseLoginRedirector;
use Illuminate\Http\RedirectResponse;

class LoginRedirector extends BaseLoginRedirector
{
    public function redirect(): RedirectResponse
    {
        $user = auth()->user();

        if ($user?->role === 'admin') {
            return redirect()->intended(route('filament.admin.pages.dashboard'));
        }

        if ($user?->role === 'staff') {
            return redirect()->intended(route('filament.staff.pages.dashboard')); // Only if you have a staff panel
        }

        return parent::redirect();
    }
}
