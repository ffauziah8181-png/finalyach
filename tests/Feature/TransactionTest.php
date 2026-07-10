<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dapat_membuat_transaksi(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['tipe' => 'pengeluaran']);

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'category_id' => $category->id,
            'tipe' => 'pengeluaran',
            'jumlah' => 45000,
            'catatan' => 'Makan siang',
            'tanggal' => now()->toDateString(),
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'jumlah' => 45000,
        ]);
    }

    public function test_transaksi_dapat_dibuat_dengan_alias_field_flutter(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['tipe' => 'pengeluaran']);

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'categoryId' => $category->id,
            'type' => 'pengeluaran',
            'amount' => 45000,
            'note' => 'Makan siang',
            'date' => now()->toDateString(),
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'jumlah' => 45000,
            'catatan' => 'Makan siang',
        ]);
    }

    public function test_transaksi_gagal_dibuat_tanpa_field_wajib(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/transactions', []);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_user_hanya_dapat_melihat_transaksi_miliknya_sendiri(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $category = Category::factory()->create();

        $transaksiUserB = Transaction::factory()->create([
            'user_id' => $userB->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($userA)->getJson("/api/transactions/{$transaksiUserB->id}");

        $response->assertStatus(403);
    }

    public function test_user_dapat_menghapus_transaksi_miliknya(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_list_transaksi_dapat_difilter_berdasarkan_tipe(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Transaction::factory()->count(2)->create([
            'user_id' => $user->id, 'category_id' => $category->id, 'tipe' => 'pengeluaran',
        ]);
        Transaction::factory()->count(3)->create([
            'user_id' => $user->id, 'category_id' => $category->id, 'tipe' => 'pemasukan',
        ]);

        $response = $this->actingAs($user)->getJson('/api/transactions?tipe=pemasukan');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
}
