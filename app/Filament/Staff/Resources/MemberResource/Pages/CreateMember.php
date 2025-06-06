<?php

namespace App\Filament\Staff\Resources\MemberResource\Pages;

use App\Filament\Staff\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;
}
