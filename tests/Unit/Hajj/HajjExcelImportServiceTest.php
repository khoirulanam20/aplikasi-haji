<?php

namespace Tests\Unit\Hajj;

use App\Models\HajjParticipant;
use App\Models\Role;
use App\Models\User;
use App\Support\Hajj\HajjExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class HajjExcelImportServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_bare_no_header_does_not_match_nomor_porsi_alias(): void
    {
        $service = app(HajjExcelImportService::class);
        $method = new ReflectionMethod($service, 'headerMatches');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service, 'no', 'no porsi'));
        $this->assertTrue($method->invoke($service, 'no porsi', 'no porsi'));
        $this->assertTrue($method->invoke($service, 'kd_porsi', 'kd_porsi'));
    }

    public function test_count_processable_rows_includes_database_duplicates_when_replacing(): void
    {
        $service = app(HajjExcelImportService::class);
        $method = new ReflectionMethod($service, 'countProcessableRows');
        $method->setAccessible(true);

        $rows = [
            ['status' => 'ready'],
            ['status' => 'duplicate', 'duplicate_source' => 'database'],
            ['status' => 'duplicate', 'duplicate_source' => 'file'],
        ];

        $this->assertSame(1, $method->invoke($service, $rows, false));
        $this->assertSame(2, $method->invoke($service, $rows, true));
    }

    public function test_format_import_error_shortens_duplicate_sql_message(): void
    {
        $service = app(HajjExcelImportService::class);
        $method = new ReflectionMethod($service, 'formatImportError');
        $method->setAccessible(true);

        $message = $method->invoke($service, new \RuntimeException(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '2023-1' for key 'haji_participants.haji_participants_tahun_haji_nomor_porsi_unique'"
        ));

        $this->assertSame('Duplikat nomor porsi pada tahun ini', $message);
    }

    public function test_sanitize_nomor_porsi_rejects_row_number_like_values(): void
    {
        $service = app(HajjExcelImportService::class);
        $method = new ReflectionMethod($service, 'sanitizeNomorPorsi');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($service, '1'));
        $this->assertNull($method->invoke($service, '44'));
        $this->assertSame('1100479760', $method->invoke($service, '1100479760'));
    }

    public function test_telepon_header_requires_telp_keyword(): void
    {
        $service = app(HajjExcelImportService::class);
        $method = new ReflectionMethod($service, 'headerMatchesField');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service, 'telepon', 'no', 'no telp'));
        $this->assertTrue($method->invoke($service, 'telepon', 'no telp/hp', 'no telp'));
    }

    public function test_replace_existing_reactivates_inactive_user_and_updates_email(): void
    {
        config(['starterkit.hajj_participant_email_domain' => 'haji.example.com']);

        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $user = User::factory()->create([
            'username' => '1100458689',
            'email' => '1100458689@peserta-haji.local',
            'is_active' => false,
        ]);

        HajjParticipant::create([
            'tahun_haji' => 2024,
            'nomor_porsi' => '1100458689',
            'nama' => 'Nama Lama',
            'user_id' => $user->id,
        ]);

        $service = app(HajjExcelImportService::class);
        $method = new ReflectionMethod($service, 'replaceExisting');
        $method->invoke($service, 2024, [
            'nomor_porsi' => '1100458689',
            'nama' => 'Nama Baru',
            'alamat' => null,
            'desa' => null,
            'kecamatan' => null,
            'telepon' => null,
            'kloter' => null,
            'rombongan' => null,
            'regu' => null,
        ], 1);

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('1100458689@haji.example.com', $user->email);
    }
}
