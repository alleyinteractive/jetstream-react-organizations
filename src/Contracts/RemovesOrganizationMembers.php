<?php

namespace Laravel\Jetstream\Contracts;

interface RemovesOrganizationMembers
{
    /**
     * Remove the organization member from the given organization.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  mixed  $organizationMember
     * @return void
     */
    public function remove($user, $organization, $organizationMember);
}
