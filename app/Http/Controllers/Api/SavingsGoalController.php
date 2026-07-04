<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavingsGoalRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Models\SavingsGoal;
use App\Services\SavingsGoalService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    use ApiResponse;

    public function __construct(protected SavingsGoalService $savingsGoalService) {}

    public function index(Request $request)
    {
        $goals = $this->savingsGoalService->listForUser($request->user()->id, $request->get('status'));

        return $this->success('Berhasil mengambil daftar target tabungan.', SavingsGoalResource::collection($goals));
    }

    public function store(SavingsGoalRequest $request)
    {
        $goal = $this->savingsGoalService->create(
            $request->user()->id,
            $request->validated(),
            $request->file('foto_sampul')
        );

        return $this->created('Target tabungan berhasil dibuat.', new SavingsGoalResource($goal));
    }

    public function show(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('view', $savingsGoal);

        return $this->success(
            'Berhasil mengambil detail target tabungan.',
            new SavingsGoalResource($savingsGoal->load(['riwayat' => fn ($q) => $q->orderByDesc('created_at')]))
        );
    }

    public function update(SavingsGoalRequest $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);

        $updated = $this->savingsGoalService->update(
            $savingsGoal,
            $request->validated(),
            $request->file('foto_sampul')
        );

        return $this->success('Target tabungan berhasil diperbarui.', new SavingsGoalResource($updated));
    }

    public function setoran(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('update', $savingsGoal);

        $data = $request->validate([
            'tipe' => ['required', 'in:setoran,penarikan'],
            'jumlah' => ['required', 'numeric', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $goal = $this->savingsGoalService->catatSetoran(
                $savingsGoal,
                $data['tipe'],
                (float) $data['jumlah'],
                $data['catatan'] ?? null
            );

            return $this->success('Berhasil dicatat.', new SavingsGoalResource($goal));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), null, $e->getCode() ?: 422);
        }
    }

    public function destroy(Request $request, SavingsGoal $savingsGoal)
    {
        $this->authorize('delete', $savingsGoal);

        $this->savingsGoalService->delete($savingsGoal);

        return $this->success('Target tabungan berhasil dihapus.');
    }
}
