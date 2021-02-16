<?php

namespace Laravel\Jetstream;

use Illuminate\Database\Eloquent\Model;

class OrganizationInvitation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the organization that the invitation belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Jetstream::organizationModel());
    }
}
