<?php

namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        // Make sure $user exists before accessing properties
        if ($user?->role === 'admin') {
            return redirect()->to(Dashboard::getUrl(panel: 'admin'));
        }

        if ($user?->role === 'staff') {
            return redirect()->to(Dashboard::getUrl(panel: 'staff')); // If you have a staff panel
        }

        // fallback
        return parent::toResponse($request);
    }
}
