<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    settings: Record<string, any>;
}>();

const page = usePage();
const flash = computed(() => page.props.flash || {});
const flashMessage = ref(null);
const flashType = ref('success');
const flashBox = ref(null);

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
            window.scrollTo({ top: 0, behavior: 'smooth' });
            flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => (flashMessage.value = null), 3000);
        }
    },
    { immediate: true, deep: true },
);

/**
 * SystemSetting → frontend form fields
 */
const form = useForm({
    sacco_name: props.settings.sacco_name?.value ?? '',
    sacco_email: props.settings.sacco_email?.value ?? '',
    sacco_phone: props.settings.sacco_phone?.value ?? '',
    sacco_address: props.settings.sacco_address?.value ?? '',
    sacco_registration_number: props.settings.sacco_registration_number?.value ?? '',
    currency: props.settings.currency?.value ?? 'KES',
});

/**
 * Global error checker
 */
const hasErrors = computed(() => Object.keys(form.errors).length > 0);

/**
 * Submit form
 */
function saveSettings() {
    form.post(route('admin.settings.update-general'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'General' }]">
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
                            ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300'
                            : 'border-red-200 bg-red-50 text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300',
                        'mb-4 flex items-center rounded-xl border p-4 shadow-sm',
                    ]"
                >
                    <component
                        :is="flashType === 'success' ? CheckCircle : AlertCircle"
                        class="h-5 w-5"
                        :class="flashType === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                    />
                    <div class="flex items-center gap-2">
                        <p class="ml-3 text-sm">{{ flashMessage }}</p>
                        <button
                            @click="flashMessage = null"
                            class="ml-auto text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            x
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <Head title="General Settings" />

        <div class="space-y-6 rounded-2xl bg-white p-6 shadow-md">
            <!-- HEADER -->
            <div>
                <h1 class="text-xl font-semibold text-gray-800 sm:text-2xl">General Settings</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your SACCO profile and system identity</p>
            </div>

            <!-- GLOBAL ERROR -->
            <transition
                enter-active-class="transition duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
            >
                <div v-if="hasErrors" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Please correct the highlighted fields before saving.
                </div>
            </transition>

            <!-- FORM -->
            <form @submit.prevent="saveSettings" class="space-y-5">
                <!-- SACCO NAME -->
                <div>
                    <label class="block text-sm font-medium text-gray-700"> Organization Name </label>

                    <input
                        v-model="form.sacco_name"
                        type="text"
                        placeholder="e.g., SEPU SACCO Society"
                        :class="[
                            'mt-1 w-full rounded-lg border px-4 py-2 transition outline-none',
                            form.errors.sacco_name
                                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                                : 'border-gray-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400',
                        ]"
                    />

                    <p v-if="form.errors.sacco_name" class="mt-1 text-sm text-red-500">
                        {{ form.errors.sacco_name }}
                    </p>
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700"> Email Address </label>

                    <input
                        v-model="form.sacco_email"
                        type="email"
                        placeholder="e.g., info@sacco.co.ke"
                        :class="[
                            'mt-1 w-full rounded-lg border px-4 py-2 transition outline-none',
                            form.errors.sacco_email
                                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                                : 'border-gray-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400',
                        ]"
                    />

                    <p v-if="form.errors.sacco_email" class="mt-1 text-sm text-red-500">
                        {{ form.errors.sacco_email }}
                    </p>
                </div>

                <!-- PHONE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700"> Phone Number </label>

                    <input
                        v-model="form.sacco_phone"
                        type="text"
                        placeholder="e.g., +254 700 000 000"
                        :class="[
                            'mt-1 w-full rounded-lg border px-4 py-2 transition outline-none',
                            form.errors.sacco_phone
                                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                                : 'border-gray-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400',
                        ]"
                    />

                    <p v-if="form.errors.sacco_phone" class="mt-1 text-sm text-red-500">
                        {{ form.errors.sacco_phone }}
                    </p>
                </div>

                <!-- ADDRESS -->
                <div>
                    <label class="block text-sm font-medium text-gray-700"> Physical Address </label>

                    <textarea
                        v-model="form.sacco_address"
                        rows="3"
                        placeholder="e.g., Nairobi CBD, Kenya"
                        :class="[
                            'mt-1 w-full rounded-lg border px-4 py-2 transition outline-none',
                            form.errors.sacco_address
                                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                                : 'border-gray-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400',
                        ]"
                    ></textarea>

                    <p v-if="form.errors.sacco_address" class="mt-1 text-sm text-red-500">
                        {{ form.errors.sacco_address }}
                    </p>
                </div>

                <!-- REGISTRATION NUMBER -->
                <div>
                    <label class="block text-sm font-medium text-gray-700"> Registration Number </label>

                    <input
                        v-model="form.sacco_registration_number"
                        type="text"
                        placeholder="e.g., SACCO/REG/2025/001"
                        :class="[
                            'mt-1 w-full rounded-lg border px-4 py-2 transition outline-none',
                            form.errors.sacco_registration_number
                                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                                : 'border-gray-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400',
                        ]"
                    />

                    <p v-if="form.errors.sacco_registration_number" class="mt-1 text-sm text-red-500">
                        {{ form.errors.sacco_registration_number }}
                    </p>
                </div>

                <!-- CURRENCY -->
                <div>
                    <label class="block text-sm font-medium text-gray-700"> Default Currency </label>

                    <select
                        v-model="form.currency"
                        :class="[
                            'mt-1 w-full rounded-lg border px-4 py-2 transition outline-none',
                            form.errors.currency
                                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                                : 'border-gray-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400',
                        ]"
                    >
                        <option value="KES">KES (Kenyan Shilling)</option>
                        <option value="USD">USD (US Dollar)</option>
                        <option value="EUR">EUR (Euro)</option>
                    </select>

                    <p v-if="form.errors.currency" class="mt-1 text-sm text-red-500">
                        {{ form.errors.currency }}
                    </p>
                </div>

                <!-- ACTION -->
                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-orange-500 px-6 py-2 text-white shadow-md transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
