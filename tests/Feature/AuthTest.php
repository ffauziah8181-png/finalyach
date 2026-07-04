<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dapat_registrasi_dan_menerima_kode_otp(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.email', 'budi@example.com');

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user->otp_code);
        $this->assertNull($user->email_verified_at);
    }

    public function test_registrasi_gagal_jika_email_sudah_terdaftar(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_user_dapat_verifikasi_otp_dan_mendapat_token(): void
    {
        $user = User::factory()->unverified()->create([
            'otp_code' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verifikasi_otp_gagal_jika_kode_salah(): void
    {
        $user = User::factory()->unverified()->create([
            'otp_code' => '111111',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => '999999',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_user_terverifikasi_dapat_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'salahpassword',
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_login_gagal_jika_akun_belum_diverifikasi(): void
    {
        $user = User::factory()->unverified()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403)->assertJson(['success' => false]);
    }

    public function test_endpoint_terproteksi_menolak_akses_tanpa_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
