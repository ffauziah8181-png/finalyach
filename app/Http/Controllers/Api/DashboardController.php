<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Data ringkasan untuk halaman Beranda:
     * total saldo, tren 7 hari, budget bulanan, pengeluaran terbesar, catatan terbaru.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // Tren saldo 7 hari terakhir
        $trenHarian = collect(range(6, 0))->map(function ($i) use ($user) {
            $tanggal = now()->subDays($i)->toDateString();
            $masuk = $user->transactions()->where('tipe', 'pemasukan')->whereDate('tanggal', $tanggal)->sum('jumlah');
            $keluar = $user->transactions()->where('tipe', 'pengeluaran')->whereDate('tanggal', $tanggal)->sum('jumlah');

            return [
                'tanggal' => $tanggal,
                'net' => (float) $masuk - (float) $keluar,
            ];
        });

        $persentasePerubahan = $this->hitungPersentasePerubahan($user->id);

        $budgetBulanan = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->get()
            ->map(fn ($b) => [
                'kategori' => $b->category->nama,
                'icon' => $b->category->icon,
                'budget' => (float) $b->jumlah_budget,
                'terpakai' => $b->terpakai,
            ]);

        $pengeluaranTerbesar = Transaction::with('category')
            ->where('user_id', $user->id)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->select('category_id')
            ->selectRaw('SUM(jumlah) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(3)
            ->get()
            ->map(fn ($t) => [
                'kategori' => $t->category->nama,
                'icon' => $t->category->icon,
                'total' => (float) $t->total,
            ]);

        $catatanTerbaru = Transaction::with('category')
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'total_saldo' => $user->saldo,
            'persentase_perubahan' => $persentasePerubahan,
            'tren_7_hari' => $trenHarian,
            'budget_bulanan' => $budgetBulanan,
            'pengeluaran_terbesar' => $pengeluaranTerbesar,
            'catatan_terbaru' => TransactionResource::collection($catatanTerbaru),
        ]);
    }

    private function hitungPersentasePerubahan(int $userId): float
    {
        $saldoMingguIni = $this->saldoRentang($userId, now()->subDays(7), now());
        $saldoMingguLalu = $this->saldoRentang($userId, now()->subDays(14), now()->subDays(7));

        if ($saldoMingguLalu == 0) {
            return $saldoMingguIni > 0 ? 100 : 0;
        }

        return round((($saldoMingguIni - $saldoMingguLalu) / abs($saldoMingguLalu)) * 100, 1);
    }

    private function saldoRentang(int $userId, $dari, $sampai): float
    {
        $masuk = Transaction::where('user_id', $userId)->where('tipe', 'pemasukan')
            ->whereBetween('tanggal', [$dari, $sampai])->sum('jumlah');
        $keluar = Transaction::where('user_id', $userId)->where('tipe', 'pengeluaran')
            ->whereBetween('tanggal', [$dari, $sampai])->sum('jumlah');

        return (float) $masuk - (float) $keluar;
    }
}
