<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\AddsOrganizationMembers;
use Laravel\Jetstream\Events\AddingOrganizationMember;
use Laravel\Jetstream\Events\OrganizationMemberAdded;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class AddOrganizationMember implements AddsOrganizationMembers
{
    /**
     * Add a new organization member to the given organization.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function add($user, $organization, string $email, string $role = null)
    {
        Gate::forUser($user)->authorize('addOrganizationMember', $organization);

        $this->validate($organization, $email, $role);

        $newOrganizationMember = Jetstream::findUserByEmailOrFail($email);

        AddingOrganizationMember::dispatch($organization, $newOrganizationMember);

        $organization->users()->attach(
            $newOrganizationMember, ['role' => $role]
        );

        OrganizationMemberAdded::dispatch($organization, $newOrganizationMember);
    }

    /**
     * Validate the add member operation.
     *
     * @param  mixed  $organization
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    protected function validate($organization, string $email, ?string $role)
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules(), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnOrganization($organization, $email)
        )->validateWithBag('addOrganizationMember');
    }

    /**
     * Get the validation rules for adding a organization member.
     *
     * @return array
     */
    protected function rules()
    {
        return array_filter([
            'email' => ['required', 'email', 'exists:users'],
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the organization.
     *
     * @param  mixed  $organization
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureUserIsNotAlreadyOnOrganization($organization, string $email)
    {
        return function ($validator) use ($organization, $email) {
            $validator->errors()->addIf(
                $organization->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the organization.')
            );
        };
    }
}
