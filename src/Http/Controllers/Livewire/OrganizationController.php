<?php

namespace Laravel\Jetstream\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;

class OrganizationController extends Controller
{
    /**
     * Show the organization management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organizationId
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $organizationId)
    {
        $organization = Jetstream::newOrganizationModel()->findOrFail($organizationId);

        if (Gate::denies('view', $organization)) {
            abort(403);
        }

        return view('organizations.show', [
            'user' => $request->user(),
            'organization' => $organization,
        ]);
    }

    /**
     * Show the organization creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Jetstream::newOrganizationModel());

        return view('organizations.create', [
            'user' => $request->user(),
        ]);
    }
}
