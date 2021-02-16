<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class OrganizationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The organization instance.
     *
     * @var \App\Models\Organization
     */
    public $organization;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function __construct($organization)
    {
        $this->organization = $organization;
    }
}
