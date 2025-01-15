<?php declare(strict_types=1);

namespace App;

use App\Enums\IncidentStatus;
use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Enums\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Policy extends Model
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    protected $casts = [
        'product' => Product::class,
        'type' => PolicyType::class,
        'status' => PolicyStatus::class,
    ];

    public function scopeEnabledFor(Builder $query, PolicyType $type)
    {
        return $query->whereStatus(PolicyStatus::ACTIVE)->whereType($type->value);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function policyRuns(): HasMany
    {
        return $this->hasMany(PolicyRun::class);
    }

    protected function openIncident(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->incidents()->whereStatus(IncidentStatus::OPEN)->first(),
        );
    }

    protected function formattedType(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::of($this->type->value)->lower()->replace('_', ' ')->title(),
        );
    }
}
