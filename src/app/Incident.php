<?php declare(strict_types=1);

namespace App;

use App\Enums\IncidentStatus;
use App\Enums\Severity;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Incident extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => IncidentStatus::class,
        'closed_at' => 'datetime',
        'severity' => Severity::class,
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function openPolicyRun(): BelongsTo
    {
        return $this->belongsTo(PolicyRun::class, 'open_policy_run_id');
    }

    public function closePolicyRun(): BelongsTo
    {
        return $this->belongsTo(PolicyRun::class, 'close_policy_run_id');
    }

    public function scopeOpenFor(Builder $query, Policy $policy)
    {
        return $query->whereStatus(IncidentStatus::OPEN)->wherePolicyId($policy->id);
    }

    public function close(PolicyRun $policyRun)
    {
        if ($this->status === IncidentStatus::OPEN) {
            $this->update([
                'status' => IncidentStatus::CLOSED,
                'close_policy_run_id' => $policyRun->id,
                'closed_at' => now(),
            ]);
        }
    }

    protected function formattedSeverity(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::of($this->severity->value)->lower()->replace('_', ' ')->title(),
        );
    }
}
