<?php

use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\CurrentOrganizationController;
use Laravel\Jetstream\Http\Controllers\Inertia\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController;
use Laravel\Jetstream\Http\Controllers\Inertia\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController;
use Laravel\Jetstream\Http\Controllers\Inertia\OrganizationController;
use Laravel\Jetstream\Http\Controllers\Inertia\OrganizationMemberController;
use Laravel\Jetstream\Http\Controllers\Inertia\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;
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

        Route::delete('/user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])
                    ->name('other-browser-sessions.destroy');

        Route::delete('/user/profile-photo', [ProfilePhotoController::class, 'destroy'])
                    ->name('current-user-photo.destroy');

        if (Jetstream::hasAccountDeletionFeatures()) {
            Route::delete('/user', [CurrentUserController::class, 'destroy'])
                        ->name('current-user.destroy');
        }

        // API...
        if (Jetstream::hasApiFeatures()) {
            Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
            Route::post('/user/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
            Route::put('/user/api-tokens/{token}', [ApiTokenController::class, 'update'])->name('api-tokens.update');
            Route::delete('/user/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
        }

        // Organizations...
        if (Jetstream::hasOrganizationFeatures()) {
            Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
            Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
            Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
            Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
            Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
            Route::put('/current-organization', [CurrentOrganizationController::class, 'update'])->name('current-organization.update');
            Route::post('/organizations/{organization}/members', [OrganizationMemberController::class, 'store'])->name('organization-members.store');
            Route::put('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'update'])->name('organization-members.update');
            Route::delete('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'destroy'])->name('organization-members.destroy');

            Route::get('/organization-invitations/{invitation}', [OrganizationInvitationController::class, 'accept'])
                        ->middleware(['signed'])
                        ->name('organization-invitations.accept');

            Route::delete('/organization-invitations/{invitation}', [OrganizationInvitationController::class, 'destroy'])
                        ->name('organization-invitations.destroy');
        }
    });
});
