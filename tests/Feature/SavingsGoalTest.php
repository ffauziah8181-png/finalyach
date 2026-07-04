<?php

namespace Tests\Feature;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsGoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dapat_membuat_target_tabungan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/savings-goals', [
            'nama_target' => 'Rumah Impian',
            'nominal_target' => 150000000,
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
        $this->assertDatabaseHas('savings_goals', ['nama_target' => 'Rumah Impian']);
    }

    public function test_setoran_menambah_nominal_terkumpul(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->create([
            'user_id' => $user->id,
            'nominal_target' => 1000000,
            'nominal_terkumpul' => 0,
        ]);

        $response = $this->actingAs($user)->postJson("/api/savings-goals/{$goal->id}/setoran", [
            'tipe' => 'setoran',
            'jumlah' => 200000,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(200000, $goal->fresh()->nominal_terkumpul);
    }

    public function test_target_otomatis_tercapai_saat_nominal_terpenuhi(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->create([
            'user_id' => $user->id,
            'nominal_target' => 500000,
            'nominal_terkumpul' => 400000,
        ]);

        $this->actingAs($user)->postJson("/api/savings-goals/{$goal->id}/setoran", [
            'tipe' => 'setoran',
            'jumlah' => 100000,
        ]);

        $this->assertEquals('tercapai', $goal->fresh()->status);
    }

    public function test_penarikan_melebihi_saldo_ditolak(): void
    {
        $user = User::factory()->create();
        $goal = SavingsGoal::factory()->create([
            'user_id' => $user->id,
            'nominal_terkumpul' => 50000,
        ]);

        $response = $this->actingAs($user)->postJson("/api/savings-goals/{$goal->id}/setoran", [
            'tipe' => 'penarikan',
            'jumlah' => 100000,
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertEquals(50000, $goal->fresh()->nominal_terkumpul);
    }

    public function test_user_lain_tidak_dapat_mengubah_target_tabungan_orang_lain(): void
    {
        $pemilik = User::factory()->create();
        $userLain = User::factory()->create();
        $goal = SavingsGoal::factory()->create(['user_id' => $pemilik->id]);

        $response = $this->actingAs($userLain)->putJson("/api/savings-goals/{$goal->id}", [
            'nama_target' => 'Diubah paksa',
            'nominal_target' => 1000000,
        ]);

        $response->assertStatus(403);
    }
}
