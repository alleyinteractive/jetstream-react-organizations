<?php

namespace Laravel\Jetstream\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ValidateOrganizationDeletion
{
    /**
     * Validate that the organization can be deleted by the given user.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @return void
     */
    public function validate($user, $organization)
    {
        Gate::forUser($user)->authorize('delete', $organization);

        if ($organization->personal_organization) {
            throw ValidationException::withMessages([
                'organization' => __('You may not delete your personal organization.'),
            ])->errorBag('deleteOrganization');
        }
    }
}
