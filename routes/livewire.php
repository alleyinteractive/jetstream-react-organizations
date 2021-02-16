<?php

use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\CurrentOrganizationController;
use Laravel\Jetstream\Http\Controllers\Livewire\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Livewire\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Livewire\OrganizationController;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController;
use Laravel\Jetstream\Http\Controllers\OrganizationInvitationController;
use Laravel\Jetstream\Jetstream;

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
    if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
    }

    Route::group(['middleware' => ['auth', 'verified']], function () {
        // User & Profile...
        Route::get('/user/profile', [UserProfileController::class, 'show'])
                    ->name('profile.show');

        // API...
        if (Jetstream::hasApiFeatures()) {
            Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
        }

        // Organizations...
        if (Jetstream::hasOrganizationFeatures()) {
            Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
            Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
            Route::put('/current-organization', [CurrentOrganizationController::class, 'update'])->name('current-organization.update');

            Route::get('/organization-invitations/{invitation}', [OrganizationInvitationController::class, 'accept'])
                        ->middleware(['signed'])
                        ->name('organization-invitations.accept');
        }
    });
});
