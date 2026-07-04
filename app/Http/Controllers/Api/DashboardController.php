<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(protected DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        $summary = $this->dashboardService->summaryForUser($request->user()->id);
        $summary['catatan_terbaru'] = TransactionResource::collection($summary['catatan_terbaru']);

        return $this->success('Berhasil mengambil data dashboard.', $summary);
    }
}
