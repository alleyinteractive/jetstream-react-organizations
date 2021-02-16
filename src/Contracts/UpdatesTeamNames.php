<?php

namespace Laravel\Jetstream\Contracts;

interface UpdatesOrganizationNames
{
    /**
     * Validate and update the given organization's name.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  array  $input
     * @return void
     */
    public function update($user, $organization, array $input);
}
