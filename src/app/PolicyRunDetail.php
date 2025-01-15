<?php declare(strict_types=1);

namespace App;

use App\Enums\SubjectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyRunDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'subject_type' => SubjectType::class,
        'meta' => 'json',
    ];
}
