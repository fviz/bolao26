<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRegistrationInviteCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $expectedCode = (string) config('fortify.registration_invite_code');

        if ($expectedCode === '' || ! is_string($value) || ! hash_equals($expectedCode, $value)) {
            $fail('Código de convite inválido.');
        }
    }
}
