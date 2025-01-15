<?php declare(strict_types=1);

namespace App\Http\Controllers\Policies;

use App\Enums\PolicyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policies\SummaryRequest;
use App\Http\Resources\PolicySummaryCollection;
use App\Policy;

class SummaryController extends Controller
{
    public function __invoke(SummaryRequest $request)
    {
        $policies = Policy::whereAccountId($request->account_id)->whereStatus(PolicyStatus::ACTIVE)->get();

        return new PolicySummaryCollection($policies);
    }
}
