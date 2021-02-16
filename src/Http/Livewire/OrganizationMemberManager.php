<?php

namespace Laravel\Jetstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Actions\UpdateOrganizationMemberRole;
use Laravel\Jetstream\Contracts\AddsOrganizationMembers;
use Laravel\Jetstream\Contracts\InvitesOrganizationMembers;
use Laravel\Jetstream\Contracts\RemovesOrganizationMembers;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\OrganizationInvitation;
use Livewire\Component;

class OrganizationMemberManager extends Component
{
    /**
     * The organization instance.
     *
     * @var mixed
     */
    public $organization;

    /**
     * Indicates if a user's role is currently being managed.
     *
     * @var bool
     */
    public $currentlyManagingRole = false;

    /**
     * The user that is having their role managed.
     *
     * @var mixed
     */
    public $managingRoleFor;

    /**
     * The current role for the user that is having their role managed.
     *
     * @var string
     */
    public $currentRole;

    /**
     * Indicates if the application is confirming if a user wishes to leave the current organization.
     *
     * @var bool
     */
    public $confirmingLeavingOrganization = false;

    /**
     * Indicates if the application is confirming if a organization member should be removed.
     *
     * @var bool
     */
    public $confirmingOrganizationMemberRemoval = false;

    /**
     * The ID of the organization member being removed.
     *
     * @var int|null
     */
    public $organizationMemberIdBeingRemoved = null;

    /**
     * The "add organization member" form state.
     *
     * @var array
     */
    public $addOrganizationMemberForm = [
        'email' => '',
        'role' => null,
    ];

    /**
     * Mount the component.
     *
     * @param  mixed  $organization
     * @return void
     */
    public function mount($organization)
    {
        $this->organization = $organization;
    }

    /**
     * Add a new organization member to a organization.
     *
     * @return void
     */
    public function addOrganizationMember()
    {
        $this->resetErrorBag();

        if (Features::sendsOrganizationInvitations()) {
            app(InvitesOrganizationMembers::class)->invite(
                $this->user,
                $this->organization,
                $this->addOrganizationMemberForm['email'],
                $this->addOrganizationMemberForm['role']
            );
        } else {
            app(AddsOrganizationMembers::class)->add(
                $this->user,
                $this->organization,
                $this->addOrganizationMemberForm['email'],
                $this->addOrganizationMemberForm['role']
            );
        }

        $this->addOrganizationMemberForm = [
            'email' => '',
            'role' => null,
        ];

        $this->organization = $this->organization->fresh();

        $this->emit('saved');
    }

    /**
     * Cancel a pending organization member invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function cancelOrganizationInvitation($invitationId)
    {
        if (! empty($invitationId)) {
            $model = Jetstream::organizationInvitationModel();

            $model::whereKey($invitationId)->delete();
        }

        $this->organization = $this->organization->fresh();
    }

    /**
     * Allow the given user's role to be managed.
     *
     * @param  int  $userId
     * @return void
     */
    public function manageRole($userId)
    {
        $this->currentlyManagingRole = true;
        $this->managingRoleFor = Jetstream::findUserByIdOrFail($userId);
        $this->currentRole = $this->managingRoleFor->organizationRole($this->organization)->key;
    }

    /**
     * Save the role for the user being managed.
     *
     * @param  \Laravel\Jetstream\Actions\UpdateOrganizationMemberRole  $updater
     * @return void
     */
    public function updateRole(UpdateOrganizationMemberRole $updater)
    {
        $updater->update(
            $this->user,
            $this->organization,
            $this->managingRoleFor->id,
            $this->currentRole
        );

        $this->organization = $this->organization->fresh();

        $this->stopManagingRole();
    }

    /**
     * Stop managing the role of a given user.
     *
     * @return void
     */
    public function stopManagingRole()
    {
        $this->currentlyManagingRole = false;
    }

    /**
     * Remove the currently authenticated user from the organization.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesOrganizationMembers  $remover
     * @return void
     */
    public function leaveOrganization(RemovesOrganizationMembers $remover)
    {
        $remover->remove(
            $this->user,
            $this->organization,
            $this->user
        );

        $this->confirmingLeavingOrganization = false;

        $this->organization = $this->organization->fresh();

        return redirect(config('fortify.home'));
    }

    /**
     * Confirm that the given organization member should be removed.
     *
     * @param  int  $userId
     * @return void
     */
    public function confirmOrganizationMemberRemoval($userId)
    {
        $this->confirmingOrganizationMemberRemoval = true;

        $this->organizationMemberIdBeingRemoved = $userId;
    }

    /**
     * Remove a organization member from the organization.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesOrganizationMembers  $remover
     * @return void
     */
    public function removeOrganizationMember(RemovesOrganizationMembers $remover)
    {
        $remover->remove(
            $this->user,
            $this->organization,
            $user = Jetstream::findUserByIdOrFail($this->organizationMemberIdBeingRemoved)
        );

        $this->confirmingOrganizationMemberRemoval = false;

        $this->organizationMemberIdBeingRemoved = null;

        $this->organization = $this->organization->fresh();
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Get the available organization member roles.
     *
     * @return array
     */
    public function getRolesProperty()
    {
        return array_values(Jetstream::$roles);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('organizations.organization-member-manager');
    }
}
