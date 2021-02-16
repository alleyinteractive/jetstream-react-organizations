<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Mail\OrganizationInvitation;
use Tests\TestCase;

class InviteOrganizationMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_members_can_be_invited_to_organization()
    {
        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $response = $this->post('/organizations/'.$user->currentOrganization->id.'/members', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        Mail::assertSent(OrganizationInvitation::class);

        $this->assertCount(1, $user->currentOrganization->fresh()->organizationInvitations);
    }

    public function test_organization_member_invitations_can_be_cancelled()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $invitation = $user->currentOrganization->organizationInvitations()->create([
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        $response = $this->delete('/organization-invitations/'.$invitation->id);

        $this->assertCount(0, $user->currentOrganization->fresh()->organizationInvitations);
    }
}
