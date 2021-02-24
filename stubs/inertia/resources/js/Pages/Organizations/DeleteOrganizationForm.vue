<template>
    <jet-action-section>
        <template #title>
            Delete Organization
        </template>

        <template #description>
            Permanently delete this organization.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Once a organization is deleted, all of its resources and data will be permanently deleted. Before deleting this organization, please download any data or information regarding this organization that you wish to retain.
            </div>

            <div class="mt-5">
                <jet-danger-button @click.native="confirmOrganizationDeletion">
                    Delete Organization
                </jet-danger-button>
            </div>

            <!-- Delete Organization Confirmation Modal -->
            <jet-confirmation-modal :show="confirmingOrganizationDeletion" @close="confirmingOrganizationDeletion = false">
                <template #title>
                    Delete Organization
                </template>

                <template #content>
                    Are you sure you want to delete this organization? Once a organization is deleted, all of its resources and data will be permanently deleted.
                </template>

                <template #footer>
                    <jet-secondary-button @click.native="confirmingOrganizationDeletion = false">
                        Cancel
                    </jet-secondary-button>

                    <jet-danger-button class="ml-2" @click.native="deleteOrganization" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Delete Organization
                    </jet-danger-button>
                </template>
            </jet-confirmation-modal>
        </template>
    </jet-action-section>
</template>

<script>
    import JetActionSection from '@/Jetstream/ActionSection'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
    import JetDangerButton from '@/Jetstream/DangerButton'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'

    export default {
        props: ['organization'],

        components: {
            JetActionSection,
            JetConfirmationModal,
            JetDangerButton,
            JetSecondaryButton,
        },

        data() {
            return {
                confirmingOrganizationDeletion: false,
                deleting: false,

                form: this.$inertia.form()
            }
        },

        methods: {
            confirmOrganizationDeletion() {
                this.confirmingOrganizationDeletion = true
            },

            deleteOrganization() {
                this.form.delete(route('organizations.destroy', this.organization), {
                    errorBag: 'deleteOrganization'
                });
            },
        },
    }
</script>
