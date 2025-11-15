<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

// Get user from Inertia props
const page = usePage();
const user = page.props.auth.user;

// Current active role
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
        onSuccess: () => {
            router.reload(); 
        },
    });
};

const stopSwitch = () => {
    router.post('/switch-role/stop', {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload(); 
        },
    });
};

</script>

<template>
    <!-- Hide completely if user is a normal member -->
    <div v-if="allowedRoles.length > 0" class="flex items-center gap-2">

        <!-- Current visible role -->
        <div
            class="px-3 py-1 text-sm rounded-lg bg-gray-100 border shadow-sm"
            v-if="activeRole !== realRole"
        >
            Acting as:
            <span class="font-semibold text-blue-600">{{ activeRole }}</span>
        </div>

        <div
            class="px-3 py-1 text-sm rounded-lg bg-gray-100 border shadow-sm"
            v-else
        >
            Role:
            <span class="font-semibold text-gray-700">{{ activeRole }}</span>
        </div>

        <!-- Dropdown -->
        <select
            v-if="allowedRoles.length > 1"
            class="px-2 py-1 border rounded-md text-sm"
            @change="switchRole($event.target.value)"
        >
            <option disabled selected>Switch Role</option>
            <option
                v-for="r in allowedRoles"
                :key="r"
                :value="r"
            >
                {{ r }}
            </option>
        </select>

        <!-- If only one possible switch target (e.g., accountant → member) -->
        <button
            v-else
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md"
            @click="switchRole(allowedRoles[0])"
        >
            Switch to {{ allowedRoles[0] }}
        </button>

        <!-- Stop Switch -->
        <button
            v-if="activeRole !== realRole"
            class="px-3 py-1 text-sm bg-red-600 text-white rounded-md"
            @click="stopSwitch"
        >
            Stop
        </button>
    </div>
</template>

<style scoped>
select {
    cursor: pointer;
}
</style>
