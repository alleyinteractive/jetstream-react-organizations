<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesOrganizations;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The organization deleter implementation.
     *
     * @var \Laravel\Jetstream\Contracts\DeletesOrganizations
     */
    protected $deletesOrganizations;

    /**
     * Create a new action instance.
     *
     * @param  \Laravel\Jetstream\Contracts\DeletesOrganizations  $deletesOrganizations
     * @return void
     */
    public function __construct(DeletesOrganizations $deletesOrganizations)
    {
        $this->deletesOrganizations = $deletesOrganizations;
    }

    /**
     * Delete the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
    {
        DB::transaction(function () use ($user) {
            $this->deleteOrganizations($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }

    /**
     * Delete the organizations and organization associations attached to the user.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function deleteOrganizations($user)
    {
        $user->organizations()->detach();

        $user->ownedOrganizations->each(function ($organization) {
            $this->deletesOrganizations->delete($organization);
        });
    }
}
