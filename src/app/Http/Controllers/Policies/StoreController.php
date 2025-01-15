<?php declare(strict_types=1);

namespace App\Http\Controllers\Policies;

use App\Enums\PolicyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policies\StoreRequest;
use App\Policy;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        $policies = collect($request->policies)
            ->map(fn ($policyType) => [
                'account_id' => $request->account_id,
                'tenant_domain' => $request->tenant_domain,
                'product' => $request->product,
                'type' => $policyType,
                'status' => PolicyStatus::ACTIVE,
            ])
            ->all();

        Policy::upsert($policies, ['account_id', 'type'], ['status' => PolicyStatus::ACTIVE]);

        return response()->json([], Response::HTTP_CREATED);
    }
}
