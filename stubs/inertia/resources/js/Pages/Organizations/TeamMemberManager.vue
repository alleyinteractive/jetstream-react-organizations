<template>
    <div>
        <div v-if="userPermissions.canAddOrganizationMembers">
            <jet-section-border />

            <!-- Add Organization Member -->
            <jet-form-section @submitted="addOrganizationMember">
                <template #title>
                    Add Organization Member
                </template>

                <template #description>
                    Add a new organization member to your organization, allowing them to collaborate with you.
                </template>

                <template #form>
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-600">
                            Please provide the email address of the person you would like to add to this organization.
                        </div>
                    </div>

                    <!-- Member Email -->
                    <div class="col-span-6 sm:col-span-4">
                        <jet-label for="email" value="Email" />
                        <jet-input id="email" type="email" class="mt-1 block w-full" v-model="addOrganizationMemberForm.email" />
                        <jet-input-error :message="addOrganizationMemberForm.errors.email" class="mt-2" />
                    </div>

                    <!-- Role -->
                    <div class="col-span-6 lg:col-span-4" v-if="availableRoles.length > 0">
                        <jet-label for="roles" value="Role" />
                        <jet-input-error :message="addOrganizationMemberForm.errors.role" class="mt-2" />

                        <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
                            <button type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
                                            :class="{'border-t border-gray-200 rounded-t-none': i > 0, 'rounded-b-none': i != Object.keys(availableRoles).length - 1}"
                                            @click="addOrganizationMemberForm.role = role.key"
                                            v-for="(role, i) in availableRoles"
                                            :key="role.key">
                                <div :class="{'opacity-50': addOrganizationMemberForm.role && addOrganizationMemberForm.role != role.key}">
                                    <!-- Role Name -->
                                    <div class="flex items-center">
                                        <div class="text-sm text-gray-600" :class="{'font-semibold': addOrganizationMemberForm.role == role.key}">
                                            {{ role.name }}
                                        </div>

                                        <svg v-if="addOrganizationMemberForm.role == role.key" class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>

                                    <!-- Role Description -->
                                    <div class="mt-2 text-xs text-gray-600">
                                        {{ role.description }}
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </template>

                <template #actions>
                    <jet-action-message :on="addOrganizationMemberForm.recentlySuccessful" class="mr-3">
                        Added.
                    </jet-action-message>

                    <jet-button :class="{ 'opacity-25': addOrganizationMemberForm.processing }" :disabled="addOrganizationMemberForm.processing">
                        Add
                    </jet-button>
                </template>
            </jet-form-section>
        </div>

        <div v-if="organization.organization_invitations.length > 0 && userPermissions.canAddOrganizationMembers">
            <jet-section-border />

            <!-- Organization Member Invitations -->
            <jet-action-section class="mt-10 sm:mt-0">
                <template #title>
                    Pending Organization Invitations
                </template>

                <template #description>
                    These people have been invited to your organization and have been sent an invitation email. They may join the organization by accepting the email invitation.
                </template>

                <!-- Pending Organization Member Invitation List -->
                <template #content>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between" v-for="invitation in organization.organization_invitations" :key="invitation.id">
                            <div class="text-gray-600">{{ invitation.email }}</div>

                            <div class="flex items-center">
                                <!-- Cancel Organization Invitation -->
                                <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none"
                                                    @click="cancelOrganizationInvitation(invitation)"
                                                    v-if="userPermissions.canRemoveOrganizationMembers">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </jet-action-section>
        </div>

        <div v-if="organization.users.length > 0">
            <jet-section-border />

            <!-- Manage Organization Members -->
            <jet-action-section class="mt-10 sm:mt-0">
                <template #title>
                    Organization Members
                </template>

                <template #description>
                    All of the people that are part of this organization.
                </template>

                <!-- Organization Member List -->
                <template #content>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between" v-for="user in organization.users" :key="user.id">
                            <div class="flex items-center">
                                <img class="w-8 h-8 rounded-full" :src="user.profile_photo_url" :alt="user.name">
                                <div class="ml-4">{{ user.name }}</div>
                            </div>

                            <div class="flex items-center">
                                <!-- Manage Organization Member Role -->
                                <button class="ml-2 text-sm text-gray-400 underline"
                                        @click="manageRole(user)"
                                        v-if="userPermissions.canAddOrganizationMembers && availableRoles.length">
                                    {{ displayableRole(user.membership.role) }}
                                </button>

                                <div class="ml-2 text-sm text-gray-400" v-else-if="availableRoles.length">
                                    {{ displayableRole(user.membership.role) }}
                                </div>

                                <!-- Leave Organization -->
                                <button class="cursor-pointer ml-6 text-sm text-red-500"
                                                    @click="confirmLeavingOrganization"
                                                    v-if="$page.props.user.id === user.id">
                                    Leave
                                </button>

                                <!-- Remove Organization Member -->
                                <button class="cursor-pointer ml-6 text-sm text-red-500"
                                                    @click="confirmOrganizationMemberRemoval(user)"
                                                    v-if="userPermissions.canRemoveOrganizationMembers">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </jet-action-section>
        </div>

        <!-- Role Management Modal -->
        <jet-dialog-modal :show="currentlyManagingRole" @close="currentlyManagingRole = false">
            <template #title>
                Manage Role
            </template>

            <template #content>
                <div v-if="managingRoleFor">
                    <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
                        <button type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
                                        :class="{'border-t border-gray-200 rounded-t-none': i > 0, 'rounded-b-none': i !== Object.keys(availableRoles).length - 1}"
                                        @click="updateRoleForm.role = role.key"
                                        v-for="(role, i) in availableRoles"
                                        :key="role.key">
                            <div :class="{'opacity-50': updateRoleForm.role && updateRoleForm.role !== role.key}">
                                <!-- Role Name -->
                                <div class="flex items-center">
                                    <div class="text-sm text-gray-600" :class="{'font-semibold': updateRoleForm.role === role.key}">
                                        {{ role.name }}
                                    </div>

                                    <svg v-if="updateRoleForm.role === role.key" class="ml-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>

                                <!-- Role Description -->
                                <div class="mt-2 text-xs text-gray-600">
                                    {{ role.description }}
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </template>

            <template #footer>
                <jet-secondary-button @click.native="currentlyManagingRole = false">
                    Nevermind
                </jet-secondary-button>

                <jet-button class="ml-2" @click.native="updateRole" :class="{ 'opacity-25': updateRoleForm.processing }" :disabled="updateRoleForm.processing">
                    Save
                </jet-button>
            </template>
        </jet-dialog-modal>

        <!-- Leave Organization Confirmation Modal -->
        <jet-confirmation-modal :show="confirmingLeavingOrganization" @close="confirmingLeavingOrganization = false">
            <template #title>
                Leave Organization
            </template>

            <template #content>
                Are you sure you would like to leave this organization?
            </template>

            <template #footer>
                <jet-secondary-button @click.native="confirmingLeavingOrganization = false">
                    Nevermind
                </jet-secondary-button>

                <jet-danger-button class="ml-2" @click.native="leaveOrganization" :class="{ 'opacity-25': leaveOrganizationForm.processing }" :disabled="leaveOrganizationForm.processing">
                    Leave
                </jet-danger-button>
            </template>
        </jet-confirmation-modal>

        <!-- Remove Organization Member Confirmation Modal -->
        <jet-confirmation-modal :show="organizationMemberBeingRemoved" @close="organizationMemberBeingRemoved = null">
            <template #title>
                Remove Organization Member
            </template>

            <template #content>
                Are you sure you would like to remove this person from the organization?
            </template>

            <template #footer>
                <jet-secondary-button @click.native="organizationMemberBeingRemoved = null">
                    Nevermind
                </jet-secondary-button>

                <jet-danger-button class="ml-2" @click.native="removeOrganizationMember" :class="{ 'opacity-25': removeOrganizationMemberForm.processing }" :disabled="removeOrganizationMemberForm.processing">
                    Remove
                </jet-danger-button>
            </template>
        </jet-confirmation-modal>
    </div>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetActionSection from '@/Jetstream/ActionSection'
    import JetButton from '@/Jetstream/Button'
    import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
    import JetDangerButton from '@/Jetstream/DangerButton'
    import JetDialogModal from '@/Jetstream/DialogModal'
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import JetSecondaryButton from '@/Jetstream/SecondaryButton'
    import JetSectionBorder from '@/Jetstream/SectionBorder'

    export default {
        components: {
            JetActionMessage,
            JetActionSection,
            JetButton,
            JetConfirmationModal,
            JetDangerButton,
            JetDialogModal,
            JetFormSection,
            JetInput,
            JetInputError,
            JetLabel,
            JetSecondaryButton,
            JetSectionBorder,
        },

        props: [
            'organization',
            'availableRoles',
            'userPermissions'
        ],

        data() {
            return {
                addOrganizationMemberForm: this.$inertia.form({
                    email: '',
                    role: null,
                }),

                updateRoleForm: this.$inertia.form({
                    role: null,
                }),

                leaveOrganizationForm: this.$inertia.form(),
                removeOrganizationMemberForm: this.$inertia.form(),

                currentlyManagingRole: false,
                managingRoleFor: null,
                confirmingLeavingOrganization: false,
                organizationMemberBeingRemoved: null,
            }
        },

        methods: {
            addOrganizationMember() {
                this.addOrganizationMemberForm.post(route('organization-members.store', this.organization), {
                    errorBag: 'addOrganizationMember',
                    preserveScroll: true,
                    onSuccess: () => this.addOrganizationMemberForm.reset(),
                });
            },

            cancelOrganizationInvitation(invitation) {
                this.$inertia.delete(route('organization-invitations.destroy', invitation), {
                    preserveScroll: true
                });
            },

            manageRole(organizationMember) {
                this.managingRoleFor = organizationMember
                this.updateRoleForm.role = organizationMember.membership.role
                this.currentlyManagingRole = true
            },

            updateRole() {
                this.updateRoleForm.put(route('organization-members.update', [this.organization, this.managingRoleFor]), {
                    preserveScroll: true,
                    onSuccess: () => (this.currentlyManagingRole = false),
                })
            },

            confirmLeavingOrganization() {
                this.confirmingLeavingOrganization = true
            },

            leaveOrganization() {
                this.leaveOrganizationForm.delete(route('organization-members.destroy', [this.organization, this.$page.props.user]))
            },

            confirmOrganizationMemberRemoval(organizationMember) {
                this.organizationMemberBeingRemoved = organizationMember
            },

            removeOrganizationMember() {
                this.removeOrganizationMemberForm.delete(route('organization-members.destroy', [this.organization, this.organizationMemberBeingRemoved]), {
                    errorBag: 'removeOrganizationMember',
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => (this.organizationMemberBeingRemoved = null),
                })
            },

            displayableRole(role) {
                return this.availableRoles.find(r => r.key === role).name
            },
        },
    }
</script>
