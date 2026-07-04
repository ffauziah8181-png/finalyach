<?php

namespace App\Services;

use App\Models\Budget;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactions
    ) {}

    public function summaryForUser(int $userId): array
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $trenHarian = collect(range(6, 0))->map(function ($i) use ($userId) {
            $tanggal = now()->subDays($i)->toDateString();
            $masuk = $this->transactions->totalByType($userId, 'pemasukan', $tanggal, $tanggal);
            $keluar = $this->transactions->totalByType($userId, 'pengeluaran', $tanggal, $tanggal);

            return ['tanggal' => $tanggal, 'net' => $masuk - $keluar];
        });

        $saldoMingguIni = $this->saldoRentang($userId, now()->subDays(7)->toDateString(), now()->toDateString());
        $saldoMingguLalu = $this->saldoRentang($userId, now()->subDays(14)->toDateString(), now()->subDays(7)->toDateString());
        $persentasePerubahan = $this->persentasePerubahan($saldoMingguIni, $saldoMingguLalu);

        $budgetBulanan = Budget::with('category')
            ->where('user_id', $userId)
            ->where('bulan', $bulanIni)->where('tahun', $tahunIni)
            ->get()
            ->map(fn ($b) => [
                'kategori' => $b->category->nama,
                'icon' => $b->category->icon,
                'budget' => (float) $b->jumlah_budget,
                'terpakai' => $b->terpakai,
            ]);

        $pengeluaranTerbesar = $this->transactions->topCategoriesByExpense($userId, $bulanIni, $tahunIni, 3)
            ->map(fn ($t) => [
                'kategori' => $t->category->nama,
                'icon' => $t->category->icon,
                'total' => (float) $t->total,
            ]);

        $totalMasuk = $this->transactions->totalByType($userId, 'pemasukan');
        $totalKeluar = $this->transactions->totalByType($userId, 'pengeluaran');

        return [
            'total_saldo' => $totalMasuk - $totalKeluar,
            'persentase_perubahan' => $persentasePerubahan,
            'tren_7_hari' => $trenHarian,
            'budget_bulanan' => $budgetBulanan,
            'pengeluaran_terbesar' => $pengeluaranTerbesar,
            'catatan_terbaru' => $this->transactions->latestForUser($userId, 5),
        ];
    }

    private function saldoRentang(int $userId, string $dari, string $sampai): float
    {
        $masuk = $this->transactions->totalByType($userId, 'pemasukan', $dari, $sampai);
        $keluar = $this->transactions->totalByType($userId, 'pengeluaran', $dari, $sampai);

        return $masuk - $keluar;
    }

    private function persentasePerubahan(float $sekarang, float $lalu): float
    {
        if ($lalu == 0.0) {
            return $sekarang > 0 ? 100.0 : 0.0;
        }

        return round((($sekarang - $lalu) / abs($lalu)) * 100, 1);
    }
}
