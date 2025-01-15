<?php declare(strict_types=1);

namespace App;

use App\Enums\PolicyRunStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyRun extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $cast = [
        'completed_at' => 'datetime',
        'meta' => 'json',
        'status' => PolicyRunStatus::class,
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PolicyRunDetail::class);
    }

    public function complete(PolicyRunStatus $policyRunStatus = PolicyRunStatus::COMPLETED): bool
    {
        return $this->update(['status' => $policyRunStatus, 'completed_at' => now()]);
    }
}
