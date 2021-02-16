<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\CreateOrganizationForm;
use Livewire\Livewire;
use Tests\TestCase;

class CreateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizations_can_be_created()
    {
        $this->actingAs($user = User::factory()->withPersonalOrganization()->create());

        Livewire::test(CreateOrganizationForm::class)
                    ->set(['state' => ['name' => 'Test Organization']])
                    ->call('createOrganization');

        $this->assertCount(2, $user->fresh()->ownedOrganizations);
        $this->assertEquals('Test Organization', $user->fresh()->ownedOrganizations()->latest('id')->first()->name);
    }
}
