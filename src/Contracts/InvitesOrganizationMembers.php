<?php

namespace Laravel\Jetstream\Contracts;

interface InvitesOrganizationMembers
{
    /**
     * Invite a new organization member to the given organization.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  string  $email
     * @return void
     */
    public function invite($user, $organization, string $email, string $role = null);
}
