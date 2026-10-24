<template>
    <AppLayout
        :breadcrumbs="[{ title: 'Members', href: route('members.index') }, { title: `${member.first_name} ${member.last_name} - Next of Kin` }]"
    >
        <!-- Flash Messages -->
        <div ref="flashBox" class="mx-auto mt-4 max-w-3xl px-4">
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="flashMessage"
                    :class="[
                        flashType === 'success'
                            ? 'border border-green-200 bg-green-50 text-green-700'
                            : 'border border-red-200 bg-red-50 text-red-700',
                        'mb-4 flex items-center rounded-xl p-4 shadow-sm',
                    ]"
                >
                    <component
                        :is="flashType === 'success' ? CheckCircle : AlertCircle"
                        class="h-5 w-5"
                        :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'"
                    />
                    <div class="flex items-center gap-2">
                        <p class="ml-3 text-sm">{{ flashMessage }}</p>
                        <button @click="flashMessage = null" class="ml-auto text-gray-500 hover:text-gray-700">x</button>
                    </div>
                </div>
            </transition>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('members.show', member.id)" class="mr-4">
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <div>
                    <h2 class="text-xl leading-tight font-semibold text-gray-800">{{ member.first_name }} {{ member.last_name }} - Next of Kin</h2>
                    <p class="text-sm text-gray-600">Member ID: {{ member.membership_id }}</p>
                </div>
            </div>
            <div class="flex space-x-2" v-if="canManage">
                <button
                    @click="showAddModal = true"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-indigo-700"
                >
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Add Next of Kin
                </button>
            </div>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Member Info Card -->
                <div class="mb-6 rounded-lg bg-white shadow">
                    <div class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-12 w-12 flex-shrink-0">
                                <img
                                    v-if="member.profile_photo"
                                    :src="`/storage/${member.profile_photo}`"
                                    :alt="member.first_name"
                                    class="h-12 w-12 rounded-full object-cover"
                                />
                                <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-300">
                                    <UserIcon class="h-8 w-8 text-gray-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ member.first_name }} {{ member.last_name }}</h3>
                                <p class="text-sm text-gray-500">{{ member.membership_id }}</p>
                            </div>
                            <div class="ml-auto">
                                <span
                                    :class="{
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold': true,
                                        'bg-green-100 text-green-800': member.membership_status === 'active',
                                        'bg-red-100 text-red-800': member.membership_status === 'inactive',
                                        'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended',
                                    }"
                                >
                                    {{ member.membership_status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next of Kin List -->
                <div class="space-y-6" v-if="nextOfKin.length > 0">
                    <div v-for="(kin, index) in nextOfKin" :key="kin.id" class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                                            <UsersIcon class="h-6 w-6 text-indigo-600" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">{{ kin.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ kin.relationship }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2" v-if="canManage">
                                    <button @click="editNextOfKin(kin)" class="text-indigo-600 hover:text-indigo-500">
                                        <PencilIcon class="h-5 w-5" />
                                    </button>
                                    <button @click="deleteNextOfKin(kin)" class="text-red-600 hover:text-red-500">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ kin.phone || 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ kin.email || 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ID Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ kin.id_number || 'Not provided' }}</dd>
                                </div>
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ kin.address || 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ kin.date_of_birth ? formatDate(kin.date_of_birth) : 'Not provided' }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-12 text-center">
                    <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No next of kin</h3>
                    <p class="mt-1 text-sm text-gray-500">No next of kin information has been added for this member.</p>
                    <div class="mt-6" v-if="canManage">
                        <button
                            @click="showAddModal = true"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                        >
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Next of Kin
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Next of Kin Modal -->
        <div
            v-if="showAddModal || showEditModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity" aria-hidden="true" @click="closeModals"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    class="relative inline-block transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 sm:align-middle"
                >
                    <form @submit.prevent="submitForm">
                        <div>
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ editingKin ? 'Edit Next of Kin' : 'Add Next of Kin' }}
                                </h3>
                                <div class="mt-6 space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> Full Name * </label>
                                        <input
                                            v-model="kinForm.name"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.name }"
                                        />
                                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                    </div>

                                    <!-- Relationship -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> Relationship * </label>
                                        <select
                                            v-model="kinForm.relationship"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.relationship }"
                                        >
                                            <option value="">Select Relationship</option>
                                            <option value="spouse">Spouse</option>
                                            <option value="parent">Parent</option>
                                            <option value="child">Child</option>
                                            <option value="sibling">Sibling</option>
                                            <option value="relative">Relative</option>
                                            <option value="friend">Friend</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <p v-if="errors.relationship" class="mt-1 text-sm text-red-600">{{ errors.relationship }}</p>
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> Phone Number </label>
                                        <input
                                            v-model="kinForm.phone"
                                            type="tel"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.phone }"
                                        />
                                        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> Email Address </label>
                                        <input
                                            v-model="kinForm.email"
                                            type="email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.email }"
                                        />
                                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                                    </div>

                                    <!-- ID Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> ID Number </label>
                                        <input
                                            v-model="kinForm.id_number"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.id_number }"
                                        />
                                        <p v-if="errors.id_number" class="mt-1 text-sm text-red-600">{{ errors.id_number }}</p>
                                    </div>

                                    <!-- Date of Birth -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> Date of Birth </label>
                                        <input
                                            v-model="kinForm.date_of_birth"
                                            type="date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.date_of_birth }"
                                        />
                                        <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ errors.date_of_birth }}</p>
                                    </div>

                                    <!-- Address -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"> Address </label>
                                        <textarea
                                            v-model="kinForm.address"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.address }"
                                        ></textarea>
                                        <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ processing ? 'Saving...' : editingKin ? 'Update' : 'Add' }}
                            </button>
                            <button
                                type="button"
                                @click="closeModals"
                                class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, PencilIcon, PlusIcon, TrashIcon, UserIcon, UsersIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
const page = usePage();

export default {
    components: {
        AppLayout,
        Head,
        Link,
        ArrowLeftIcon,
        PlusIcon,
        UserIcon,
        UsersIcon,
        PencilIcon,
        TrashIcon,
    },
    props: {
        member: Object,
        nextOfKin: Array,
        errors: Object,
    },
    setup(props) {
        const showAddModal = ref(false);
        const showEditModal = ref(false);
        const editingKin = ref(null);
        const processing = ref(false);

        const kinForm = reactive({
            name: '',
            relationship: '',
            phone: '',
            email: '',
            id_number: '',
            date_of_birth: '',
            address: '',
        });
        // Flash handling
        const page = usePage();
        const flash = computed(() => page.props.flash || {});
        const flashMessage = ref(null);
        const flashType = ref('success');
        const flashBox = ref(null);
        const isLoading = ref(false);

        watch(
            flash,
            (val) => {
                if (val.success) {
                    flashMessage.value = val.success;
                    flashType.value = 'success';
                } else if (val.error) {
                    flashMessage.value = val.error;
                    flashType.value = 'error';
                }

                if (flashMessage.value) {
                    // Scroll to top of page to ensure flash is visible
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    // Optional: also ensure the flash container itself is in view
                    flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });

                    // Auto-hide after 3s
                    setTimeout(() => (flashMessage.value = null), 3000);
                }
            },
            { immediate: true, deep: true },
        );

        const canManage = computed(() => {
            const role = page.props.auth?.user?.role;
            return ['admin', 'management', 'loan_officer'].includes(role);
        });

        const resetForm = () => {
            kinForm.name = '';
            kinForm.relationship = '';
            kinForm.phone = '';
            kinForm.email = '';
            kinForm.id_number = '';
            kinForm.date_of_birth = '';
            kinForm.address = '';
        };

        const editNextOfKin = (kin) => {
            editingKin.value = kin;
            kinForm.name = kin.name || '';
            kinForm.relationship = kin.relationship || '';
            kinForm.phone = kin.phone || '';
            kinForm.email = kin.email || '';
            kinForm.id_number = kin.id_number || '';
            kinForm.date_of_birth = kin.date_of_birth || '';
            kinForm.address = kin.address || '';
            showEditModal.value = true;
        };

        const deleteNextOfKin = (kin) => {
            if (confirm('Are you sure you want to delete this next of kin?')) {
                router.delete(route('members.destroy-next-of-kin', [props.member.id, kin.id]), {
                    preserveScroll: true,
                });
            }
        };

        const closeModals = () => {
            showAddModal.value = false;
            showEditModal.value = false;
            editingKin.value = null;
            resetForm();
        };

        const submitForm = () => {
            processing.value = true;

            if (editingKin.value) {
                // Update existing
                router.put(route('members.update-next-of-kin', [props.member.id, editingKin.value.id]), kinForm, {
                    preserveScroll: true,
                    onSuccess: () => {
                        closeModals();
                    },
                    onFinish: () => {
                        processing.value = false;
                    },
                });
            } else {
                // Create new
                router.post(route('members.store-next-of-kin', props.member.id), kinForm, {
                    preserveScroll: true,
                    onSuccess: () => {
                        closeModals();
                    },
                    onFinish: () => {
                        processing.value = false;
                    },
                });
            }
        };

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString();
        };

        return {
            showAddModal,
            showEditModal,
            editingKin,
            processing,
            kinForm,
            canManage,
            editNextOfKin,
            deleteNextOfKin,
            closeModals,
            submitForm,
            formatDate,
            flashMessage,
            flashType,
            flashBox,
        };
    },
};
</script>
