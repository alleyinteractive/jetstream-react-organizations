<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateOrganization;
use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;

class CurrentOrganizationControllerTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Organization::class, OrganizationPolicy::class);
        Jetstream::useUserModel(User::class);
    }

    public function test_can_switch_to_organization_the_user_belongs_to()
    {
        $this->migrate();

        $action = new CreateOrganization;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $organization = $action->create($user, ['name' => 'Test Organization']);

        $response = $this->actingAs($user)->put('/current-organization', ['organization_id' => $organization->id]);

        $response->assertRedirect('/home');

        $this->assertEquals($organization->id, $user->fresh()->currentOrganization->id);
        $this->assertTrue($user->isCurrentOrganization($organization));
    }

    public function test_cant_switch_to_organization_the_user_does_not_belong_to()
    {
        $this->migrate();

        $action = new CreateOrganization;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $organization = $action->create($user, ['name' => 'Test Organization']);

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $response = $this->actingAs($otherUser)->put('/current-organization', ['organization_id' => $organization->id]);

        $response->assertStatus(403);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('jetstream.stack', 'livewire');
        $app['config']->set('jetstream.features', ['organizations']);
    }
}
