<?php

namespace Laravel\Jetstream;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

trait HasOrganizations
{
    /**
     * Determine if the given organization is the current organization.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function isCurrentOrganization($organization)
    {
        return $organization->id === $this->currentOrganization->id;
    }

    /**
     * Get the current organization of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentOrganization()
    {
        if (is_null($this->current_organization_id) && $this->id) {
            $this->switchOrganization($this->personalOrganization());
        }

        return $this->belongsTo(Jetstream::organizationModel(), 'current_organization_id');
    }

    /**
     * Switch the user's context to the given organization.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function switchOrganization($organization)
    {
        if (! $this->belongsToOrganization($organization)) {
            return false;
        }

        $this->forceFill([
            'current_organization_id' => $organization->id,
        ])->save();

        $this->setRelation('currentOrganization', $organization);

        return true;
    }

    /**
     * Get all of the organizations the user owns or belongs to.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allOrganizations()
    {
        return $this->ownedOrganizations->merge($this->organizations)->sortBy('name');
    }

    /**
     * Get all of the organizations the user owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedOrganizations()
    {
        return $this->hasMany(Jetstream::organizationModel());
    }

    /**
     * Get all of the organizations the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations()
    {
        return $this->belongsToMany(Jetstream::organizationModel(), Jetstream::membershipModel())
                        ->withPivot('role')
                        ->withTimestamps()
                        ->as('membership');
    }

    /**
     * Get the user's "personal" organization.
     *
     * @return \App\Models\Organization
     */
    public function personalOrganization()
    {
        return $this->ownedOrganizations->where('personal_organization', true)->first();
    }

    /**
     * Determine if the user owns the given organization.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function ownsOrganization($organization)
    {
        return $this->id == $organization->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs to the given organization.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function belongsToOrganization($organization)
    {
        return $this->organizations->contains(function ($t) use ($organization) {
            return $t->id === $organization->id;
        }) || $this->ownsOrganization($organization);
    }

    /**
     * Get the role that the user has on the organization.
     *
     * @param  mixed  $organization
     * @return \Laravel\Jetstream\Role
     */
    public function organizationRole($organization)
    {
        if ($this->ownsOrganization($organization)) {
            return new OwnerRole;
        }

        if (! $this->belongsToOrganization($organization)) {
            return;
        }

        return Jetstream::findRole($organization->users->where(
            'id', $this->id
        )->first()->membership->role);
    }

    /**
     * Determine if the user has the given role on the given organization.
     *
     * @param  mixed  $organization
     * @param  string  $role
     * @return bool
     */
    public function hasOrganizationRole($organization, string $role)
    {
        if ($this->ownsOrganization($organization)) {
            return true;
        }

        return $this->belongsToOrganization($organization) && optional(Jetstream::findRole($organization->users->where(
            'id', $this->id
        )->first()->membership->role))->key === $role;
    }

    /**
     * Get the user's permissions for the given organization.
     *
     * @param  mixed  $organization
     * @return array
     */
    public function organizationPermissions($organization)
    {
        if ($this->ownsOrganization($organization)) {
            return ['*'];
        }

        if (! $this->belongsToOrganization($organization)) {
            return [];
        }

        return $this->organizationRole($organization)->permissions;
    }

    /**
     * Determine if the user has the given permission on the given organization.
     *
     * @param  mixed  $organization
     * @param  string  $permission
     * @return bool
     */
    public function hasOrganizationPermission($organization, string $permission)
    {
        if ($this->ownsOrganization($organization)) {
            return true;
        }

        if (! $this->belongsToOrganization($organization)) {
            return false;
        }

        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
            ! $this->tokenCan($permission) &&
            $this->currentAccessToken() !== null) {
            return false;
        }

        $permissions = $this->organizationPermissions($organization);

        return in_array($permission, $permissions) ||
               in_array('*', $permissions) ||
               (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
               (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}
