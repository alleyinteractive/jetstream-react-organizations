<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateOrganization;
use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;
use Laravel\Sanctum\TransientToken;

class OrganizationMemberControllerTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Organization::class, OrganizationPolicy::class);
        Jetstream::useUserModel(User::class);
    }

    public function test_organization_member_permissions_can_be_updated()
    {
        Jetstream::role('admin', 'Admin', ['foo', 'bar']);
        Jetstream::role('editor', 'Editor', ['baz', 'qux']);

        $this->migrate();

        $organization = $this->createOrganization();

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $organization->users()->attach($adam, ['role' => 'admin']);

        $response = $this->actingAs($organization->owner)->put('/organizations/'.$organization->id.'/members/'.$adam->id, [
            'role' => 'editor',
        ]);

        $response->assertRedirect();

        $adam = $adam->fresh();

        $adam->withAccessToken(new TransientToken);

        $this->assertTrue($adam->hasOrganizationPermission($organization, 'baz'));
        $this->assertTrue($adam->hasOrganizationPermission($organization, 'qux'));
    }

    public function test_organization_member_permissions_cant_be_updated_if_not_authorized()
    {
        $this->migrate();

        $organization = $this->createOrganization();

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $organization->users()->attach($adam, ['role' => 'admin']);

        $response = $this->actingAs($adam)->put('/organizations/'.$organization->id.'/members/'.$adam->id, [
            'role' => 'admin',
        ]);

        $response->assertStatus(403);
    }

    protected function createOrganization()
    {
        $action = new CreateOrganization;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        return $action->create($user, ['name' => 'Test Organization']);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('jetstream.stack', 'inertia');
        $app['config']->set('jetstream.features', ['organizations']);
    }
}
