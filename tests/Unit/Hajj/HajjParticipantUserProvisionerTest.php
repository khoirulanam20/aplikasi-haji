<?php

namespace Tests\Unit\Hajj;

use App\Models\AppConfig;
use App\Models\HajjParticipant;
use App\Models\Role;
use App\Models\User;
use App\Support\Hajj\HajjParticipantUserProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HajjParticipantUserProvisionerTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_configured_email_domain_when_email_not_provided(): void
    {
        config(['starterkit.hajj_participant_email_domain' => 'haji.example.com']);

        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $participant = HajjParticipant::create([
            'tahun_haji' => 2025,
            'nomor_porsi' => '1100458689',
            'nama' => 'Ahmad Peserta',
        ]);

        $result = app(HajjParticipantUserProvisioner::class)->provision($participant);

        $this->assertSame('1100458689@haji.example.com', $result['user']->email);
        $this->assertTrue($result['user']->is_active);
        $this->assertInstanceOf(User::class, $result['user']);
    }

    public function test_applies_db_email_domain_when_runtime_config_is_stale(): void
    {
        config(['starterkit.hajj_participant_email_domain' => 'peserta-haji.local']);

        AppConfig::singleton()->update([
            'hajj_participant_email_domain' => 'haji.example.com',
        ]);

        Role::create(['name' => 'user', 'guard_name' => 'web', 'title' => 'User', 'is_active' => true]);

        $participant = HajjParticipant::create([
            'tahun_haji' => 2025,
            'nomor_porsi' => '1100458689',
            'nama' => 'Ahmad Peserta',
        ]);

        $result = app(HajjParticipantUserProvisioner::class)->provision($participant);

        $this->assertSame('1100458689@haji.example.com', $result['user']->email);
    }
}
