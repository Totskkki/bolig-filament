<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Closure;

class UniqueFullName implements ValidationRule
{
    protected string $firstName;
    protected ?string $middleName;
    protected ?int $namesid;

    public function __construct(string $firstName, ?string $middleName = null, ?int $namesid = null)
    {
        $this->firstName = strtolower($firstName); // Convert to lowercase
        $this->middleName = $middleName ? strtolower($middleName) : null;
        $this->namesid = $namesid;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lastName = strtolower($value); // Also lowercase the input last name

        $exists = DB::table('names')
            ->whereRaw('LOWER(first_name) = ?', [$this->firstName])
            ->whereRaw('LOWER(middle_name) = ?', [$this->middleName])
            ->whereRaw('LOWER(last_name) = ?', [$lastName])
            ->when($this->namesid, function ($query) {
                $query->where('namesid', '!=', $this->namesid);
            })
            ->exists();

        if ($exists) {
            $fail('A member with the same full name already exists (case-insensitive).');
        }
    }
}
