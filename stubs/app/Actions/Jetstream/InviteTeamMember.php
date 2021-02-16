<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\Contracts\InvitesOrganizationMembers;
use Laravel\Jetstream\Events\InvitingOrganizationMember;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Mail\OrganizationInvitation;
use Laravel\Jetstream\Rules\Role;

class InviteOrganizationMember implements InvitesOrganizationMembers
{
    /**
     * Invite a new organization member to the given organization.
     *
     * @param  mixed  $user
     * @param  mixed  $organization
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function invite($user, $organization, string $email, string $role = null)
    {
        Gate::forUser($user)->authorize('addOrganizationMember', $organization);

        $this->validate($organization, $email, $role);

        InvitingOrganizationMember::dispatch($organization, $email, $role);

        $invitation = $organization->organizationInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new OrganizationInvitation($invitation));
    }

    /**
     * Validate the invite member operation.
     *
     * @param  mixed  $organization
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    protected function validate($organization, string $email, ?string $role)
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($organization), [
            'email.unique' => __('This user has already been invited to the organization.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnOrganization($organization, $email)
        )->validateWithBag('addOrganizationMember');
    }

    /**
     * Get the validation rules for inviting a organization member.
     *
     * @param  mixed  $organization
     * @return array
     */
    protected function rules($organization)
    {
        return array_filter([
            'email' => ['required', 'email', Rule::unique('organization_invitations')->where(function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })],
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the organization.
     *
     * @param  mixed  $organization
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureUserIsNotAlreadyOnOrganization($organization, string $email)
    {
        return function ($validator) use ($organization, $email) {
            $validator->errors()->addIf(
                $organization->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the organization.')
            );
        };
    }
}
