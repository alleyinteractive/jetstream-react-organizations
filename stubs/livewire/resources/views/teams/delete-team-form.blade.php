<x-jet-action-section>
    <x-slot name="title">
        {{ __('Delete Organization') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Permanently delete this organization.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('Once a organization is deleted, all of its resources and data will be permanently deleted. Before deleting this organization, please download any data or information regarding this organization that you wish to retain.') }}
        </div>

        <div class="mt-5">
            <x-jet-danger-button wire:click="$toggle('confirmingOrganizationDeletion')" wire:loading.attr="disabled">
                {{ __('Delete Organization') }}
            </x-jet-danger-button>
        </div>

        <!-- Delete Organization Confirmation Modal -->
        <x-jet-confirmation-modal wire:model="confirmingOrganizationDeletion">
            <x-slot name="title">
                {{ __('Delete Organization') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Are you sure you want to delete this organization? Once a organization is deleted, all of its resources and data will be permanently deleted.') }}
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$toggle('confirmingOrganizationDeletion')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-jet-secondary-button>

                <x-jet-danger-button class="ml-2" wire:click="deleteOrganization" wire:loading.attr="disabled">
                    {{ __('Delete Organization') }}
                </x-jet-danger-button>
            </x-slot>
        </x-jet-confirmation-modal>
    </x-slot>
</x-jet-action-section>
