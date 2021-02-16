<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\OrganizationMemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class RemoveOrganizationMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_members_can_be_removed_from_organizations()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $user->currentOrganization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->set('organizationMemberIdBeingRemoved', $otherUser->id)
                        ->call('removeOrganizationMember');

        $this->assertCount(0, $user->currentOrganization->fresh()->users);
    }

    public function test_only_organization_owner_can_remove_organization_members()
    {
        $user = User::factory()->withPersonalOrganization()->create();

        $user->currentOrganization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->set('organizationMemberIdBeingRemoved', $user->id)
                        ->call('removeOrganizationMember')
                        ->assertStatus(403);
    }
}
