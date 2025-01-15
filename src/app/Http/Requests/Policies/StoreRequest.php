<?php declare(strict_types=1);

namespace App\Http\Requests\Policies;

use App\Enums\Product;
use App\Rules\DomainRule;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'tenant_domain' => ['required', new DomainRule()],
            'product' => ['required', Rule::enum(Product::class)],
        ];
    }
}
