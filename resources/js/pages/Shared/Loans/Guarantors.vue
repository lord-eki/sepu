<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    guarantees: Object, // paginator object
    isAdmin: Boolean,
});

const activeTab = ref('all');
const search = ref('');

/**
 * Extract real data from Laravel paginator
 */
const baseList = computed(() => props.guarantees?.data || []);

/**
 * Filtered + searched list
 */
const filtered = computed(() => {
    let list = baseList.value;

    // status filter
    if (activeTab.value !== 'all') {
        list = list.filter((g) => g.status === activeTab.value);
    }

    // search filter
    if (search.value.trim()) {
        const s = search.value.toLowerCase();

        list = list.filter(
            (g) =>
                g.loan?.loan_number?.toLowerCase().includes(s) ||
                g.loan?.member?.first_name?.toLowerCase().includes(s) ||
                g.loan?.member?.last_name?.toLowerCase().includes(s) ||
                g.guarantorMember?.first_name?.toLowerCase().includes(s) ||
                g.guarantorMember?.last_name?.toLowerCase().includes(s),
        );
    }

    return list;
});

const tabs = [
    { key: 'all', label: 'All', color: 'bg-slate-100 text-slate-700' },
    { key: 'pending', label: 'Pending', color: 'bg-amber-100 text-amber-700' },
    { key: 'accepted', label: 'Accepted', color: 'bg-emerald-100 text-emerald-700' },
    { key: 'rejected', label: 'Rejected', color: 'bg-rose-100 text-rose-700' },
];

const statusColor = (status) =>
    ({
        accepted: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        rejected: 'bg-rose-100 text-rose-700 border-rose-200',
        pending: 'bg-amber-100 text-amber-700 border-amber-200',
    })[status] || 'bg-slate-100 text-slate-700';
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: isAdmin ? 'Admin' : 'Loans', href: '/' }, { title: 'Guarantors' }]">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 p-6 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">
            <!-- HEADER -->
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Guarantor Requests</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ isAdmin ? 'All system guarantee records' : 'Your active guarantees' }}
                    </p>
                </div>

                <!-- SEARCH -->
                <div class="w-full md:w-80">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search loan, member, guarantor..."
                        class="w-full rounded-xl border bg-white px-4 py-2 shadow-sm outline-none focus:ring-2 focus:ring-blue-900 dark:border-gray-700 dark:bg-gray-900"
                    />
                </div>
            </div>

            <!-- TABS -->
            <div class="mb-6 flex flex-wrap gap-2">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    @click="activeTab = t.key"
                    class="rounded-xl px-4 py-2 text-sm font-medium shadow-sm transition-all duration-200"
                    :class="
                        activeTab === t.key
                            ? 'bg-blue-900 text-white shadow-md'
                            : 'bg-white text-gray-700 hover:bg-orange-50 dark:bg-gray-900 dark:text-gray-300'
                    "
                >
                    {{ t.label }}
                </button>
            </div>

            <!-- EMPTY STATE -->
            <div v-if="!filtered.length" class="py-16 text-center">
                <div class="text-sm text-gray-400">No guarantor records found</div>
            </div>

            <!-- LIST -->
            <div v-else class="grid gap-4">
                <div
                    v-for="g in filtered"
                    :key="g.id"
                    class="group flex items-start justify-between rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- LEFT CONTENT -->
                    <div class="space-y-2">
                        <!-- Borrower -->
                        <p class="font-semibold text-gray-900 dark:text-white">{{ g.loan?.member?.first_name }} {{ g.loan?.member?.last_name }}</p>

                        <!-- Loan -->
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Loan No:
                            <span class="font-medium text-blue-900 dark:text-blue-400">
                                {{ g.loan?.loan_number }}
                            </span>
                        </p>

                        <!-- Amount -->
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Guaranteed Amount:
                            <span class="font-semibold text-orange-600"> KES {{ Number(g.guaranteed_amount).toLocaleString() }} </span>
                        </p>

                        <!-- Guarantor (Admin only) -->
                        <p v-if="isAdmin" class="pt-1 text-xs text-gray-400">
                            Guarantor:
                            {{ g.guarantorMember?.first_name }} {{ g.guarantorMember?.last_name }}
                        </p>
                    </div>

                    <!-- RIGHT ACTIONS -->
                    <div class="flex flex-col items-end gap-3">
                        <!-- STATUS -->
                        <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="statusColor(g.status)">
                            {{ g.status }}
                        </span>

                        <!-- VIEW -->
                        <button
                            @click="router.visit(`/guarantor-requests/${g.loan_id}`)"
                            class="rounded-xl bg-gradient-to-r from-blue-950 to-blue-800 px-4 py-2 text-sm text-white shadow-md transition hover:from-orange-500 hover:to-orange-600"
                        >
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
