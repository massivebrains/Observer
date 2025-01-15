<?php declare(strict_types=1);

namespace App\Http\Controllers\Policies;

use App\Enums\PolicyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policies\DeleteRequest;
use App\Policy;

class DeleteController extends Controller
{
    public function __invoke(DeleteRequest $request)
    {
        Policy::whereAccountId($request->account_id)
            ->whereIn('type', $request->policies)
            ->update(['status' => PolicyStatus::INACTIVE]);

        return response()->json([]);
    }
}
