<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateOrganizationNameForm;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateOrganizationNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_names_can_be_updated()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        Livewire::test(UpdateOrganizationNameForm::class, ['organization' => $user->currentOrganization])
                    ->set(['state' => ['name' => 'Test Organization']])
                    ->call('updateOrganizationName');

        $this->assertCount(1, $user->fresh()->ownedOrganizations);
        $this->assertEquals('Test Organization', $user->currentOrganization->fresh()->name);
    }
}
