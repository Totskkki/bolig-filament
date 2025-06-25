<?php

namespace App\Providers;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        FilamentIcon::register([
            'panels::sidebar.group.collapse-button' => view('icons.chevron-up'),
            'users-icon' => view('icons.users'),
            'dashboard-icon' => view('icons.dashboard'),
            //'settings-icon' => view('icons.settings'),
        ]);

        TextInput::configureUsing(fn(TextInput $textInput) => $textInput->inlineLabel());
        Radio::configureUsing(fn(Radio $radio) => $radio->inlineLabel());
        Action::configureUsing(fn(Action $action) => $action->iconButton());
    }
}
