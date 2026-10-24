<template>
    <AppLayout :breadcrumbs="[{ title: 'Budgets', href: '/budgets' }, { title: `Edit Budget - ${budget.title}` }]">
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

        <!-- Page header -->
        <div class="pb-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('budgets.show', budget.id)" class="text-slate-500 transition hover:text-[#f97316]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </Link>

                    <div>
                        <h1 class="mt-5 text-xl leading-tight font-semibold text-[#0a2342] sm:text-2xl">
                            Edit Budget — <span class="text-[#0a2342]/90">{{ budget.title }}</span>
                        </h1>
                        <div class="mt-1 flex items-center gap-3">
                            <p class="text-sm text-gray-600">{{ budget.budget_year }} Budget</p>
                            <span class="h-0.5 w-10 rounded-full bg-[#f97316]"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="min-h-screen bg-[#f8fafc] py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Status warning (non-editable) -->
                <div v-if="budget.status !== 'draft'">
                    <div class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="rounded-full bg-[#fff3e0] p-2">
                                <svg class="h-5 w-5 text-[#f97316]" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.366-.89 1.603-.89 1.969 0l6.518 15.857A1 1 0 0115.8 20H4.2a1 1 0 01-.944-1.044L8.257 3.1zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-9a1 1 0 00-.993.883L9 6v5a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-[#0a2342]">Budget Cannot Be Edited</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Only draft budgets can be edited. This budget is currently
                                    <strong class="text-[#0a2342]"> "{{ getStatusLabel(budget.status) }}"</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit form (draft only) -->
                <form @submit.prevent="updateBudget" v-if="budget.status === 'draft'" class="space-y-6">
                    <!-- Basic Information card -->
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md">
                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-[#0a2342]">Basic Information</h2>
                            <div class="text-sm text-gray-500">Edit the budget meta information</div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label for="budget_year" class="block text-sm font-medium text-gray-700">Budget Year</label>
                                    <input
                                        id="budget_year"
                                        v-model="form.budget_year"
                                        type="number"
                                        :min="new Date().getFullYear()"
                                        :max="new Date().getFullYear() + 5"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:border-[#f97316] focus:ring-2 focus:ring-[#f97316]/40 focus:outline-none"
                                        required
                                    />
                                    <div v-if="errors.budget_year" class="mt-1 text-sm text-red-600">{{ errors.budget_year }}</div>
                                </div>

                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Budget Title</label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:border-[#f97316] focus:ring-2 focus:ring-[#f97316]/40 focus:outline-none"
                                        required
                                    />
                                    <div v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:border-[#f97316] focus:ring-2 focus:ring-[#f97316]/40 focus:outline-none"
                                    ></textarea>
                                    <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                                </div>

                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:border-[#f97316] focus:ring-2 focus:ring-[#f97316]/40 focus:outline-none"
                                        required
                                    />
                                    <div v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</div>
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        id="end_date"
                                        v-model="form.end_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:border-[#f97316] focus:ring-2 focus:ring-[#f97316]/40 focus:outline-none"
                                        required
                                    />
                                    <div v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Budget Items card -->
                    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow">
                        <!-- HEADER -->
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-[#0a2342]">Budget Items</h3>

                            <button
                                type="button"
                                @click="addBudgetItem"
                                class="rounded-lg bg-[#f97316] px-4 py-2 text-white transition hover:bg-[#e86b11]"
                            >
                                + Add Item
                            </button>
                        </div>

                        <!-- EMPTY -->
                        <div v-if="form.budget_items.length === 0" class="py-6 text-center text-gray-500">No budget items yet.</div>

                        <!-- TABLE -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full border text-sm">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left">#</th>
                                        <th class="px-3 py-2 text-left">Account</th>
                                        <th class="px-3 py-2 text-left">Category</th>
                                        <th class="px-3 py-2 text-left">Description</th>
                                        <th class="px-3 py-2 text-right">Amount</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="(item, i) in form.budget_items" :key="i" class="border-t hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ i + 1 }}</td>

                                        <!-- ACCOUNT -->
                                        <td class="px-3 py-2">
                                            <select v-model="item.chart_of_account_id" class="w-full rounded-lg border border-gray-300 p-2" required>
                                                <option value="">Select Account</option>
                                                <option v-for="acc in budget_accounts" :key="acc.id" :value="acc.id">
                                                    {{ acc.account_category }} - {{ acc.account_name }}
                                                </option>
                                            </select>
                                        </td>

                                        <!-- CATEGORY (AUTO) -->
                                        <td class="px-3 py-2 text-gray-600">
                                            {{ getAccount(item.chart_of_account_id)?.account_category || '-' }}
                                        </td>

                                        <!-- DESCRIPTION -->
                                        <td class="px-3 py-2">
                                            <input v-model="item.description" class="w-full rounded-lg border border-gray-300 p-2" />
                                        </td>

                                        <!-- AMOUNT -->
                                        <td class="px-3 py-2">
                                            <input
                                                v-model.number="item.budgeted_amount"
                                                type="number"
                                                class="w-full rounded-lg border border-gray-300 p-2 text-right"
                                                @input="calculateTotalBudget"
                                                required
                                            />
                                        </td>

                                        <!-- DELETE -->
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="confirmDelete(i)" class="text-red-500 hover:text-red-700">✕</button>
                                        </td>
                                    </tr>
                                </tbody>

                                <!-- TOTAL -->
                                <tfoot class="bg-gray-50 font-semibold">
                                    <tr>
                                        <td colspan="4" class="px-3 py-2 text-right">Total</td>
                                        <td class="px-3 py-2 text-right text-[#0a2342]">
                                            {{ formatCurrency(form.total_budget) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>
                    <!-- Actions (sticky on small screens) -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-md">
                        <div class="flex justify-end gap-3 md:items-center">
                            <Link
                                :href="route('budgets.show', budget.id)"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#0a2342] transition hover:shadow-sm"
                            >
                                Cancel
                            </Link>

                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex items-center gap-3 rounded-lg bg-[#f97316] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#e86b11] disabled:opacity-60"
                            >
                                <svg v-if="processing" class="mr-2 -ml-1 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.3726 0 0 5.3726 0 12h4z"></path>
                                </svg>
                                {{ processing ? 'Updating...' : 'Update Budget' }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Read-only view for non-draft -->
                <div v-else class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-[#0a2342]">Budget Information (Read Only)</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Budget Year</label>
                                <p class="mt-1 text-sm text-[#0a2342]">{{ budget.budget_year }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Budget Title</label>
                                <p class="mt-1 text-sm text-[#0a2342]">{{ budget.title }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-600">Description</label>
                                <p class="mt-1 text-sm text-[#0a2342]">{{ budget.description || 'No description provided' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Start Date</label>
                                <p class="mt-1 text-sm text-[#0a2342]">{{ formatDate(budget.start_date) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">End Date</label>
                                <p class="mt-1 text-sm text-[#0a2342]">{{ formatDate(budget.end_date) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Total Budget</label>
                                <p class="mt-1 text-lg font-semibold text-[#0a2342]">{{ formatCurrency(budget.total_budget) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-600">Status</label>
                                <div class="mt-1">
                                    <span :class="getStatusClass(budget.status) + ' inline-flex rounded-full px-3 py-1 text-xs font-semibold'">
                                        {{ getStatusLabel(budget.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-gray-100 pt-4">
                            <Link
                                :href="route('budgets.show', budget.id)"
                                class="inline-flex items-center rounded-lg bg-[#f97316] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#e86b11]"
                            >
                                Back to Budget Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-lg">
                <h3 class="mb-3 text-lg font-semibold text-[#0a2342]">Delete Item?</h3>
                <p class="text-sm text-gray-600">Are you sure you want to delete this item? This action cannot be undone.</p>

                <div class="mt-5 flex justify-end gap-3">
                    <button @click="showDeleteConfirm = false" class="rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-800 hover:bg-gray-300">
                        Cancel
                    </button>
                    <button @click="removeBudgetItem" class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const search = ref('');

const page = usePage();

const flashMessage = ref(page.props.flash?.success || page.props.flash?.error);
const flashType = ref(page.props.flash?.success ? 'success' : 'error');

const itemRefs = ref([]);

const addBudgetItem = () => {
    form.budget_items.push({
        chart_of_account_id: '',
        description: '',
        budgeted_amount: 0,
    });
};

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
            setTimeout(() => (flashMessage.value = null), 5000);
        }
    },
    { immediate: true, deep: true },
);

const props = defineProps({
    budget: Object,
    budget_accounts: Array,
    errors: Object,
});

const form = useForm({
    budget_year: props.budget.budget_year,
    title: props.budget.title,
    description: props.budget.description,
    total_budget: props.budget.total_budget,
    start_date: props.budget.start_date?.substring(0, 10) || '',
    end_date: props.budget.end_date?.substring(0, 10) || '',
    budget_items: [],
});

const processing = ref(false);

// Initialize budget items from existing data
onMounted(() => {
    form.budget_items = (props.budget.budget_items || []).map((item) => ({
        chart_of_account_id: item.chart_of_account_id,
        description: item.description,
        budgeted_amount: item.budgeted_amount,
    }));

    calculateTotalBudget();
});

// Watch for budget year changes to update title and default dates
watch(
    () => form.budget_year,
    (newYear) => {
        if (newYear) {
            form.title = `${newYear} Annual Budget`;
            form.start_date = `${newYear}-01-01`;
            form.end_date = `${newYear}-12-31`;
        }
    },
);

const showDeleteConfirm = ref(false);
const deleteIndex = ref(null);

const filteredItems = computed(() => {
    return form.budget_items.filter(
        (i) => i.item_name.toLowerCase().includes(search.value.toLowerCase()) || i.category.toLowerCase().includes(search.value.toLowerCase()),
    );
});

const confirmDelete = (index) => {
    deleteIndex.value = index;
    showDeleteConfirm.value = true;
};

const removeBudgetItem = () => {
    if (deleteIndex.value !== null) {
        form.budget_items.splice(deleteIndex.value, 1);
        calculateTotalBudget();
    }
    showDeleteConfirm.value = false;
    deleteIndex.value = null;
};

const getAccount = (id) => {
    return props.budget_accounts.find((a) => a.id === id);
};

const calculateTotalBudget = () => {
    const total = form.budget_items.reduce((sum, item) => {
        return sum + (parseFloat(item.budgeted_amount) || 0);
    }, 0);
    form.total_budget = total;
};

const updateBudget = () => {
    processing.value = true;
    calculateTotalBudget();

    form.put(route('budgets.update', props.budget.id), {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        approved: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        closed: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Draft',
        approved: 'Approved',
        active: 'Active',
        closed: 'Closed',
    };
    return labels[status] || status;
};
</script>

<style scoped>
button:hover {
    cursor: pointer;
}

@keyframes highlightFlash {
    0% {
        background-color: #ca9252ff;
    }

    100% {
        background-color: white;
    }
}

.animate-highlight {
    animation: highlightFlash 1.5s ease-out;
}
</style>
