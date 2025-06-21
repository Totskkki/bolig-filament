<?php

namespace App\Models;

use App\Models\Users\Name;
use App\Models\Users\Address;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
  protected $table = 'users';


    protected $primaryKey = 'userid';
    // public $incrementing = true;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'contact_number',
        'name_id',
        'address_id',
    ];
   


    public function name()
    {
        return $this->belongsTo(Name::class, 'name_id', 'namesid');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'addressid');
    }
   public function user()
{
    return $this->belongsTo(Users::class, 'userid'); // ✅ CORRECT keys
}


}
