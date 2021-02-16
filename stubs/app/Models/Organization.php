<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\OrganizationCreated;
use Laravel\Jetstream\Events\OrganizationDeleted;
use Laravel\Jetstream\Events\OrganizationUpdated;
use Laravel\Jetstream\Organization as JetstreamOrganization;

class Organization extends JetstreamOrganization
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'personal_organization' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'personal_organization',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => OrganizationCreated::class,
        'updated' => OrganizationUpdated::class,
        'deleted' => OrganizationDeleted::class,
    ];
}
