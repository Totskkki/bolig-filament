<?php

namespace App\Rules;

use App\Models\Users\Name;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueFullName implements ValidationRule
{
    protected string $firstName;
    protected ?string $middleName;
    protected ?int $excludeNamesId;

    public function __construct(string $firstName, ?string $middleName = null, ?int $excludeNamesId = null)
    {
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->excludeNamesId = $excludeNamesId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Name::query()
            ->where('first_name', $this->firstName)
            ->where('middle_name', $this->middleName)
            ->where('last_name', $value);

        if ($this->excludeNamesId) {
            $query->where('namesid', '!=', $this->excludeNamesId);
        }

        if ($query->exists()) {
            $fail("A person with the same full name already exists.");
        }
    }
}
