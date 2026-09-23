<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimahubAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OfficeSetting::updateOrCreate(
            ['id' => 1],
            [
                'office_name' => 'Kantor Instansi MagangHub',
                'latitude' => -6.17539240,
                'longitude' => 106.82715280,
                'radius_meters' => 100,
                'work_start_time' => '07:30:00',
                'work_end_time' => '16:00:00',
            ]
        );
    }

    public function test_login_page_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIMAHUB');
        $response->assertSee('Email atau NIM');
    }

    public function test_admin_can_login_with_email_and_access_dashboard()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@simahub.id'],
            [
                'name' => 'Admin',
                'nim' => 'ADMIN',
                'institution' => 'Instansi MagangHub',
                'role' => 'admin',
                'password' => bcrypt('admin123'),
            ]
        );

        $response = $this->post('/login', [
            'login' => 'admin@simahub.id',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_login_with_nim_and_access_dashboard()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@simahub.id'],
            [
                'name' => 'Admin',
                'nim' => 'ADMIN',
                'institution' => 'Instansi MagangHub',
                'role' => 'admin',
                'password' => bcrypt('admin123'),
            ]
        );

        $response = $this->post('/login', [
            'login' => 'ADMIN',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_student_can_login_and_access_student_dashboard()
    {
        $student = User::create([
            'name' => 'Siswa Tes',
            'email' => 'siswates@magang.id',
            'nim' => 'TEST1234',
            'institution' => 'Universitas Indonesia',
            'role' => 'mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'TEST1234',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/mahasiswa/dashboard');
        $this->assertAuthenticatedAs($student);

        $student->delete();
    }

    public function test_attendance_rule_on_friday_wfh_is_accepted_anywhere()
    {
        // Set test date to Friday at 07:15 (Tepat Waktu)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 7, 15, 0)); // 25 Sept 2026 is Friday

        $student = User::create([
            'name' => 'Mahasiswa WFH',
            'email' => 'wfh@magang.id',
            'nim' => 'WFH001',
            'institution' => 'ITB',
            'role' => 'mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Location 50 km away from office (e.g. Bandung / remote)
        $response = $this->post('/mahasiswa/attendance/check-in', [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);

        $response->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $student->id)->where('date', '2026-09-25')->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('WFH', $attendance->type);
        $this->assertEquals('tepat_waktu', $attendance->status);

        // Cleanup
        $attendance->delete();
        $student->delete();
        Carbon::setTestNow();
    }

    public function test_attendance_rule_on_thursday_outside_radius_is_rejected()
    {
        // Set test date to Thursday (24 Sept 2026)
        Carbon::setTestNow(Carbon::create(2026, 9, 24, 7, 15, 0)); // Thursday

        $student = User::create([
            'name' => 'Mahasiswa Jauh',
            'email' => 'jauh@magang.id',
            'nim' => 'JAUH001',
            'institution' => 'UGM',
            'role' => 'mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Location 5 km away
        $response = $this->post('/mahasiswa/attendance/check-in', [
            'latitude' => -6.200000,
            'longitude' => 106.850000,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $student->id,
            'date' => '2026-09-24',
        ]);

        $student->delete();
        Carbon::setTestNow();
    }

    public function test_attendance_rule_after_0730_is_marked_as_terlambat()
    {
        // Set test date to Friday at 08:00 (Terlambat)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 8, 0, 0));

        $student = User::create([
            'name' => 'Mahasiswa Telat',
            'email' => 'telat@magang.id',
            'nim' => 'TELAT001',
            'institution' => 'UI',
            'role' => 'mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        $response = $this->post('/mahasiswa/attendance/check-in', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        $response->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $student->id)->where('date', '2026-09-25')->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('terlambat', $attendance->status);

        $attendance->delete();
        $student->delete();
        Carbon::setTestNow();
    }

    public function test_checkout_before_1600_is_rejected()
    {
        // 25 Sept 2026 07:15
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 7, 15, 0));

        $student = User::create([
            'name' => 'Mahasiswa Pulang Cepat',
            'email' => 'cepat@magang.id',
            'nim' => 'CEPAT001',
            'institution' => 'UNDIP',
            'role' => 'mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Check in first
        $this->post('/mahasiswa/attendance/check-in', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        // Attempt checkout at 15:30 (before 16:00)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 15, 30, 0));

        $checkoutResponse = $this->post('/mahasiswa/attendance/check-out', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        $checkoutResponse->assertSessionHas('error');

        // Attempt checkout at 16:05 (allowed!)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 16, 5, 0));

        $validCheckoutResponse = $this->post('/mahasiswa/attendance/check-out', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        $validCheckoutResponse->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $student->id)->where('date', '2026-09-25')->first();
        $this->assertNotNull($attendance->check_out_time);

        $attendance->delete();
        $student->delete();
        Carbon::setTestNow();
    }
}
