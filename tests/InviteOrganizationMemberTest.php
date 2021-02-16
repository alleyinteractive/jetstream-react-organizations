<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateOrganization;
use App\Actions\Jetstream\InviteOrganizationMember;
use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;

class InviteOrganizationMemberTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Organization::class, OrganizationPolicy::class);

        Jetstream::useUserModel(User::class);
    }

    public function test_organization_members_can_be_invited()
    {
        Mail::fake();

        Jetstream::role('admin', 'Admin', ['foo']);

        $this->migrate();

        $organization = $this->createOrganization();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $action = new InviteOrganizationMember;

        $action->invite($organization->owner, $organization, 'adam@laravel.com', 'admin');

        $organization = $organization->fresh();

        $this->assertCount(0, $organization->users);
        $this->assertCount(1, $organization->organizationInvitations);
        $this->assertEquals('adam@laravel.com', $organization->organizationInvitations->first()->email);
        $this->assertEquals($organization->id, $organization->organizationInvitations->first()->organization->id);
    }

    public function test_user_cant_already_be_on_organization()
    {
        Mail::fake();

        $this->expectException(ValidationException::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $action = new InviteOrganizationMember;

        $action->invite($organization->owner, $organization, 'adam@laravel.com', 'admin');
        $this->assertTrue(true);
        $action->invite($organization->owner, $organization->fresh(), 'adam@laravel.com', 'admin');
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
