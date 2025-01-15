<?php declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class DomainRule implements ValidationRule
{
    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i', $value)) {
            $fail('The :attribute must be a valid domain.');
        }
    }
}
