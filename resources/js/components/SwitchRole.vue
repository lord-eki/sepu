<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = page.props.auth.user;

const activeRole = computed(() => user?.active_role ?? user?.role);

const realRole = user?.role;

const allowedRoles = computed(() => {
    if (!realRole) return [];

    const map = {
        admin: ['admin', 'member'],
        management: ['management', 'member'],
        loan_officer: ['loan_officer', 'member'],
        accountant: ['accountant', 'member'],
        member: [], 
    };

    return map[realRole] ?? [];
});

const switchRole = (role: string) => {
    router.post(
        '/switch-role',
        { role },
        {
            preserveScroll: true,
            onSuccess: () => router.reload(),
        },
    );
};

</script>

<template>
    <div v-if="allowedRoles.length > 0" class="max:flex-col flex items-center gap-2">
        <div
            class="dark:text-gray rounded-lg border border-gray-300 bg-gray-100 px-3 py-1 text-sm text-blue-900 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            v-if="activeRole !== realRole"
        >
            Active:
            <span class="font-semibold text-blue-900 dark:text-gray-200">{{ activeRole }}</span>
        </div>

        <div class="rounded-lg border border-gray-300 bg-gray-100 px-3 py-1 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" v-else>
            {{ activeRole }}
        </div>

        <select
            v-if="allowedRoles.length > 1"
            class="rounded-md border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            @change="switchRole($event.target.value)"
        >
            <option disabled selected>Switch</option>
            <option v-for="r in allowedRoles" :key="r" :value="r">
                {{ r }}
            </option>
        </select>

        <button v-else class="rounded-md bg-blue-600 px-3 py-1 text-sm text-white" @click="switchRole(allowedRoles[0])">
            Switch to {{ allowedRoles[0] }}
        </button>
    </div>
</template>

<style scoped>
select {
    cursor: pointer;
}
</style>
