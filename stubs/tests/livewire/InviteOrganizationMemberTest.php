<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Http\Livewire\OrganizationMemberManager;
use Laravel\Jetstream\Mail\OrganizationInvitation;
use Livewire\Livewire;
use Tests\TestCase;

class InviteOrganizationMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_members_can_be_invited_to_organization()
    {
        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->set('addOrganizationMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addOrganizationMember');

        Mail::assertSent(OrganizationInvitation::class);

        $this->assertCount(1, $user->currentOrganization->fresh()->organizationInvitations);
    }

    public function test_organization_member_invitations_can_be_cancelled()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        // Add the organization member...
        $component = Livewire::test(OrganizationMemberManager::class, ['organization' => $user->currentOrganization])
                        ->set('addOrganizationMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addOrganizationMember');

        $invitationId = $user->currentOrganization->fresh()->organizationInvitations->first()->id;

        // Cancel the organization invitation...
        $component->call('cancelOrganizationInvitation', $invitationId);

        $this->assertCount(0, $user->currentOrganization->fresh()->organizationInvitations);
    }
}
