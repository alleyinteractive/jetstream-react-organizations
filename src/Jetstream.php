<?php

namespace Laravel\Jetstream;

use Illuminate\Support\Arr;
use Laravel\Jetstream\Contracts\AddsOrganizationMembers;
use Laravel\Jetstream\Contracts\CreatesOrganizations;
use Laravel\Jetstream\Contracts\DeletesOrganizations;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Contracts\InvitesOrganizationMembers;
use Laravel\Jetstream\Contracts\RemovesOrganizationMembers;
use Laravel\Jetstream\Contracts\UpdatesOrganizationNames;

class Jetstream
{
    /**
     * Indicates if Jetstream routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * The roles that are available to assign to users.
     *
     * @var array
     */
    public static $roles = [];

    /**
     * The permissions that exist within the application.
     *
     * @var array
     */
    public static $permissions = [];

    /**
     * The default permissions that should be available to new entities.
     *
     * @var array
     */
    public static $defaultPermissions = [];

    /**
     * The user model that should be used by Jetstream.
     *
     * @var string
     */
    public static $userModel = 'App\\Models\\User';

    /**
     * The organization model that should be used by Jetstream.
     *
     * @var string
     */
    public static $organizationModel = 'App\\Models\\Organization';

    /**
     * The membership model that should be used by Jetstream.
     *
     * @var string
     */
    public static $membershipModel = 'App\\Models\\Membership';

    /**
     * The organization invitation model that should be used by Jetstream.
     *
     * @var string
     */
    public static $organizationInvitationModel = 'App\\Models\\OrganizationInvitation';

    /**
     * The Inertia manager instance.
     *
     * @var \Laravel\Jetstream\InertiaManager
     */
    public static $inertiaManager;

    /**
     * Determine if Jetstream has registered roles.
     *
     * @return bool
     */
    public static function hasRoles()
    {
        return count(static::$roles) > 0;
    }

    /**
     * Find the role with the given key.
     *
     * @param  string  $key
     * @return \Laravel\Jetstream\Role
     */
    public static function findRole(string $key)
    {
        return static::$roles[$key] ?? null;
    }

    /**
     * Define a role.
     *
     * @param  string  $key
     * @param  string  $name
     * @param  array  $permissions
     * @return \Laravel\Jetstream\Role
     */
    public static function role(string $key, string $name, array $permissions)
    {
        static::$permissions = collect(array_merge(static::$permissions, $permissions))
                                    ->unique()
                                    ->sort()
                                    ->values()
                                    ->all();

        return tap(new Role($key, $name, $permissions), function ($role) use ($key) {
            static::$roles[$key] = $role;
        });
    }

    /**
     * Determine if any permissions have been registered with Jetstream.
     *
     * @return bool
     */
    public static function hasPermissions()
    {
        return count(static::$permissions) > 0;
    }

    /**
     * Define the available API token permissions.
     *
     * @param  array  $permissions
     * @return static
     */
    public static function permissions(array $permissions)
    {
        static::$permissions = $permissions;

        return new static;
    }

    /**
     * Define the default permissions that should be available to new API tokens.
     *
     * @param  array  $permissions
     * @return static
     */
    public static function defaultApiTokenPermissions(array $permissions)
    {
        static::$defaultPermissions = $permissions;

        return new static;
    }

    /**
     * Return the permissions in the given list that are actually defined permissions for the application.
     *
     * @param  array  $permissions
     * @return array
     */
    public static function validPermissions(array $permissions)
    {
        return array_values(array_intersect($permissions, static::$permissions));
    }

    /**
     * Determine if Jetstream is managing profile photos.
     *
     * @return bool
     */
    public static function managesProfilePhotos()
    {
        return Features::managesProfilePhotos();
    }

    /**
     * Determine if Jetstream is supporting API features.
     *
     * @return bool
     */
    public static function hasApiFeatures()
    {
        return Features::hasApiFeatures();
    }

    /**
     * Determine if Jetstream is supporting organization features.
     *
     * @return bool
     */
    public static function hasOrganizationFeatures()
    {
        return Features::hasOrganizationFeatures();
    }

    /**
     * Determine if the application is using the terms confirmation feature.
     *
     * @return bool
     */
    public static function hasTermsAndPrivacyPolicyFeature()
    {
        return Features::hasTermsAndPrivacyPolicyFeature();
    }

    /**
     * Determine if the application is using any account deletion features.
     *
     * @return bool
     */
    public static function hasAccountDeletionFeatures()
    {
        return Features::hasAccountDeletionFeatures();
    }

    /**
     * Find a user instance by the given ID.
     *
     * @param  int  $id
     * @return mixed
     */
    public static function findUserByIdOrFail($id)
    {
        return static::newUserModel()->where('id', $id)->firstOrFail();
    }

    /**
     * Find a user instance by the given email address or fail.
     *
     * @param  string  $email
     * @return mixed
     */
    public static function findUserByEmailOrFail(string $email)
    {
        return static::newUserModel()->where('email', $email)->firstOrFail();
    }

    /**
     * Get the name of the user model used by the application.
     *
     * @return string
     */
    public static function userModel()
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     *
     * @return mixed
     */
    public static function newUserModel()
    {
        $model = static::userModel();

        return new $model;
    }

    /**
     * Specify the user model that should be used by Jetstream.
     *
     * @param  string  $model
     * @return static
     */
    public static function useUserModel(string $model)
    {
        static::$userModel = $model;

        return new static;
    }

    /**
     * Get the name of the organization model used by the application.
     *
     * @return string
     */
    public static function organizationModel()
    {
        return static::$organizationModel;
    }

    /**
     * Get a new instance of the organization model.
     *
     * @return mixed
     */
    public static function newOrganizationModel()
    {
        $model = static::organizationModel();

        return new $model;
    }

    /**
     * Specify the organization model that should be used by Jetstream.
     *
     * @param  string  $model
     * @return static
     */
    public static function useOrganizationModel(string $model)
    {
        static::$organizationModel = $model;

        return new static;
    }

    /**
     * Get the name of the membership model used by the application.
     *
     * @return string
     */
    public static function membershipModel()
    {
        return static::$membershipModel;
    }

    /**
     * Specify the membership model that should be used by Jetstream.
     *
     * @param  string  $model
     * @return static
     */
    public static function useMembershipModel(string $model)
    {
        static::$membershipModel = $model;

        return new static;
    }

    /**
     * Get the name of the organization invitation model used by the application.
     *
     * @return string
     */
    public static function organizationInvitationModel()
    {
        return static::$organizationInvitationModel;
    }

    /**
     * Specify the organization invitation model that should be used by Jetstream.
     *
     * @param  string  $model
     * @return static
     */
    public static function useOrganizationInvitationModel(string $model)
    {
        static::$organizationInvitationModel = $model;

        return new static;
    }

    /**
     * Register a class / callback that should be used to create organizations.
     *
     * @param  string  $class
     * @return void
     */
    public static function createOrganizationsUsing(string $class)
    {
        return app()->singleton(CreatesOrganizations::class, $class);
    }

    /**
     * Register a class / callback that should be used to update organization names.
     *
     * @param  string  $class
     * @return void
     */
    public static function updateOrganizationNamesUsing(string $class)
    {
        return app()->singleton(UpdatesOrganizationNames::class, $class);
    }

    /**
     * Register a class / callback that should be used to add organization members.
     *
     * @param  string  $class
     * @return void
     */
    public static function addOrganizationMembersUsing(string $class)
    {
        return app()->singleton(AddsOrganizationMembers::class, $class);
    }

    /**
     * Register a class / callback that should be used to add organization members.
     *
     * @param  string  $class
     * @return void
     */
    public static function inviteOrganizationMembersUsing(string $class)
    {
        return app()->singleton(InvitesOrganizationMembers::class, $class);
    }

    /**
     * Register a class / callback that should be used to remove organization members.
     *
     * @param  string  $class
     * @return void
     */
    public static function removeOrganizationMembersUsing(string $class)
    {
        return app()->singleton(RemovesOrganizationMembers::class, $class);
    }

    /**
     * Register a class / callback that should be used to delete organizations.
     *
     * @param  string  $class
     * @return void
     */
    public static function deleteOrganizationsUsing(string $class)
    {
        return app()->singleton(DeletesOrganizations::class, $class);
    }

    /**
     * Register a class / callback that should be used to delete users.
     *
     * @param  string  $class
     * @return void
     */
    public static function deleteUsersUsing(string $class)
    {
        return app()->singleton(DeletesUsers::class, $class);
    }

    /**
     * Manage Jetstream's Inertia settings.
     *
     * @return \Laravel\Jetstream\InertiaManager
     */
    public static function inertia()
    {
        if (is_null(static::$inertiaManager)) {
            static::$inertiaManager = new InertiaManager;
        }

        return static::$inertiaManager;
    }

    /**
     * Find the path to a localized Markdown resource.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function localizedMarkdownPath($name)
    {
        $localName = preg_replace('#(\.md)$#i', '.'.app()->getLocale().'$1', $name);

        return Arr::first([
            resource_path('markdown/'.$localName),
            resource_path('markdown/'.$name),
        ], function ($path) {
            return file_exists($path);
        });
    }

    /**
     * Configure Jetstream to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }
}
