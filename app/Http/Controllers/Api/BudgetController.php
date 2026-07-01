<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $budgets = Budget::with('category')
            ->where('user_id', $request->user()->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'kategori' => $b->category->nama,
                'icon' => $b->category->icon,
                'jumlah_budget' => (float) $b->jumlah_budget,
                'terpakai' => $b->terpakai,
                'sisa' => (float) $b->jumlah_budget - $b->terpakai,
            ]);

        return response()->json($budgets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'jumlah_budget' => ['required', 'numeric', 'min:0'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2020'],
        ]);
        $data['user_id'] = $request->user()->id;

        $budget = Budget::updateOrCreate(
            ['user_id' => $data['user_id'], 'category_id' => $data['category_id'], 'bulan' => $data['bulan'], 'tahun' => $data['tahun']],
            ['jumlah_budget' => $data['jumlah_budget']]
        );

        return response()->json(['message' => 'Anggaran berhasil disimpan.', 'data' => $budget], 201);
    }

    public function destroy(Request $request, Budget $budget)
    {
        abort_if($budget->user_id !== $request->user()->id, 403, 'Tidak diizinkan.');
        $budget->delete();

        return response()->json(['message' => 'Anggaran berhasil dihapus.']);
    }
}
