<?php

namespace Laravel\Jetstream\Tests;

use App\Actions\Jetstream\CreateOrganization;
use App\Actions\Jetstream\DeleteOrganization;
use App\Models\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Actions\ValidateOrganizationDeletion;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Tests\Fixtures\OrganizationPolicy;
use Laravel\Jetstream\Tests\Fixtures\User;

class DeleteOrganizationTest extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Gate::policy(Organization::class, OrganizationPolicy::class);
        Jetstream::useUserModel(User::class);
    }

    public function test_organization_can_be_deleted()
    {
        $this->migrate();

        $organization = $this->createOrganization();

        $action = new DeleteOrganization;

        $action->delete($organization);

        $this->assertNull($organization->fresh());
    }

    public function test_organization_deletion_can_be_validated()
    {
        Jetstream::useUserModel(User::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $action = new ValidateOrganizationDeletion;

        $action->validate($organization->owner, $organization);

        $this->assertTrue(true);
    }

    public function test_personal_organization_cant_be_deleted()
    {
        $this->expectException(ValidationException::class);

        Jetstream::useUserModel(User::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $organization->forceFill(['personal_organization' => true])->save();

        $action = new ValidateOrganizationDeletion;

        $action->validate($organization->owner, $organization);
    }

    public function test_non_owner_cant_delete_organization()
    {
        $this->expectException(AuthorizationException::class);

        Jetstream::useUserModel(User::class);

        $this->migrate();

        $organization = $this->createOrganization();

        $action = new ValidateOrganizationDeletion;

        $action->validate(User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]), $organization);
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
