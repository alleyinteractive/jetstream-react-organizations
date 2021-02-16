<?php

namespace Laravel\Jetstream\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Laravel\Jetstream\Actions\ValidateOrganizationDeletion;
use Laravel\Jetstream\Contracts\CreatesOrganizations;
use Laravel\Jetstream\Contracts\DeletesOrganizations;
use Laravel\Jetstream\Contracts\UpdatesOrganizationNames;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\RedirectsActions;

class OrganizationController extends Controller
{
    use RedirectsActions;

    /**
     * Show the organization management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @return \Inertia\Response
     */
    public function show(Request $request, $organizationId)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($organizationId);

        Gate::authorize('view', $organization);

        return Jetstream::inertia()->render($request, 'Organizations/Show', [
            'organization' => $organization->load('owner', 'users', 'organizationInvitations'),
            'availableRoles' => array_values(Jetstream::$roles),
            'availablePermissions' => Jetstream::$permissions,
            'defaultPermissions' => Jetstream::$defaultPermissions,
            'permissions' => [
                'canAddOrganizationMembers' => Gate::check('addOrganizationMember', $organization),
                'canDeleteOrganization' => Gate::check('delete', $organization),
                'canRemoveOrganizationMembers' => Gate::check('removeOrganizationMember', $organization),
                'canUpdateOrganization' => Gate::check('update', $organization),
            ],
        ]);
    }

    /**
     * Show the organization creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Jetstream::newOrganizationModel());

        return Inertia::render('Organizations/Create');
    }

    /**
     * Create a new organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $creator = app(CreatesOrganizations::class);

        $creator->create($request->user(), $request->all());

        return $this->redirectPath($creator);
    }

    /**
     * Update the given organization's name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $organizationId)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($organizationId);

        app(UpdatesOrganizationNames::class)->update($request->user(), $organization, $request->all());

        return back(303);
    }

    /**
     * Delete the given organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $organizationId)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($organizationId);

        app(ValidateOrganizationDeletion::class)->validate($request->user(), $organization);

        $deleter = app(DeletesOrganizations::class);

        $deleter->delete($organization);

        return $this->redirectPath($deleter);
    }
}
