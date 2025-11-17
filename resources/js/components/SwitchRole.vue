<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

// Get user from Inertia props
const page = usePage();
const user = page.props.auth.user;

// Current active role (shortened wording)
const activeRole = computed(() => user?.active_role ?? user?.role);

// Real role (from DB)
const realRole = user?.role;

// Determine allowed target roles dynamically
const allowedRoles = computed(() => {
    if (!realRole) return [];

    const map = {
        admin: ['admin', 'member'],
        management: ['management', 'member'],
        loan_officer: ['loan_officer', 'member'],
        accountant: ['accountant', 'member'],
        member: [], // Members cannot switch
    };

    return map[realRole] ?? [];
});

const switchRole = (role: string) => {
    router.post('/switch-role', { role }, {
        preserveScroll: true,
        onSuccess: () => router.reload(),
    });
};

const stopSwitch = () => {
    router.post('/switch-role/stop', {}, {
        preserveScroll: true,
        onSuccess: () => router.reload(),
    });
};
</script>

<template>
    <!-- Hide completely if user is a normal member -->
    <div v-if="allowedRoles.length > 0" class="flex max:flex-col items-center gap-2">

        <!-- Current visible role label  -->
        <div
            class="px-3 py-1 text-sm rounded-lg text-blue-900 bg-gray-100 dark:bg-gray-800 dark:text-gray dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-sm"
            v-if="activeRole !== realRole"
        >
            Active:
            <span class="font-semibold text-blue-900 dark:text-gray-200">{{ activeRole }}</span>
        </div>

        <div
            class="px-3 py-1 text-sm rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-sm"
            v-else
        >
            {{ activeRole }}
        </div>

        <!-- Dropdown (shortened wording, removed 'Role') -->
        <select
            v-if="allowedRoles.length > 1"
            class="px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
            @change="switchRole($event.target.value)"
        >
            <option disabled selected>Switch</option>
            <option v-for="r in allowedRoles" :key="r" :value="r">
                {{ r }}
            </option>
        </select>

        <!-- Single target switch button -->
        <button
            v-else
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md"
            @click="switchRole(allowedRoles[0])"
        >
            Switch to {{ allowedRoles[0] }}
        </button>
    </div>
</template>

<style scoped>
select {
    cursor: pointer;
}
</style>
