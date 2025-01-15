<?php declare(strict_types=1);

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incidents\GetRequest;
use App\Http\Resources\IncidentResource;
use App\Incident;

class GetController extends Controller
{
    public function __invoke(GetRequest $request)
    {
        $perPage = min(100, (int) $request->get('per_page'));

        $query = Incident::query()
            ->whereAccountId(request('account_id'))
            ->when(request('sort'), fn ($query) => $query->orderBy(request('sort'), request('direction', 'asc')));

        return IncidentResource::collection($query->paginate($perPage));
    }
}
