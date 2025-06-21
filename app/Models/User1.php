<?php

// namespace App\Models;

// // use Illuminate\Contracts\Auth\MustVerifyEmail;
// use App\Models\Member;
// use App\Models\Users\Name;
// use App\Models\Users\Address;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;


// class User extends Authenticatable
// {

//      protected $table = 'users'; // optional if Laravel already knows this

//     public function name()
//     {
//         return $this->belongsTo(Name::class, 'name_id', 'namesid');
//     }

//     public function address()
//     {
//         return $this->belongsTo(Address::class, 'address_id', 'addressid');
//     }
//     public function member()
//     {
//         return $this->hasOne(Member::class, 'user_id', 'userid');
//     }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // use HasFactory, Notifiable;

    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var list<string>
    //  */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    // /**
    //  * The attributes that should be hidden for serialization.
    //  *
    //  * @var list<string>
    //  */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    // /**
    //  * Get the attributes that should be cast.
    //  *
    //  * @return array<string, string>
    //  */
    // protected function casts(): array
    // {
    //     return [
    //         'email_verified_at' => 'datetime',
    //         'password' => 'hashed',
    //     ];
    // }
//}
