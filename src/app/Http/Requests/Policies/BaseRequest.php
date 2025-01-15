<?php declare(strict_types=1);

namespace App\Http\Requests\Policies;

use App\Enums\PolicyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer'],
            'policies' => ['required', 'array'],
            'policies.*' => ['distinct', Rule::enum(PolicyType::class)],
        ];
    }
}
