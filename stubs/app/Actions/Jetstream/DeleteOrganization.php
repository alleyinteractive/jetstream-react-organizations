<?php

namespace App\Actions\Jetstream;

use Laravel\Jetstream\Contracts\DeletesOrganizations;

class DeleteOrganization implements DeletesOrganizations
{
    /**
     * Delete the given organization.
     *
     * @param  mixed  $organization
     * @return void
     */
    public function delete($organization)
    {
        $organization->purge();
    }
}
