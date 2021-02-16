<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RemovingOrganizationMember
{
    use Dispatchable;

    /**
     * The organization instance.
     *
     * @var mixed
     */
    public $organization;

    /**
     * The organization member being removed.
     *
     * @var mixed
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $organization
     * @param  mixed  $user
     * @return void
     */
    public function __construct($organization, $user)
    {
        $this->organization = $organization;
        $this->user = $user;
    }
}
