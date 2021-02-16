<?php

namespace Laravel\Jetstream\Contracts;

interface DeletesOrganizations
{
    /**
     * Delete the given organization.
     *
     * @param  mixed  $organization
     * @return void
     */
    public function delete($organization);
}
