<?php

namespace Tests\Feature;

use App\Models\OfficeSetting;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

/**
 * Catatan: endpoint /_deploy dengan token valid TIDAK dites end-to-end karena
 * akan menjalankan `optimize` dan menulis cache ke bootstrap/cache di mesin lokal.
 */
class DeployTest extends TestCase
{
    use RefreshDatabase;

    public function test_deploy_endpoint_rejects_missing_token_without_csrf_error(): void
    {
        config(['app.deploy_token' => 'secret-test']);

        // Harus 403 (token), bukan 419 (CSRF) — membuktikan middleware web tidak aktif
        $this->post('/_deploy')->assertForbidden();
    }

    public function test_deploy_endpoint_rejects_wrong_token(): void
    {
        config(['app.deploy_token' => 'secret-test']);

        $this->post('/_deploy', [], ['X-Deploy-Token' => 'wrong'])->assertForbidden();
    }

    public function test_deploy_endpoint_rejects_when_server_token_not_configured(): void
    {
        config(['app.deploy_token' => null]);

        $this->post('/_deploy', [], ['X-Deploy-Token' => ''])->assertForbidden();
    }

    public function test_production_seeder_creates_admins_and_office_setting(): void
    {
        config(['app.seed_admin_password' => 'rahasia-awal']);

        $this->seed(ProductionSeeder::class);

        $this->assertSame(1, OfficeSetting::count());
        $this->assertSame(2, User::where('role', 'admin')->count());
        $this->assertSame(0, User::where('role', '!=', 'admin')->count());
        $this->assertTrue(Hash::check('rahasia-awal', User::where('username', 'admins')->value('password')));
    }

    public function test_production_seeder_does_not_overwrite_existing_data(): void
    {
        config(['app.seed_admin_password' => 'rahasia-awal']);
        $this->seed(ProductionSeeder::class);

        // Simulasi admin sudah ganti password & mengubah radius geofence
        User::where('username', 'admin')->first()->update(['password' => 'password-baru']);
        OfficeSetting::first()->update(['radius_meters' => 250]);

        $this->seed(ProductionSeeder::class);

        $this->assertTrue(Hash::check('password-baru', User::where('username', 'admin')->value('password')));
        $this->assertSame(250, (int) OfficeSetting::first()->radius_meters);
        $this->assertSame(2, User::count());
    }

    public function test_production_seeder_requires_password(): void
    {
        config(['app.seed_admin_password' => null]);

        $this->expectException(RuntimeException::class);

        $this->seed(ProductionSeeder::class);
    }
}
