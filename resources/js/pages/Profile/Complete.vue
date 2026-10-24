<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-blue-900 shadow-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
                <h2 class="text-xl font-bold tracking-tight text-white sm:text-2xl">Complete Your Profile</h2>
                <Link
                    :href="route('logout')"
                    method="post"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white shadow transition hover:bg-orange-600"
                >
                    Logout
                </Link>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="mx-auto mt-4 max-w-2xl px-4">
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
                            ? 'border border-green-300 bg-green-100 text-green-800'
                            : 'border border-red-300 bg-red-100 text-red-800',
                        'relative mb-4 flex w-full items-center rounded-lg px-6 py-3 shadow-sm',
                    ]"
                >
                    <span class="flex-1">{{ flashMessage }}</span>
                    <button type="button" class="ml-3 text-gray-500 hover:text-gray-700" @click="flashMessage = null">✕</button>
                </div>
            </transition>
        </div>

        <!-- Content -->
        <main class="py-8 max-lg:px-4">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="formprofile space-y-8">
                    <!-- Intro -->
                    <div class="rounded-xl border-l-4 border-orange-500 bg-white p-6 shadow-md">
                        <h3 class="mb-1 text-lg font-semibold text-[#081642]">Welcome</h3>
                        <p class="text-sm text-gray-500">Please fill in the required fields to complete your membership profile.</p>
                    </div>

                    <!-- Profile Photo -->
                    <section class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="text-lg font-semibold text-[#081642]">Profile Photo</h3>
                        <p class="mb-4 text-sm text-gray-500">Upload a clear passport-style photo.</p>
                        <div class="flex items-center gap-6">
                            <div class="flex-shrink-0">
                                <img
                                    v-if="photoPreview"
                                    :src="photoPreview"
                                    alt="Profile preview"
                                    class="h-20 w-20 rounded-full object-cover ring-2 ring-orange-500"
                                />
                                <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-200">
                                    <User class="h-10 w-10 text-gray-400" />
                                </div>
                            </div>
                            <input type="file" ref="photoInput" accept="image/*" class="hidden" @change="handlePhotoUpload" />
                            <button
                                type="button"
                                @click="$refs.photoInput.click()"
                                class="rounded-md border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-medium text-orange-700 shadow-sm hover:bg-orange-100"
                            >
                                Change Photo
                            </button>
                        </div>
                    </section>

                    <!-- Personal Information -->
                    <section class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="text-lg font-semibold text-[#081642]">Personal Information</h3>
                        <p class="mb-6 text-sm text-gray-500">Your basic details.</p>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- First Name -->
                            <div>
                                <label class="block text-sm font-medium">First Name</label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 p-2 shadow-sm sm:text-sm"
                                />
                            </div>
                            <!-- Last Name -->
                            <div>
                                <label class="block text-sm font-medium">Last Name</label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 p-2 shadow-sm sm:text-sm"
                                />
                            </div>
                            <!-- Other Names -->
                            <div>
                                <label class="block text-sm font-medium">Other Names</label>
                                <input
                                    v-model="form.middle_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <!-- DOB -->
                            <div>
                                <label class="block text-sm font-medium">Date of Birth</label>
                                <input
                                    v-model="form.date_of_birth"
                                    type="date"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <!-- Gender -->
                            <div>
                                <label class="block text-sm font-medium">Gender</label>
                                <select
                                    v-model="form.gender"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                >
                                    <option value="">Select Gender</option>
                                    <option v-for="(label, value) in genders" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <!-- Marital -->
                            <div>
                                <label class="block text-sm font-medium">Marital Status</label>
                                <select
                                    v-model="form.marital_status"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                >
                                    <option value="">Select Status</option>
                                    <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Contact Information -->
                    <section class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="text-lg font-semibold text-[#081642]">Contact Information</h3>
                        <p class="mb-6 text-sm text-gray-500">How we can reach you.</p>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    readonly
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 p-2 shadow-sm sm:text-sm"
                                />
                            </div>
                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 p-2 shadow-sm sm:text-sm"
                                />
                            </div>
                            <!-- Physical Address -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium">Physical Address</label>
                                <input
                                    v-model="form.physical_address"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <!-- City -->
                            <div>
                                <label class="block text-sm font-medium">City</label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <!-- County -->
                            <div>
                                <label class="block text-sm font-medium">County</label>
                                <input
                                    v-model="form.county"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <!-- Postal Address -->
                            <div>
                                <label class="block text-sm font-medium">Postal Address</label>
                                <input
                                    v-model="form.postal_address"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Identification -->
                    <section class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="text-lg font-semibold text-[#081642]">Identification</h3>
                        <p class="mb-6 text-sm text-gray-500">Provide a valid ID document.</p>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium">ID Type</label>
                                <select
                                    v-model="form.id_type"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                >
                                    <option value="">Select ID Type</option>
                                    <option v-for="(label, value) in idTypes" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">ID Number</label>
                                <input
                                    v-model="form.id_number"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Documents -->
                    <section class="rounded-xl bg-white p-6 shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-[#081642]">Supporting Documents</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Please upload the following required documents as <strong>one combined PDF</strong>:
                                </p>

                                <div class="mt-4 rounded-lg border border-orange-200 bg-orange-50 p-4">
                                    <h4 class="mb-2 font-medium text-orange-800">Required Documents</h4>

                                    <ul class="list-inside list-disc space-y-1 text-sm text-gray-700">
                                        <li>National ID (Front and Back)</li>
                                        <li>Recent Passport-size Photo</li>
                                        <li>KRA PIN Certificate</li>
                                        <li>Any other relevant supporting documents (if applicable)</li>
                                    </ul>

                                    <p class="mt-3 text-xs text-orange-700">
                                        <strong>Note:</strong> Scan or combine all the above documents into a <strong>single PDF file</strong> before
                                        uploading. Maximum file size: <strong>10 MB</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <input ref="documentsInput" type="file" accept=".pdf" class="hidden" @change="handleDocumentsUpload" />

                            <button
                                type="button"
                                @click="$refs.documentsInput.click()"
                                class="inline-flex items-center rounded-lg border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-medium text-orange-700 shadow-sm transition hover:bg-orange-100"
                            >
                                <FileIcon class="mr-2 h-4 w-4" />
                                Upload Combined PDF
                            </button>
                        </div>

                        <ul v-if="selectedDocuments.length" class="mt-5 space-y-3">
                            <li
                                v-for="(doc, index) in selectedDocuments"
                                :key="index"
                                class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <FileIcon class="h-5 w-5 text-red-500" />

                                    <div>
                                        <p class="font-medium text-gray-800">
                                            {{ doc.name }}
                                        </p>

                                        <p class="text-xs text-gray-500">PDF Document</p>
                                    </div>
                                </div>

                                <button type="button" @click="removeDocument(index)" class="text-sm font-medium text-red-600 hover:text-red-800">
                                    Remove
                                </button>
                            </li>
                        </ul>
                    </section>

                    <!-- Emergency Contact -->
                    <section class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="text-lg font-semibold text-[#081642]">Emergency Contact</h3>
                        <p class="mb-6 text-sm text-gray-500">Who we should reach in case of emergency.</p>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium">Name</label>
                                <input
                                    v-model="form.emergency_contact_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Phone</label>
                                <input
                                    v-model="form.emergency_contact_phone"
                                    type="tel"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium">Relationship</label>
                                <input
                                    v-model="form.emergency_contact_relationship"
                                    type="text"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Submit -->
                    <div class="flex justify-end max-sm:mr-5">
                        <button
                            type="submit"
                            :disabled="form.processing || !isFormValid"
                            class="inline-flex items-center rounded-lg bg-blue-900 px-6 py-3 font-medium text-white shadow-md transition hover:bg-orange-600 disabled:bg-gray-300"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Complete Profile</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { FileIcon, User } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash || {});
const flashMessage = ref(null);
const flashType = ref('success');
const selectedPaymentMethod = ref('');
const confirmPayment = () => {
    showPaymentModal.value = false;
    alert('Payment confirmation received. Admin will verify your payment.');
};

watch(flash, (val) => {
    if (val.success) {
        flashMessage.value = val.success;
        flashType.value = 'success';
        form.reset();
        photoPreview.value = null;
        selectedDocuments.value = [];
    } else if (val.error) {
        flashMessage.value = val.error;
        flashType.value = 'error';
        form.reset();
        photoPreview.value = null;
        selectedDocuments.value = [];
    }
    if (flashMessage.value) {
        setTimeout(() => (flashMessage.value = null), 5000);
    }
});

const props = defineProps({
    user: Object,
    idTypes: Object,
    genders: Object,
    maritalStatuses: Object,
    errors: Object,
});

const firstName = props.user?.name ? props.user.name.split(' ')[0] : '';
const lastName = props.user?.name ? (props.user.name.split(' ')[1] ?? '') : '';

const isFormValid = computed(() => {
    return (
        form.first_name &&
        form.last_name &&
        form.date_of_birth &&
        form.gender &&
        form.marital_status &&
        form.physical_address &&
        form.id_type &&
        form.id_number &&
        form.occupation &&
        form.employer &&
        form.monthly_income &&
        form.emergency_contact_name &&
        form.emergency_contact_phone &&
        form.emergency_contact_relationship
    );
});

const form = useForm({
    first_name: firstName,
    last_name: lastName,
    middle_name: '',
    date_of_birth: '',
    gender: '',
    marital_status: '',
    email: props.user?.email ?? '',
    phone: props.user?.phone ?? '',
    physical_address: '',
    city: '',
    county: '',
    postal_address: '',
    id_type: '',
    id_number: '',
    occupation: '',
    employer: '',
    monthly_income: '',
    profile_photo: null,
    documents: [],
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: '',
});

const photoPreview = ref(null);
const handlePhotoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        photoPreview.value = URL.createObjectURL(file);
        form.profile_photo = file;
    }
};

const selectedDocuments = ref([]);
const handleDocumentsUpload = (e) => {
    selectedDocuments.value = Array.from(e.target.files);
    form.documents = selectedDocuments.value;
};
const removeDocument = (index) => {
    selectedDocuments.value.splice(index, 1);
    form.documents = selectedDocuments.value;
};

const submit = () => {
    form.post(route('profile.complete.store'), {
        forceFormData: true,
    });
};
</script>

<style>
.formprofile label {
    color: #081642ff;
}

button:hover {
    cursor: pointer;
}
</style>
