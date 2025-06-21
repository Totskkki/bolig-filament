<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;


class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return null;
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'user.name',
            'user.address',
        ]);
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $name = $this->record->name;
        $address = $this->record->address;

        return array_merge($data, [
            // User related
            'first_name' => $name?->first_name,
            'last_name' => $name?->last_name,
            'middle_name' => $name?->middle_name,
            'suffix' => $name?->suffix,
           // 'email' => $user?->email,
            'phone' => $this->record->phone,
           // 'username' => $user?->username,
            'image_photo' => $this->record->image_photo,
            'birthday' => $name?->birthday,
            'age' => $name?->age,
            'gender' => $name?->gender,




            // Address related
            'street' => $address?->street,
            'city' => $address?->city,
            'province' => $address?->province,
            'postal_code' => $address?->postal_code,
            'country' => $address?->country,

            'status' => $this->record->membership_status,
            'membership_date' => $this->record->membership_date,

        ]);
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState(); // Get the form data
        $name = $this->record->name;
        $address = $this->record->address;

        // Update name
        $name->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'],
            'suffix' => $data['suffix'],
            'birthday' => $data['birthday'],
            'age' => $data['age'],
            'gender' => $data['gender'],
        ]);

        // Update user

        // Update address
        $address->update([
            'street' => $data['street'],
            'city' => $data['city'],
            'province' => $data['province'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'],
        ]);

        // Update member data if needed
        $this->record->update([
            'phone' => $data['phone'],
            'image_photo' => $data['image_photo'],
            'membership_status' => $data['membership_status'],
            'membership_date' => $data['membership_date'],
        ]);
        Notification::make()
            ->title('Member updated successfully')
            ->success()
            ->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }
}
