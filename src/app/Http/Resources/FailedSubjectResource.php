<?php declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FailedSubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
