<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOrganizationNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_names_can_be_updated()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $response = $this->put('/organizations/'.$user->currentOrganization->id, [
            'name' => 'Test Organization',
        ]);

        $this->assertCount(1, $user->fresh()->ownedOrganizations);
        $this->assertEquals('Test Organization', $user->currentOrganization->fresh()->name);
    }
}
