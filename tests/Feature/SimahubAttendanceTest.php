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
                'wfh_days' => [5],
                'work_hours' => [
                    1 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    2 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    3 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    4 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    5 => ['start' => '07:30:00', 'end' => '16:30:00'],
                ],
            ]
        );
    }

    public function test_login_page_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIMAHUB');
        $response->assertSee('Username');
    }

    public function test_admin_can_login_with_username_and_access_dashboard()
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Instansi',
                'role' => 'admin',
                'position' => 'Administrator',
                'password' => bcrypt('admin123'),
            ]
        );

        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);

        // Akses halaman rekapitulasi absensi admin
        $attendanceResponse = $this->get('/admin/attendance');
        $attendanceResponse->assertStatus(200);
        $attendanceResponse->assertSee('Data Presensi');
        $attendanceResponse->assertSee('Total Durasi Terlambat');
    }

    public function test_student_can_login_and_access_student_dashboard()
    {
        $student = User::create([
            'name' => 'Peserta Tes',
            'username' => 'peserta_tes',
            'position' => 'Web Developer',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'peserta_tes',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);

        $student->delete();
    }

    public function test_attendance_rule_on_friday_wfh_is_accepted_anywhere()
    {
        // Set test date to Friday at 07:15 (Tepat Waktu)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 7, 15, 0)); // 25 Sept 2026 is Friday

        $student = User::create([
            'name' => 'Peserta WFH',
            'username' => 'peserta_wfh',
            'position' => 'Data Analyst',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Location 50 km away from office (e.g. Bandung / remote)
        $response = $this->post('/intern/attendance/check-in', [
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
            'name' => 'Peserta Jauh',
            'username' => 'peserta_jauh',
            'position' => 'UI UX Designer',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Location 5 km away
        $response = $this->post('/intern/attendance/check-in', [
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
            'name' => 'Peserta Telat',
            'username' => 'peserta_telat',
            'position' => 'Technical Writer',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        $response = $this->post('/intern/attendance/check-in', [
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

    public function test_checkout_before_1600_on_thursday_is_rejected()
    {
        // 24 Sept 2026 (Thursday) 07:15
        Carbon::setTestNow(Carbon::create(2026, 9, 24, 7, 15, 0));

        $student = User::create([
            'name' => 'Peserta Pulang Kamis',
            'username' => 'peserta_pulang_kamis',
            'position' => 'Network Engineer',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Check in first
        $this->post('/intern/attendance/check-in', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        // Attempt checkout at 15:30 (before 16:00)
        Carbon::setTestNow(Carbon::create(2026, 9, 24, 15, 30, 0));

        $checkoutResponse = $this->post('/intern/attendance/check-out', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        $checkoutResponse->assertSessionHas('error');

        // Attempt checkout at 16:05 (allowed on Thursday!)
        Carbon::setTestNow(Carbon::create(2026, 9, 24, 16, 5, 0));

        $validCheckoutResponse = $this->post('/intern/attendance/check-out', [
            'latitude' => -6.17539240,
            'longitude' => 106.82715280,
        ]);

        $validCheckoutResponse->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $student->id)->where('date', '2026-09-24')->first();
        $this->assertNotNull($attendance->check_out_time);

        $attendance->delete();
        $student->delete();
        Carbon::setTestNow();
    }

    public function test_friday_checkout_before_1630_is_rejected_and_allowed_after_1630()
    {
        // 25 Sept 2026 (Friday) 07:15
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 7, 15, 0));

        $student = User::create([
            'name' => 'Peserta Pulang Jumat',
            'username' => 'peserta_pulang_jumat',
            'position' => 'Frontend Developer',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Check in first (Friday WFH)
        $this->post('/intern/attendance/check-in', [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);

        // Attempt checkout at 16:15 (after 16:00, but before Friday limit 16:30)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 16, 15, 0));

        $checkoutResponse = $this->post('/intern/attendance/check-out', [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);

        $checkoutResponse->assertSessionHas('error');

        // Attempt checkout at 16:35 (allowed on Friday!)
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 16, 35, 0));

        $validCheckoutResponse = $this->post('/intern/attendance/check-out', [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);

        $validCheckoutResponse->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $student->id)->where('date', '2026-09-25')->first();
        $this->assertNotNull($attendance->check_out_time);

        $attendance->delete();
        $student->delete();
        Carbon::setTestNow();
    }

    public function test_lateness_duration_calculation_and_badge()
    {
        $student = User::create([
            'name' => 'Peserta Uji Telat',
            'username' => 'uji_telat',
            'position' => 'Data Analyst',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        // Masuk jam 08:05 pada hari Kamis (limit 07:30 => telat 35 menit)
        $attendance = Attendance::create([
            'user_id' => $student->id,
            'date' => '2026-09-24', // Thursday
            'check_in_time' => '08:05:00',
            'status' => 'terlambat',
            'type' => 'WFO',
        ]);

        $this->assertEquals(35, $attendance->late_minutes);
        $this->assertEquals('0 jam 35 Menit', $attendance->late_duration_text);
        $this->assertStringContainsString('TELAT', $attendance->status_badge);
        $this->assertStringContainsString('(0 jam 35 Menit)', $attendance->status_badge);
        $this->assertStringContainsString("TELAT\n(0 jam 35 Menit)", $attendance->status_with_late_duration);

        $attendance->delete();
        $student->delete();
    }

    public function test_export_csv_headers_and_metadata_sync()
    {
        $admin = User::create([
            'name' => 'Admin Export',
            'username' => 'admin_export',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/export/csv?month=2026-09');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Verifikasi tidak ada kolom usang NIM
        $this->assertStringNotContainsString('NIM', $content);
        $this->assertStringNotContainsString('Perguruan Tinggi', $content);

        // Verifikasi kolom baru tersinkronisasi
        $this->assertStringContainsString('Username', $content);
        $this->assertStringContainsString('Posisi Magang', $content);
        $this->assertStringContainsString('No. WhatsApp / HP', $content);
        $this->assertStringContainsString('Durasi Terlambat', $content);
        $this->assertStringContainsString('Total Durasi Terlambat', $content);
        $this->assertStringContainsString('Jumat (07:30 - 16:30 WIB)', $content);

        $admin->delete();
    }

    public function test_wfh_days_text_and_schedule_sync_on_student_dashboard()
    {
        $office = OfficeSetting::getSetting();

        // 1. Uji jika WFH hanya Senin
        $office->update(['wfh_days' => [1]]);
        $this->assertEquals('Senin', $office->wfh_days_text);

        // 2. Uji jika WFH Senin dan Selasa
        $office->update(['wfh_days' => [1, 2]]);
        $this->assertEquals('Senin, Selasa', $office->wfh_days_text);

        // 3. Uji jika Full WFO (tidak ada WFH)
        $office->update(['wfh_days' => []]);
        $this->assertEquals('Tidak ada (Full WFO di kantor)', $office->wfh_days_text);

        // 4. Uji tampilan pada dashboard peserta magang saat WFH diset ke hari Selasa 08:00 - 17:00
        $workHours = $office->work_hours;
        $workHours[2] = ['start' => '08:00:00', 'end' => '17:00:00'];
        $office->update([
            'wfh_days' => [2],
            'work_hours' => $workHours,
        ]);

        $student = User::create([
            'name' => 'Peserta Sync Test',
            'username' => 'sync_test',
            'position' => 'Backend Engineer',
            'role' => 'intern',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($student);

        // Akses dashboard pada hari Selasa (22 Sept 2026)
        Carbon::setTestNow(Carbon::create(2026, 9, 22, 7, 50, 0)); // Tuesday
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSeeText('Hari WFH: Selasa');
        $response->assertSee('Batas Masuk (Hari Selasa):');
        $response->assertSee('08:00 WIB');
        $response->assertSee('Jam Pulang (Hari Selasa):');
        $response->assertSee('17:00 WIB');
        $response->assertSee('WFH (Bebas dari Rumah)');
        $response->assertSee('Jadwal Kerja & WFH Mingguan', false);
        $response->assertSee('Total Durasi Terlambat');

        $student->delete();
        Carbon::setTestNow();
    }
}
