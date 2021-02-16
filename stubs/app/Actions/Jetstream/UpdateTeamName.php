<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesOrganizationNames;

class UpdateOrganizationName implements UpdatesOrganizationNames
{
    /**
     * Validate and update the given organization's name.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  array  $input
     * @return void
     */
    public function update($user, $organization, array $input)
    {
        Gate::forUser($user)->authorize('update', $organization);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateOrganizationName');

        $organization->forceFill([
            'name' => $input['name'],
        ])->save();
    }
}
