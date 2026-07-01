<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavingsGoalRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status'); // berjalan | tercapai

        $query = SavingsGoal::where('user_id', $request->user()->id);

        if ($status) {
            $query->where('status', $status);
        }

        return SavingsGoalResource::collection($query->orderByDesc('created_at')->get());
    }

    public function store(SavingsGoalRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('foto_sampul')) {
            $data['foto_sampul'] = $request->file('foto_sampul')->store('tabungan', 'public');
        }

        $goal = SavingsGoal::create($data);

        return response()->json([
            'message' => 'Target tabungan berhasil dibuat.',
            'data' => new SavingsGoalResource($goal),
        ], 201);
    }

    public function show(Request $request, SavingsGoal $savingsGoal)
    {
        $this->pastikanMilikUser($request, $savingsGoal);

        return new SavingsGoalResource($savingsGoal->load(['riwayat' => fn ($q) => $q->orderByDesc('created_at')]));
    }

    public function update(SavingsGoalRequest $request, SavingsGoal $savingsGoal)
    {
        $this->pastikanMilikUser($request, $savingsGoal);

        $data = $request->validated();

        if ($request->hasFile('foto_sampul')) {
            $data['foto_sampul'] = $request->file('foto_sampul')->store('tabungan', 'public');
        }

        $savingsGoal->update($data);

        return response()->json([
            'message' => 'Target tabungan berhasil diperbarui.',
            'data' => new SavingsGoalResource($savingsGoal),
        ]);
    }

    /**
     * Tambah setoran/penarikan pada target tabungan (Riwayat Setoran).
     */
    public function setoran(Request $request, SavingsGoal $savingsGoal)
    {
        $this->pastikanMilikUser($request, $savingsGoal);

        $data = $request->validate([
            'tipe' => ['required', 'in:setoran,penarikan'],
            'jumlah' => ['required', 'numeric', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($savingsGoal, $data) {
            SavingsTransaction::create([
                'savings_goal_id' => $savingsGoal->id,
                'tipe' => $data['tipe'],
                'jumlah' => $data['jumlah'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            $perubahan = $data['tipe'] === 'setoran' ? $data['jumlah'] : -$data['jumlah'];
            $savingsGoal->increment('nominal_terkumpul', $perubahan);

            if ($savingsGoal->nominal_terkumpul >= $savingsGoal->nominal_target) {
                $savingsGoal->update(['status' => 'tercapai']);
            }
        });

        return response()->json([
            'message' => 'Berhasil dicatat.',
            'data' => new SavingsGoalResource($savingsGoal->fresh(['riwayat'])),
        ]);
    }

    public function destroy(Request $request, SavingsGoal $savingsGoal)
    {
        $this->pastikanMilikUser($request, $savingsGoal);
        $savingsGoal->delete();

        return response()->json(['message' => 'Target tabungan berhasil dihapus.']);
    }

    private function pastikanMilikUser(Request $request, SavingsGoal $savingsGoal): void
    {
        abort_if($savingsGoal->user_id !== $request->user()->id, 403, 'Tidak diizinkan.');
    }
}
