<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\OrganizationMemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_leave_organizations()
    {
        $user = User::factory()->withPersonalOrganization()->create();

        $user->currentOrganization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->call('leaveOrganization');

        $this->assertCount(0, $user->currentOrganization->fresh()->users);
    }

    public function test_organization_owners_cant_leave_their_own_organization()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->call('leaveOrganization')
                        ->assertHasErrors(['organization']);

        $this->assertNotNull($user->currentOrganization->fresh());
    }
}
