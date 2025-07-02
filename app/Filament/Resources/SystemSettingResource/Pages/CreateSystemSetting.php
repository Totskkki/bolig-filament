<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSystemSetting extends CreateRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('System Settings created successfully')
            ->success();
    }
}
