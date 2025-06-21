<?php

namespace App\Filament\Staff\Resources\UsersResource\Pages;

use App\Filament\Staff\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;
}
