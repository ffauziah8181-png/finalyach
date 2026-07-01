<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Laporan keuangan: total pemasukan/pengeluaran, breakdown per kategori, tren arus kas.
     * periode: minggu | bulan | tahun
     */
    public function summary(Request $request)
    {
        $periode = $request->get('periode', 'bulan');
        $user = $request->user();
        [$dari, $sampai] = $this->rentangTanggal($periode);

        $totalPemasukan = Transaction::where('user_id', $user->id)->where('tipe', 'pemasukan')
            ->whereBetween('tanggal', [$dari, $sampai])->sum('jumlah');

        $totalPengeluaran = Transaction::where('user_id', $user->id)->where('tipe', 'pengeluaran')
            ->whereBetween('tanggal', [$dari, $sampai])->sum('jumlah');

        $pengeluaranTerbesar = Transaction::with('category')
            ->where('user_id', $user->id)->where('tipe', 'pengeluaran')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderByDesc('jumlah')->first();

        $breakdownKategori = Transaction::with('category')
            ->where('user_id', $user->id)->where('tipe', 'pengeluaran')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select('category_id')->selectRaw('SUM(jumlah) as total')
            ->groupBy('category_id')->get()
            ->map(function ($row) use ($totalPengeluaran) {
                return [
                    'kategori' => $row->category->nama,
                    'total' => (float) $row->total,
                    'persentase' => $totalPengeluaran > 0 ? round(($row->total / $totalPengeluaran) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('persentase')->values();

        $trenArusKas = $this->trenArusKas($user->id, $periode, $dari, $sampai);

        return response()->json([
            'periode' => $periode,
            'total_pemasukan' => (float) $totalPemasukan,
            'total_pengeluaran' => (float) $totalPengeluaran,
            'saldo_bersih' => (float) $totalPemasukan - (float) $totalPengeluaran,
            'pengeluaran_terbesar' => $pengeluaranTerbesar ? [
                'kategori' => $pengeluaranTerbesar->category->nama,
                'jumlah' => (float) $pengeluaranTerbesar->jumlah,
                'catatan' => $pengeluaranTerbesar->catatan,
            ] : null,
            'breakdown_kategori' => $breakdownKategori,
            'tren_arus_kas' => $trenArusKas,
        ]);
    }

    private function rentangTanggal(string $periode): array
    {
        return match ($periode) {
            'minggu' => [now()->startOfWeek(), now()->endOfWeek()],
            'tahun' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function trenArusKas(int $userId, string $periode, $dari, $sampai): array
    {
        // Kelompokkan per minggu dalam sebulan, atau per bulan dalam setahun, dst.
        $format = match ($periode) {
            'minggu' => '%Y-%m-%d',
            'tahun' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $rows = Transaction::where('user_id', $userId)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw("DATE_FORMAT(tanggal, '{$format}') as label, tipe, SUM(jumlah) as total")
            ->groupBy('label', 'tipe')
            ->orderBy('label')
            ->get();

        $hasil = [];
        foreach ($rows as $row) {
            $hasil[$row->label]['label'] = $row->label;
            $hasil[$row->label][$row->tipe] = (float) $row->total;
        }

        return array_values($hasil);
    }
}
