<?php

namespace Tests\Feature\MasterData;

use App\Models\MasterData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(array $permissions): User
    {
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    public function test_master_data_can_be_updated(): void
    {
        $user = $this->userWithPermissions([
            'masterdata.list',
            'masterdata.edit',
            'masterdata.update',
        ]);

        $item = MasterData::query()->create([
            'name' => 'Item Lama',
            'description' => 'Deskripsi lama',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put('/app/master-data/'.$item->id, [
            'name' => 'Item Baru',
            'description' => 'Deskripsi baru',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('master_data', [
            'id' => $item->id,
            'name' => 'Item Baru',
            'description' => 'Deskripsi baru',
        ]);
    }
}
