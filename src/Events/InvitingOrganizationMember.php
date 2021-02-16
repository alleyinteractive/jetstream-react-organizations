<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InvitingOrganizationMember
{
    use Dispatchable;

    /**
     * The organization instance.
     *
     * @var mixed
     */
    public $organization;

    /**
     * The email address of the invitee.
     *
     * @var mixed
     */
    public $email;

    /**
     * The role of the invitee.
     *
     * @var mixed
     */
    public $role;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $organization
     * @param  mixed  $email
     * @param  mixed  $role
     * @return void
     */
    public function __construct($organization, $email, $role)
    {
        $this->organization = $organization;
        $this->email = $email;
        $this->role = $role;
    }
}
