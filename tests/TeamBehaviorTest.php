<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateOrganization;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Organization;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\TransientToken;

class OrganizationBehaviorTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(\App\Models\Organization::class, OrganizationPolicy::class);
        Jetstream::useUserModel(User::class);
    }

    public function test_organization_relationship_methods()
    {
        $this->migrate();

        $action = new CreateOrganization;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $organization = $action->create($user, ['name' => 'Test Organization']);

        $this->assertInstanceOf(Organization::class, $organization);

        $this->assertTrue($user->belongsToOrganization($organization));
        $this->assertTrue($user->ownsOrganization($organization));
        $this->assertCount(1, $user->fresh()->ownedOrganizations);
        $this->assertCount(1, $user->fresh()->allOrganizations());

        $organization->forceFill(['personal_organization' => true])->save();

        $this->assertEquals($organization->id, $user->fresh()->personalOrganization()->id);
        $this->assertEquals($organization->id, $user->fresh()->currentOrganization->id);
        $this->assertTrue($user->hasOrganizationPermission($organization, 'foo'));

        // Test with another user that isn't on the organization...
        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $this->assertFalse($otherUser->belongsToOrganization($organization));
        $this->assertFalse($otherUser->ownsOrganization($organization));
        $this->assertFalse($otherUser->hasOrganizationPermission($organization, 'foo'));

        // Add the other user to the organization...
        Jetstream::role('editor', 'Editor', ['foo']);

        $otherUser->organizations()->attach($organization, ['role' => 'editor']);
        $otherUser = $otherUser->fresh();

        $this->assertTrue($otherUser->belongsToOrganization($organization));
        $this->assertFalse($otherUser->ownsOrganization($organization));

        $this->assertTrue($otherUser->hasOrganizationPermission($organization, 'foo'));
        $this->assertFalse($otherUser->hasOrganizationPermission($organization, 'bar'));

        $this->assertTrue($organization->userHasPermission($otherUser, 'foo'));
        $this->assertFalse($organization->userHasPermission($otherUser, 'bar'));

        $otherUser->withAccessToken(new TransientToken);

        $this->assertTrue($otherUser->belongsToOrganization($organization));
        $this->assertFalse($otherUser->ownsOrganization($organization));

        $this->assertTrue($otherUser->hasOrganizationPermission($organization, 'foo'));
        $this->assertFalse($otherUser->hasOrganizationPermission($organization, 'bar'));

        $this->assertTrue($organization->userHasPermission($otherUser, 'foo'));
        $this->assertFalse($organization->userHasPermission($otherUser, 'bar'));
    }

    public function test_has_organization_permission_checks_token_permissions()
    {
        Jetstream::role('admin', 'Administrator', ['foo']);

        $this->migrate();

        $action = new CreateOrganization;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $organization = $action->create($user, ['name' => 'Test Organization']);

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $authToken = new Sanctum;
        $adam = $authToken->actingAs($adam, ['bar'], []);

        $organization->users()->attach($adam, ['role' => 'admin']);

        $this->assertFalse($adam->hasOrganizationPermission($organization, 'foo'));

        $john = User::forceCreate([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'secret',
        ]);

        $authToken = new Sanctum;
        $john = $authToken->actingAs($john, ['foo'], []);

        $organization->users()->attach($john, ['role' => 'admin']);

        $this->assertTrue($john->hasOrganizationPermission($organization, 'foo'));
    }

    public function test_user_does_not_need_to_refresh_after_switching_organizations()
    {
        $this->migrate();

        $action = new CreateOrganization;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        $personalOrganization = $action->create($user, ['name' => 'Personal Organization']);

        $personalOrganization->forceFill(['personal_organization' => true])->save();

        $this->assertTrue($user->isCurrentOrganization($personalOrganization));

        $anotherOrganization = $action->create($user, ['name' => 'Test Organization']);

        $this->assertTrue($user->isCurrentOrganization($anotherOrganization));
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}
