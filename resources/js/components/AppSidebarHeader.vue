<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';

withDefaults(
    defineProps<{ breadcrumbs?: BreadcrumbItemType[] }>(),
    { breadcrumbs: () => [] },
);

const roleSwitchRoute = route('switch-role');
const loading = ref(false);

const submitForm = async (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const role = select.value;
    loading.value = true;

    try {
        await fetch(roleSwitchRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
            },
            body: JSON.stringify({ role }),
        });

        await router.reload({ only: ['auth'] });
    } catch (error) {
        console.error('Role switch failed:', error);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <header class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 md:px-4">
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Role Switcher -->
        <div v-if="$page.props.auth.roles.length > 1" class="ml-auto flex items-center space-x-2">
            <span class="mr-2 text-sm">Logged in as:</span>

            <select
                name="role"
                class="bg-blue-900 text-white px-2 py-1 rounded disabled:opacity-70"
                @change="submitForm($event)"
                :disabled="loading"
            >
                <option
                    v-for="role in $page.props.auth.roles"
                    :key="role"
                    :value="role"
                    :selected="role === $page.props.auth.current_role"
                >
                    {{ role.charAt(0).toUpperCase() + role.slice(1).replace('_', ' ') }}
                </option>
            </select>

            <div
                v-if="loading"
                class="ml-2 animate-spin rounded-full h-5 w-5 border-b-2 border-orange-500"
            ></div>
        </div>
    </header>
</template>
