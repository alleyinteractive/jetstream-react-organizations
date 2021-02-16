<?php

namespace Laravel\Jetstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Actions\ValidateOrganizationDeletion;
use Laravel\Jetstream\Contracts\DeletesOrganizations;
use Laravel\Jetstream\RedirectsActions;
use Livewire\Component;

class DeleteOrganizationForm extends Component
{
    use RedirectsActions;

    /**
     * The organization instance.
     *
     * @var mixed
     */
    public $organization;

    /**
     * Indicates if organization deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingOrganizationDeletion = false;

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
     * Delete the organization.
     *
     * @param  \Laravel\Jetstream\Actions\ValidateOrganizationDeletion  $validator
     * @param  \Laravel\Jetstream\Contracts\DeletesOrganizations  $deleter
     * @return void
     */
    public function deleteOrganization(ValidateOrganizationDeletion $validator, DeletesOrganizations $deleter)
    {
        $validator->validate(Auth::user(), $this->organization);

        $deleter->delete($this->organization);

        return $this->redirectPath($deleter);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('organizations.delete-organization-form');
    }
}
