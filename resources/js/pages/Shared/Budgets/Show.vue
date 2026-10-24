<template>
    <AppLayout :breadcrumbs="[{ title: 'Budgets', href: '/budgets' }, { title: `Budget - ${budget.title}` }]">
        <!-- Flash Message -->
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
                    flashType === 'success' ? 'border border-green-200 bg-green-50 text-green-800' : 'border border-red-200 bg-red-50 text-red-800',
                    'mx-auto mb-8 flex max-w-3xl items-center rounded-xl px-6 py-3 shadow-sm backdrop-blur-sm',
                ]"
            >
                <span class="flex-1 font-medium">{{ flashMessage }}</span>
                <button type="button" class="ml-3 text-gray-500 hover:text-gray-700" @click="flashMessage = null">✕</button>
            </div>
        </transition>

        <!-- Header -->
        <div
            class="flex items-start justify-between gap-3 rounded-2xl bg-gradient-to-r from-[#0a2342] to-[#0e2e5c] p-4 text-white shadow-md max-sm:mx-2 sm:items-center"
        >
            <div class="flex items-center gap-3">
                <Link
                    :href="route('budgets.index')"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-semibold sm:text-2xl">{{ budget.title }}</h2>
                    <p class="text-sm text-gray-200">{{ budget.description }}</p>
                </div>
            </div>
            <span :class="getStatusClass(budget.status)" class="inline-flex rounded-full bg-white/70 px-4 py-1.5 text-sm font-semibold">
                {{ getStatusLabel(budget.status) }}
            </span>
        </div>

        <div class="space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Budget Overview -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="card in overviewCards" :key="card.label" class="rounded-xl bg-white px-4 py-6 shadow-md transition hover:shadow-lg">
                    <div class="flex items-center gap-4">
                        <div :class="`rounded-full p-3 ${card.color}`">
                            <component :is="card.icon" class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ card.label }}</p>
                            <p class="text-lg font-semibold text-gray-800">{{ card.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Info & Actions -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Budget Info -->
                <div class="overflow-hidden rounded-xl bg-white shadow-md lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Budget Information</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 text-sm sm:grid-cols-2">
                        <div v-for="(info, i) in infoData" :key="i">
                            <label class="block font-medium text-gray-500">{{ info.label }}</label>
                            <p class="mt-1 text-gray-800">{{ info.value }}</p>
                            <p v-if="info.sub" class="text-xs text-gray-400">{{ info.sub }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-6">
                    <div class="overflow-hidden rounded-xl bg-white shadow-md">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                        </div>
                        <div class="flex flex-col gap-3 p-6">
                            <Link
                                v-if="can_edit"
                                :href="route('budgets.edit', budget.id)"
                                class="action-btn border border-gray-300 bg-blue-900 text-white hover:bg-blue-600"
                            >
                                ✏️ Edit Budget
                            </Link>

                            <button
                                v-if="can_submit && budget.status === 'draft'"
                                @click="submitBudget"
                                class="action-btn bg-orange-600 text-white hover:cursor-pointer hover:bg-orange-700"
                            >
                                📤 Submit for Approval
                            </button>

                            <button
                                v-if="can_approve && budget.status === 'pending'"
                                @click="approveBudget"
                                :disabled="processing"
                                class="action-btn bg-green-600 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                ✅ Approve Budget
                            </button>

                            <button
                                v-if="can_activate && budget.status === 'approved'"
                                @click="activateBudget"
                                :disabled="processing"
                                class="action-btn bg-blue-600 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                ⚡ Activate Budget
                            </button>

                            <button
                                v-if="can_close && budget.status === 'active'"
                                @click="closeBudget"
                                :disabled="processing"
                                class="action-btn bg-red-600 text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                🔒 Close Budget
                            </button>

                            <div class="space-y-2 border-t border-gray-200 pt-3">
                                <Link
                                    v-for="(link, i) in viewLinks"
                                    :key="i"
                                    :href="link.href"
                                    class="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800"
                                >
                                    <component :is="link.icon" class="h-4 w-4" /> {{ link.label }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Utilization -->
                    <div class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900">Budget Utilization</h3>
                        <div class="relative">
                            <div class="mb-1 flex justify-between">
                                <span class="text-xs font-medium text-gray-600">Progress</span>
                                <span class="text-xs font-semibold text-green-700">{{ utilization.utilization_percentage.toFixed(1) }}%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-200">
                                <div
                                    class="h-2 rounded-full transition-all"
                                    :style="`width:${Math.min(utilization.utilization_percentage, 100)}%`"
                                    :class="{
                                        'bg-green-600': utilization.utilization_percentage <= 75,
                                        'bg-yellow-500': utilization.utilization_percentage > 75 && utilization.utilization_percentage <= 90,
                                        'bg-red-500': utilization.utilization_percentage > 90,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Items -->
            <div class="overflow-hidden rounded-xl bg-white shadow-md">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Budget Items</h3>
                    <Link :href="route('budgets.items', budget.id)" class="text-sm text-indigo-600 hover:text-indigo-800">View All →</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    v-for="header in ['Category', 'Item', 'Budgeted', 'Spent', 'Remaining']"
                                    :key="header"
                                    class="px-6 py-3 text-left font-medium text-gray-600"
                                >
                                    {{ header }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in budget.budget_items.slice(0, 5)" :key="item.id" class="transition hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-800">{{ item.category }}</td>
                                <td class="px-6 py-3 text-gray-800">
                                    <div class="font-semibold">{{ item.item_name }}</div>
                                    <div v-if="item.description" class="text-xs text-gray-500">{{ item.description }}</div>
                                </td>
                                <td class="px-6 py-3 text-right">{{ formatCurrency(item.budgeted_amount) }}</td>
                                <td class="px-6 py-3 text-right">{{ formatCurrency(item.spent_amount) }}</td>
                                <td class="px-6 py-3 text-right font-semibold" :class="item.remaining_amount < 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ formatCurrency(item.remaining_amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- GLOBAL LOADING OVERLAY -->
        <transition enter-active-class="duration-200 ease-out" leave-active-class="duration-150 ease-in">
            <div v-if="showOverlay" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-3 rounded-xl bg-white/90 px-6 py-4 shadow-xl">
                    <svg class="h-8 w-8 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>

                    <p class="text-sm font-medium text-gray-700">Processing...</p>
                </div>
            </div>
        </transition>
    </AppLayout>
    <!-- Modern Confirmation Modal -->
    <transition
        enter-active-class="duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="confirmDialog.visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <div class="animate-fadeIn w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start gap-4">
                    <div class="rounded-xl bg-blue-100 p-3 text-blue-600">⚠️</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ confirmDialog.title }}
                        </h3>
                        <p class="mt-1 text-gray-600">
                            {{ confirmDialog.message }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="confirmDialog.visible = false"
                        class="rounded-lg border px-4 py-2 text-gray-700 hover:cursor-pointer hover:bg-gray-100"
                    >
                        Cancel
                    </button>

                    <button @click="confirmDialog.confirm" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:cursor-pointer hover:bg-blue-700">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { DollarSign, PieChart, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { ref, watch } from 'vue';
const page = usePage();

const props = defineProps({
    budget: Object,
    utilization: Object,
    recent_vouchers: Array,
    can_approve: Boolean,
    can_activate: Boolean,
    can_close: Boolean,
    can_edit: Boolean,
    can_submit: Boolean,
});

const flashMessage = ref(page.props.flash.success || page.props.flash.error);
const flashType = ref(page.props.flash.success ? 'success' : 'error');

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            flashMessage.value = flash.success;
            flashType.value = 'success';
        } else if (flash?.error) {
            flashMessage.value = flash.error;
            flashType.value = 'error';
        }

        if (flashMessage.value) {
            // Scroll to top instantly
            window.scrollTo({
                top: 0,
                behavior: 'smooth',
            });

            setTimeout(() => (flashMessage.value = null), 5000);
        }
    },
    { immediate: true, deep: true },
);

const processing = ref(false);

const formatCurrency = (amount) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0);

const getStatusClass = (status) =>
    ({
        draft: 'bg-gray-100 text-gray-700',
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        closed: 'bg-red-100 text-red-800',
    })[status] || 'bg-gray-100 text-gray-700';

const getStatusLabel = (status) =>
    ({
        draft: 'Draft',
        pending: 'Pending',
        approved: 'Approved',
        active: 'Active',
        closed: 'Closed',
    })[status] || 'Unknown';

const confirmDialog = ref({
    visible: false,
    title: '',
    message: '',
    confirm: null,
});

function openConfirm({ title, message, onConfirm }) {
    confirmDialog.value = {
        visible: true,
        title,
        message,
        confirm: () => {
            confirmDialog.value.visible = false;
            onConfirm();
        },
    };
}

const showOverlay = ref(false);

const submitBudget = () => {
    openConfirm({
        title: 'Submit Budget for Approval?',
        message: 'You will not be able to edit once submitted.',
        onConfirm: () => {
            processing.value = true;
            showOverlay.value = true;

            router.post(
                route('budgets.submit', props.budget.id),
                {},
                {
                    onSuccess: () => {
                        processing.value = false;
                        showOverlay.value = false;
                    },
                    onError: () => {
                        processing.value = false;
                        showOverlay.value = false;
                    },
                    onFinish: () => {
                        processing.value = false;
                        showOverlay.value = false;
                    },
                },
            );
        },
    });
};

const approveBudget = () => {
    if (processing.value) return;

    openConfirm({
        title: 'Approve Budget?',
        message: 'Are you sure you want to approve this budget?',
        onConfirm: () => {
            processing.value = true;
            showOverlay.value = true;

            router.post(
                route('budgets.approve', props.budget.id),
                {},
                {
                    onFinish: () => {
                        processing.value = false;
                        showOverlay.value = false;
                    },
                },
            );
        },
    });
};

const activateBudget = () => {
    if (processing.value) return;

    openConfirm({
        title: 'Activate Budget?',
        message: 'Activating this budget will deactivate any other active budgets for this year.',
        onConfirm: () => {
            processing.value = true;
            showOverlay.value = true;

            router.post(
                route('budgets.activate', props.budget.id),
                {},
                {
                    onFinish: () => {
                        processing.value = false;
                        showOverlay.value = false;
                    },
                },
            );
        },
    });
};

const closeBudget = () => {
    if (processing.value) return;

    openConfirm({
        title: 'Close Budget?',
        message: 'Closing a budget cannot be undone. Continue?',
        onConfirm: () => {
            processing.value = true;
            showOverlay.value = true;

            router.post(
                route('budgets.close', props.budget.id),
                {},
                {
                    onFinish: () => {
                        processing.value = false;
                        showOverlay.value = false;
                    },
                },
            );
        },
    });
};

const overviewCards = [
    { label: 'Total Budget', value: formatCurrency(props.budget.total_budget), color: 'bg-blue-500', icon: DollarSign },
    { label: 'Amount Spent', value: formatCurrency(props.utilization.total_spent), color: 'bg-red-500', icon: TrendingDown },
    { label: 'Remaining', value: formatCurrency(props.utilization.total_remaining), color: 'bg-green-500', icon: TrendingUp },
    { label: 'Utilization', value: `${props.utilization.utilization_percentage.toFixed(1)}%`, color: 'bg-indigo-500', icon: PieChart },
];

const infoData = [
    { label: 'Budget Year', value: props.budget.budget_year },
    { label: 'Status', value: getStatusLabel(props.budget.status) },
    {
        label: 'Budget Period',
        value: `${new Date(props.budget.start_date).toLocaleDateString()} - ${new Date(props.budget.end_date).toLocaleDateString()}`,
    },
    { label: 'Total Items', value: `${props.utilization.items_count} items in ${props.utilization.categories_count} categories` },
    { label: 'Created By', value: props.budget.creator?.name, sub: new Date(props.budget.created_at).toLocaleDateString() },
    ...(props.budget.approver
        ? [{ label: 'Approved By', value: props.budget.approver.name, sub: new Date(props.budget.approval_date).toLocaleDateString() }]
        : []),
];

const viewLinks = [
    { label: 'Budget Items', href: route('budgets.items', props.budget.id), icon: PieChart },
    { label: 'Variance Analysis', href: route('budgets.variance', props.budget.id), icon: TrendingUp },
    { label: 'Utilization Report', href: route('budgets.utilization', props.budget.id), icon: DollarSign },
];
</script>

<style scoped>
.action-btn {
    width: 100%;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    transition: all 0.2s ease-in-out;
}

@keyframes fadeIn {
    from {
        transform: scale(0.95);
        opacity: 0;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-fadeIn {
    animation: fadeIn 0.15s ease-out;
}
</style>
