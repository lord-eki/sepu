<template>
    <AppLayout :breadcrumbs="[{ title: 'System Users', href: route('system-users.index') }, { title: 'Add User' }]">
        <Head title="Add System User" />

        <!-- CENTER TOAST -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-90"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <div v-if="toast.visible" class="fixed inset-0 z-50 flex items-start justify-center pt-20">
                <div
                    :class="toast.type === 'success' ? 'bg-emerald-600/90' : 'bg-rose-600/90'"
                    class="w-full max-w-md rounded-2xl px-6 py-4 text-center text-white shadow-xl backdrop-blur-xl"
                >
                    <p class="text-lg font-semibold">
                        {{ toast.message }}
                    </p>
                </div>
            </div>
        </transition>

        <!-- HEADER -->
        <div class="mx-6 mt-6 mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-[#0B1F3A] dark:text-white">Add System User</h1>

            <Link
                :href="route('system-users.index')"
                class="rounded-xl bg-[#0B1F3A] px-4 py-2 text-white transition hover:bg-blue-900 dark:bg-gray-700 dark:hover:bg-gray-600"
            >
                Back
            </Link>
        </div>

        <!-- FORM CARD -->
        <div class="mx-auto max-w-3xl rounded-2xl border border-gray-100 bg-white p-8 shadow-lg dark:border-gray-700 dark:bg-[#0B1F3A]">
            <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- MEMBER SELECT -->
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300"> Select Member </label>

                    <Combobox v-model="form.user_id">
                        <div class="relative">
                            <ComboboxInput
                                class="w-full rounded-xl border border-gray-300 p-2 px-4 py-2 text-gray-900 focus:border-orange-500 focus:ring-orange-500 dark:border-gray-600 dark:bg-[#14294B] dark:text-white"
                                :displayValue="displayMember"
                                placeholder="Search member by name or ID"
                                @change="query = $event.target.value"
                            />

                            <ComboboxOptions
                                class="absolute z-10 mt-2 max-h-60 w-full overflow-auto rounded-xl border bg-white shadow-lg dark:border-gray-700 dark:bg-[#14294B]"
                            >
                                <ComboboxOption
                                    v-for="member in filteredMembers"
                                    :key="member.user_id"
                                    :value="member.user_id"
                                    class="cursor-pointer px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    {{ member.full_name }} — {{ member.membership_id }}
                                </ComboboxOption>

                                <div v-if="!filteredMembers.length" class="px-4 py-2 text-sm text-gray-500">No member found</div>
                            </ComboboxOptions>
                        </div>
                    </Combobox>

                    <p v-if="form.errors.user_id" class="mt-1 text-sm text-rose-500">
                        {{ form.errors.user_id }}
                    </p>
                </div>

                <!-- READONLY INFO -->
                <div>
                    <label class="label mr-4 font-bold text-slate-800">Full Name</label>
                    <input
                        :value="selectedMember?.full_name || ''"
                        readonly
                        class="input-readonly w-full rounded-xl border bg-gray-100 p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-100"
                    />
                </div>

                <div>
                    <label class="label mr-4 font-bold text-slate-800">Email</label>
                    <input
                        :value="selectedMember?.email || ''"
                        readonly
                        class="input-readonly w-full rounded-xl border bg-gray-100 p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-100"
                    />
                </div>

                <div>
                    <label class="label mr-4 font-bold text-slate-800">Phone Number</label>
                    <input
                        :value="selectedMember?.phone || ''"
                        readonly
                        class="input-readonly w-full rounded-xl border bg-gray-100 p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-100"
                    />
                </div>

                <!-- ROLE -->
                <div>
                    <label class="label mr-4 font-bold text-slate-800">User Role</label>
                    <select
                        v-model="form.role"
                        class="input w-full rounded-xl border bg-white p-3 text-gray-900 focus:border-orange-500 focus:ring-orange-500 dark:bg-[#14294B] dark:text-white"
                    >
                        <option disabled value="">Select role</option>
                        <option v-for="(label, key) in roles" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>

                    <p v-if="form.errors.role" class="mt-1 text-sm text-rose-500">
                        {{ form.errors.role }}
                    </p>
                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end pt-4 md:col-span-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-orange-600 px-6 py-2 font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Combobox, ComboboxInput, ComboboxOption, ComboboxOptions } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    roles: Object,
    members: Array,
});

const page = usePage();

/* FORM */
const form = useForm({
    user_id: '',
    role: '',
    is_active: true,
});

/* SELECTED MEMBER */
const selectedMember = computed(() => props.members.find((m) => m.user_id === form.user_id));

/* COMBOBOX */
const query = ref('');

const filteredMembers = computed(() => {
    if (!query.value) return props.members;
    return props.members.filter(
        (m) => m.full_name.toLowerCase().includes(query.value.toLowerCase()) || m.membership_id.toLowerCase().includes(query.value.toLowerCase()),
    );
});

const displayMember = (id) => {
    const member = props.members.find((m) => m.user_id === id);
    return member ? `${member.full_name} — ${member.membership_id}` : '';
};

/* TOAST */
const toast = ref({
    visible: false,
    message: '',
    type: 'success',
});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success || flash.error) {
            toast.value = {
                visible: true,
                message: flash.success || flash.error,
                type: flash.success ? 'success' : 'error',
            };

            setTimeout(() => {
                toast.value.visible = false;
            }, 4000);
        }
    },
    { deep: true },
);

/* SUBMIT */
const submit = () => {
    form.post(route('system-users.store'));
};
</script>

<style scoped>
.role-form input,
.role-form select {
    border: 1px solid gray;
    padding: 10px;
}
</style>
