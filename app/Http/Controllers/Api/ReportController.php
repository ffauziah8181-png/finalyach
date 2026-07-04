<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(protected ReportService $reportService) {}

    public function summary(Request $request)
    {
        $periode = $request->get('periode', 'bulan');

        if (! in_array($periode, ['minggu', 'bulan', 'tahun'])) {
            return $this->error('Periode tidak valid. Gunakan: minggu, bulan, atau tahun.', null, 422);
        }

        $summary = $this->reportService->summary($request->user()->id, $periode);

        return $this->success('Berhasil mengambil laporan keuangan.', $summary);
    }
}
