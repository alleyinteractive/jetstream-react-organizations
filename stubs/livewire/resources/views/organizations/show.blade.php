<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organization Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organizations.update-organization-name-form', ['organization' => $organization])

            @livewire('organizations.organization-member-manager', ['organization' => $organization])

            @if (Gate::check('delete', $organization) && ! $organization->personal_organization)
                <x-jet-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('organizations.delete-organization-form', ['organization' => $organization])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
