<?php

namespace Laravel\Jetstream\Contracts;

interface CreatesOrganizations
{
    /**
     * Validate and create a new organization for the given user.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return mixed
     */
    public function create($user, array $input);
}
