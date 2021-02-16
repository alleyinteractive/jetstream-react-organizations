<?php

namespace Laravel\Jetstream\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Laravel\Jetstream\OrganizationInvitation as OrganizationInvitationModel;

class OrganizationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The organization invitation instance.
     *
     * @var \Laravel\Jetstream\OrganizationInvitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     *
     * @param  \Laravel\Jetstream\OrganizationInvitation  $invitation
     * @return void
     */
    public function __construct(OrganizationInvitationModel $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('jetstream::mail.organization-invitation', ['acceptUrl' => URL::signedRoute('organization-invitations.accept', [
            'invitation' => $this->invitation,
        ])])->subject(__('Organization Invitation'));
    }
}
