<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\OrganizationMemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateOrganizationMemberRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_member_roles_can_be_updated()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $user->currentOrganization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->set('managingRoleFor', $otherUser)
                        ->set('currentRole', 'editor')
                        ->call('updateRole');

        $this->assertTrue($otherUser->fresh()->hasOrganizationRole(
            $user->currentOrganization->fresh(), 'editor'
        ));
    }

    public function test_only_organization_owner_can_update_organization_member_roles()
    {
        $user = User::factory()->withPersonalOrganization()->create();

        $user->currentOrganization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->set('managingRoleFor', $otherUser)
                        ->set('currentRole', 'editor')
                        ->call('updateRole')
                        ->assertStatus(403);

        $this->assertTrue($otherUser->fresh()->hasOrganizationRole(
            $user->currentOrganization->fresh(), 'admin'
        ));
    }
}
