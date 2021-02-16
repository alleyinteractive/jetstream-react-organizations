<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Organization Settings
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <update-organization-name-form :organization="organization" :permissions="permissions" />

                <organization-member-manager class="mt-10 sm:mt-0"
                            :organization="organization"
                            :available-roles="availableRoles"
                            :user-permissions="permissions" />

                <template v-if="permissions.canDeleteOrganization && ! organization.personal_organization">
                    <jet-section-border />

                    <delete-organization-form class="mt-10 sm:mt-0" :organization="organization" />
                </template>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import OrganizationMemberManager from './OrganizationMemberManager'
    import AppLayout from '@/Layouts/AppLayout'
    import DeleteOrganizationForm from './DeleteOrganizationForm'
    import JetSectionBorder from '@/Jetstream/SectionBorder'
    import UpdateOrganizationNameForm from './UpdateOrganizationNameForm'

    export default {
        props: [
            'organization',
            'availableRoles',
            'permissions',
        ],

        components: {
            AppLayout,
            DeleteOrganizationForm,
            JetSectionBorder,
            OrganizationMemberManager,
            UpdateOrganizationNameForm,
        },
    }
</script>
