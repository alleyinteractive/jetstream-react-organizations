<?php

namespace App\Actions\Jetstream;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\RemovesOrganizationMembers;
use Laravel\Jetstream\Events\OrganizationMemberRemoved;

class RemoveOrganizationMember implements RemovesOrganizationMembers
{
    /**
     * Remove the organization member from the given organization.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  mixed  $organizationMember
     * @return void
     */
    public function remove($user, $organization, $organizationMember)
    {
        $this->authorize($user, $organization, $organizationMember);

        $this->ensureUserDoesNotOwnOrganization($organizationMember, $organization);

        $organization->removeUser($organizationMember);

        OrganizationMemberRemoved::dispatch($organization, $organizationMember);
    }

    /**
     * Authorize that the user can remove the organization member.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  mixed  $organizationMember
     * @return void
     */
    protected function authorize($user, $organization, $organizationMember)
    {
        if (! Gate::forUser($user)->check('removeOrganizationMember', $organization) &&
            $user->id !== $organizationMember->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * Ensure that the currently authenticated user does not own the organization.
     *
     * @param  mixed  $organizationMember
     * @param  mixed  $organization
     * @return void
     */
    protected function ensureUserDoesNotOwnOrganization($organizationMember, $organization)
    {
        if ($organizationMember->id === $organization->owner->id) {
            throw ValidationException::withMessages([
                'organization' => [__('You may not leave a organization that you created.')],
            ])->errorBag('removeOrganizationMember');
        }
    }
}
