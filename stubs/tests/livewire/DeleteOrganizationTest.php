<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\DeleteOrganizationForm;
use Livewire\Livewire;
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

        $component = Livewire::test(DeleteOrganizationForm::class, ['organization' => $organization->fresh()])
                                ->call('deleteOrganization');

        $this->assertNull($organization->fresh());
        $this->assertCount(0, $otherUser->fresh()->organizations);
    }

    public function test_personal_organizations_cant_be_deleted()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        $component = Livewire::test(DeleteOrganizationForm::class, ['organization' => $user->currentOrganization])
                                ->call('deleteOrganization')
                                ->assertHasErrors(['organization']);

        $this->assertNotNull($user->currentOrganization->fresh());
    }
}
