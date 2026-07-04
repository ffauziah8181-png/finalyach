<?php

namespace App\Services;

use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use App\Repositories\Contracts\SavingsGoalRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SavingsGoalService
{
    public function __construct(
        protected SavingsGoalRepositoryInterface $goals
    ) {}

    public function listForUser(int $userId, ?string $status): Collection
    {
        return $this->goals->allForUser($userId, $status);
    }

    public function create(int $userId, array $data, ?UploadedFile $foto = null): SavingsGoal
    {
        $data['user_id'] = $userId;

        if ($foto) {
            $data['foto_sampul'] = $foto->store('tabungan', 'public');
        }

        return $this->goals->create($data);
    }

    public function update(SavingsGoal $goal, array $data, ?UploadedFile $foto = null): SavingsGoal
    {
        if ($foto) {
            if ($goal->foto_sampul) {
                Storage::disk('public')->delete($goal->foto_sampul);
            }
            $data['foto_sampul'] = $foto->store('tabungan', 'public');
        }

        return $this->goals->update($goal, $data);
    }

    /**
     * Catat setoran/penarikan dan update nominal terkumpul secara atomik.
     *
     * @throws Exception jika penarikan melebihi saldo terkumpul
     */
    public function catatSetoran(SavingsGoal $goal, string $tipe, float $jumlah, ?string $catatan): SavingsGoal
    {
        if ($tipe === 'penarikan' && $jumlah > (float) $goal->nominal_terkumpul) {
            throw new Exception('Jumlah penarikan melebihi saldo tabungan yang terkumpul.', 422);
        }

        DB::transaction(function () use ($goal, $tipe, $jumlah, $catatan) {
            SavingsTransaction::create([
                'savings_goal_id' => $goal->id,
                'tipe' => $tipe,
                'jumlah' => $jumlah,
                'catatan' => $catatan,
            ]);

            $perubahan = $tipe === 'setoran' ? $jumlah : -$jumlah;
            $goal->increment('nominal_terkumpul', $perubahan);

            if ($goal->nominal_terkumpul >= $goal->nominal_target) {
                $goal->update(['status' => 'tercapai']);
            }
        });

        return $goal->fresh(['riwayat']);
    }

    public function delete(SavingsGoal $goal): bool
    {
        if ($goal->foto_sampul) {
            Storage::disk('public')->delete($goal->foto_sampul);
        }

        return $this->goals->delete($goal);
    }
}
