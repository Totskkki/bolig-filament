<?php

namespace App\Models;


use App\Models\Users\Name;
use App\Models\Users\Address;
use App\Models\Member;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class User extends Authenticatable implements FilamentUser, HasName
{
    protected $table = 'users';
     use Notifiable;


    protected $primaryKey = 'userid';
    // public $incrementing = true;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'contact_number',
        'photo',
        'name_id',
        'address_id',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }



    public function name()
    {
        return $this->belongsTo(Name::class, 'name_id', 'namesid');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'addressid');
    }
    public function member()
    {
        return $this->hasOne(Member::class, 'user_id', 'userid');
    }


    //  This method determines if a user can log in to Filament
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'staff' => $this->role === 'staff',
            'admin' => $this->role === 'admin',
            default => false,
        };
    }

    //  Display username in Filament UI
    public function getFilamentName(): string
    {
        return $this->email ?? $this->username ?? 'User';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return null; // or return an actual avatar URL if you have one
    }




    // Allow only admin and staff to login to Filament
    public function canAccessFilament(): bool
    {
        return in_array($this->role, ['admin', 'staff']);
    }
    // public function username()
    // {
    //     return 'username';
    // }
    public static function logAudit(string $action, string $description): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
