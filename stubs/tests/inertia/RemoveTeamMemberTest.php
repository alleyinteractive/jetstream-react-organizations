<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->delete('/organizations/'.$user->currentOrganization->id.'/members/'.$otherUser->id);

        $this->assertCount(0, $user->currentOrganization->fresh()->users);
    }

    public function test_only_organization_owner_can_remove_organization_members()
    {
        $user = User::factory()->withPersonalOrganization()->create();

        $user->currentOrganization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $response = $this->delete('/organizations/'.$user->currentOrganization->id.'/members/'.$user->id);

        $response->assertStatus(403);
    }
}
