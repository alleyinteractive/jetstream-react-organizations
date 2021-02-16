<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateOrganization;
use App\Actions\Jetstream\RemoveOrganizationMember;
use App\Models\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Events\RemovingOrganizationMember;
use Laravel\Jetstream\Events\OrganizationMemberRemoved;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;

class RemoveOrganizationMemberTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Organization::class, OrganizationPolicy::class);

        Jetstream::useUserModel(User::class);
    }

    public function test_organization_members_can_be_removed()
    {
        Event::fake([OrganizationMemberRemoved::class]);

        $this->migrate();

        $organization = $this->createOrganization();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $organization->users()->attach($otherUser, ['role' => null]);

        $this->assertCount(1, $organization->fresh()->users);

        Auth::login($organization->owner);

        $action = new RemoveOrganizationMember;

        $action->remove($organization->owner, $organization, $otherUser);

        $this->assertCount(0, $organization->fresh()->users);

        Event::assertDispatched(OrganizationMemberRemoved::class);
    }

    public function test_a_organization_owner_cant_remove_themselves()
    {
        $this->expectException(ValidationException::class);

        Event::fake([RemovingOrganizationMember::class]);

        $this->migrate();

        $organization = $this->createOrganization();

        Auth::login($organization->owner);

        $action = new RemoveOrganizationMember;

        $action->remove($organization->owner, $organization, $organization->owner);
    }

    public function test_the_user_must_be_authorized_to_remove_organization_members()
    {
        $this->expectException(AuthorizationException::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $abigail = User::forceCreate([
            'name' => 'Abigail Otwell',
            'email' => 'abigail@laravel.com',
            'password' => 'secret',
        ]);

        $organization->users()->attach($adam, ['role' => null]);
        $organization->users()->attach($abigail, ['role' => null]);

        Auth::login($organization->owner);

        $action = new RemoveOrganizationMember;

        $action->remove($adam, $organization, $abigail);
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
}
