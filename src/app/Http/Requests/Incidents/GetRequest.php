<?php declare(strict_types=1);

namespace App\Http\Requests\Incidents;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
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
            'sort' => ['in:created_at,closed_at,severity'],
            'direction' => ['required_with:sort', 'in:asc,desc'],
        ];
    }
}
