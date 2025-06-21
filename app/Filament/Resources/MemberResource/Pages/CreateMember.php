<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Member;
use App\Models\Users\Address;
use App\Models\Users\Name;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Member created successfully')
            ->success();
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->getModel()::create($data);
    }


    protected function getRedirectUrl(): string
    {
        // Redirect to index after creation
        return $this->getResource()::getUrl('index');
    }




    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $name = Name::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'age' => $data['age'] ?? null,
        ]);
        // 1. Create Address
        $address = Address::create([
            'street'      => $data['street'],
            'city'        => $data['city'],
            'province'    => $data['province'],
            'postal_code' => $data['postal_code'],
            'country'     => $data['country'],
        ]);


        // 3. Prepare data for members table
        return [
            'names_id'        => $name->id,
            'address_id'        => $address->id,
            'membership_date'   => $data['membership_date'],
            'membership_status' => $data['membership_status'],
            'phone' => $data['phone'],
            'image_photo' => $data['image_photo'],
        ];
    }
}
