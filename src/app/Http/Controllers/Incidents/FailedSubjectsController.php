<?php declare(strict_types=1);

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incidents\FailedSubjectsRequest;
use App\Http\Resources\FailedSubjectCollection;
use App\Incident;
use App\PolicyRunDetail;

class FailedSubjectsController extends Controller
{
    public function __invoke(FailedSubjectsRequest $request, Incident $incident)
    {
        $perPage = min(100, (int) request('per_page'));
        $query = PolicyRunDetail::query()
            ->wherePolicyRunId($incident->open_policy_run_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery->where('subject_id', 'like', "%{$request->search}%")
                        ->orWhereRaw('meta->"$.*" COLLATE utf8mb4_unicode_ci LIKE ?', ["%{$request->search}%"]);
                });
            })
            ->when($request->getSort(), fn ($query) => $query->orderBy($request->getSort(), request('direction', 'asc')));

        return new FailedSubjectCollection($query->paginate($perPage));
    }
}
