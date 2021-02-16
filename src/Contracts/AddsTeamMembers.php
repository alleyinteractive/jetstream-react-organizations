<?php

namespace Laravel\Jetstream\Contracts;

interface AddsOrganizationMembers
{
    /**
     * Add a new organization member to the given organization.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  string  $email
     * @return void
     */
    public function add($user, $organization, string $email, string $role = null);
}
