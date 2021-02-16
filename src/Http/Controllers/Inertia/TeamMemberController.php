<?php

namespace Laravel\Jetstream\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Jetstream\Actions\UpdateOrganizationMemberRole;
use Laravel\Jetstream\Contracts\AddsOrganizationMembers;
use Laravel\Jetstream\Contracts\InvitesOrganizationMembers;
use Laravel\Jetstream\Contracts\RemovesOrganizationMembers;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;

class OrganizationMemberController extends Controller
{
    /**
     * Add a new organization member to a organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $organizationId)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($organizationId);

        if (Features::sendsOrganizationInvitations()) {
            app(InvitesOrganizationMembers::class)->invite(
                $request->user(),
                $organization,
                $request->email ?: '',
                $request->role
            );
        } else {
            app(AddsOrganizationMembers::class)->add(
                $request->user(),
                $organization,
                $request->email ?: '',
                $request->role
            );
        }

        return back(303);
    }

    /**
     * Update the given organization member's role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $organizationId, $userId)
    {
        app(UpdateOrganizationMemberRole::class)->update(
            $request->user(),
            Jetstream::newOrganizationModel()->findOrFail($organizationId),
            $userId,
            $request->role
        );

        return back(303);
    }

    /**
     * Remove the given user from the given organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $organizationId, $userId)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($organizationId);

        app(RemovesOrganizationMembers::class)->remove(
            $request->user(),
            $organization,
            $user = Jetstream::findUserByIdOrFail($userId)
        );

        if ($request->user()->id === $user->id) {
            return redirect(config('fortify.home'));
        }

        return back(303);
    }
}
