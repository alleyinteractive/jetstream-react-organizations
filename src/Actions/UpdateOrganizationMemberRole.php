<?php

namespace Laravel\Jetstream\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Events\OrganizationMemberUpdated;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class UpdateOrganizationMemberRole
{
    /**
     * Update the role for the given organization member.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  int  $organizationMemberId
     * @param  string  $role
     * @return void
     */
    public function update($user, $organization, $organizationMemberId, string $role)
    {
        Gate::forUser($user)->authorize('updateOrganizationMember', $organization);

        Validator::make([
            'role' => $role,
        ], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $organization->users()->updateExistingPivot($organizationMemberId, [
            'role' => $role,
        ]);

        OrganizationMemberUpdated::dispatch($organization->fresh(), Jetstream::findUserByIdOrFail($organizationMemberId));
    }
}
