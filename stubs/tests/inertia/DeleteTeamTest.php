<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizations_can_be_deleted()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $user->ownedOrganizations()->save($organization = Organization::factory()->make([
            'personal_organization' => false,
        ]));

        $organization->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'test-role']
        );

        $response = $this->delete('/organizations/'.$organization->id);

        $this->assertNull($organization->fresh());
        $this->assertCount(0, $otherUser->fresh()->organizations);
    }

    public function test_personal_organizations_cant_be_deleted()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $response = $this->delete('/organizations/'.$user->currentOrganization->id);

        $this->assertNotNull($user->currentOrganization->fresh());
    }
}
