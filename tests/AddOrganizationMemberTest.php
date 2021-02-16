<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\AddOrganizationMember;
use App\Actions\Jetstream\CreateOrganization;
use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Membership;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;
use Laravel\Sanctum\TransientToken;

class AddOrganizationMemberTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Organization::class, OrganizationPolicy::class);

        Jetstream::useUserModel(User::class);
    }

    public function test_organization_members_can_be_added()
    {
        Jetstream::role('admin', 'Admin', ['foo']);

        $this->migrate();

        $organization = $this->createOrganization();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $action = new AddOrganizationMember;

        $action->add($organization->owner, $organization, 'adam@laravel.com', 'admin');

        $organization = $organization->fresh();

        $this->assertCount(1, $organization->users);

        $this->assertInstanceOf(Membership::class, $organization->users[0]->membership);

        $this->assertTrue($otherUser->hasOrganizationRole($organization, 'admin'));
        $this->assertFalse($otherUser->hasOrganizationRole($organization, 'editor'));
        $this->assertFalse($otherUser->hasOrganizationRole($organization, 'foobar'));

        $organization->users->first()->withAccessToken(new TransientToken);

        $this->assertTrue($organization->users->first()->hasOrganizationPermission($organization, 'foo'));
        $this->assertFalse($organization->users->first()->hasOrganizationPermission($organization, 'bar'));
    }

    public function test_user_email_address_must_exist()
    {
        $this->expectException(ValidationException::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $action = new AddOrganizationMember;

        $action->add($organization->owner, $organization, 'missing@laravel.com', 'admin');

        $this->assertCount(1, $organization->fresh()->users);
    }

    public function test_user_cant_already_be_on_organization()
    {
        $this->expectException(ValidationException::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $action = new AddOrganizationMember;

        $action->add($organization->owner, $organization, 'adam@laravel.com', 'admin');
        $this->assertTrue(true);
        $action->add($organization->owner, $organization->fresh(), 'adam@laravel.com', 'admin');
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
