<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Services\BudgetService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    use ApiResponse;

    public function __construct(protected BudgetService $budgetService) {}

    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $budgets = $this->budgetService->listForPeriod($request->user()->id, $bulan, $tahun);

        return $this->success('Berhasil mengambil daftar anggaran.', $budgets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'jumlah_budget' => ['required', 'numeric', 'min:0'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $budget = $this->budgetService->setBudget(
            $request->user()->id,
            $data['category_id'],
            (float) $data['jumlah_budget'],
            $data['bulan'],
            $data['tahun']
        );

        return $this->created('Anggaran berhasil disimpan.', $budget);
    }

    public function destroy(Request $request, Budget $budget)
    {
        $this->authorize('delete', $budget);

        $this->budgetService->delete($budget);

        return $this->success('Anggaran berhasil dihapus.');
    }
}
