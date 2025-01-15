<?php declare(strict_types=1);

namespace App\Http\Requests\Incidents;

use Illuminate\Foundation\Http\FormRequest;

class FailedSubjectsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['string'],
            'sort' => ['in:meta.remote_id'],
            'direction' => ['required_with:sort', 'in:asc,desc'],
        ];
    }

    public function getSort(): ?string
    {
        return match ($this->sort) {
            'meta.remote_id' => 'meta->remote_id',
            default => null
        };
    }
}
