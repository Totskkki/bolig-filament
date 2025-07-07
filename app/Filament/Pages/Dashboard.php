<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use App\Models\Member;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    // protected static ?string $navigationIcon = 'heroicon-o-home'; // or 'home', 'peso', 'dashboard', etc.
    protected static ?string $navigationLabel = 'Dashboard';


    public static function getNavigationIcon(): string | Htmlable | null
    {
        return new HtmlString(view('components.icons.nav-dashboard')->render());
    }
}
