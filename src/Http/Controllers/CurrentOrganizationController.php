<?php

namespace Laravel\Jetstream\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Jetstream\Jetstream;

class CurrentOrganizationController extends Controller
{
    /**
     * Update the authenticated user's current organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($request->organization_id);

        if (! $request->user()->switchOrganization($organization)) {
            abort(403);
        }

        return redirect(config('fortify.home'), 303);
    }
}
