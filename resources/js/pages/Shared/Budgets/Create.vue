<template>
    <AppLayout :breadcrumbs="[{ title: 'Budgets', href: '/budgets' }, { title: 'Create' }]">
        <!-- HEADER -->
        <div class="flex items-center gap-3 px-4 pt-6">
            <Link :href="route('budgets.index')" class="rounded-full bg-gray-100 p-2 transition hover:bg-orange-100">
                <svg class="h-5 w-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </Link>
            <h2 class="text-xl font-bold text-[#0a2342]">Create Budget</h2>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8">
            <!-- WARNING -->
            <div v-if="existing_budget" class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-4 text-orange-700">
                A budget for {{ suggested_year }} already exists.
            </div>

            <form @submit.prevent="submitBudget" class="space-y-8">
                <!-- BASIC INFO -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-blue-900">Basic Information</h3>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- YEAR -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Budget Year </label>
                            <input
                                v-model="form.budget_year"
                                type="number"
                                class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                                required
                            />
                            <p v-if="errors.budget_year" class="mt-1 text-sm text-red-500">
                                {{ errors.budget_year }}
                            </p>
                        </div>

                        <!-- TITLE -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Title </label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                                required
                            />
                            <p v-if="errors.title" class="mt-1 text-sm text-red-500">
                                {{ errors.title }}
                            </p>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Description </label>
                            <textarea
                                v-model="form.description"
                                class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                            ></textarea>
                        </div>

                        <!-- START -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Start Date </label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                                required
                            />
                        </div>

                        <!-- END -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> End Date </label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-600"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- BUDGET ITEMS -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-blue-900">Budget Items</h3>

                        <button
                            type="button"
                            @click="addBudgetItem"
                            class="rounded-lg bg-orange-500 px-4 py-2 text-white transition hover:bg-orange-600"
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

                                    <!-- CATEGORY -->
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
                                        <button type="button" @click="removeBudgetItem(i)" class="text-red-500 hover:text-red-700">✕</button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td colspan="4" class="px-3 py-2 text-right">Total</td>
                                    <td class="px-3 py-2 text-right text-blue-900">
                                        {{ formatCurrency(form.total_budget) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex justify-end gap-3">
                    <Link :href="route('budgets.index')" class="rounded-lg border px-4 py-2 text-gray-700 hover:bg-gray-100"> Cancel </Link>

                    <button type="submit" :disabled="processing" class="rounded-lg bg-blue-900 px-4 py-2 text-white transition hover:bg-blue-800">
                        {{ processing ? 'Saving...' : 'Create Budget' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    suggested_year: Number,
    existing_budget: Boolean,
    budget_accounts: Array,
    errors: Object,
});

const form = useForm({
    budget_year: props.suggested_year,
    title: `${props.suggested_year} Annual Budget`,
    description: '',
    total_budget: 0,
    start_date: `${props.suggested_year}-01-01`,
    end_date: `${props.suggested_year}-12-31`,
    budget_items: [],
});

const processing = ref(false);

/* AUTO UPDATE YEAR */
watch(
    () => form.budget_year,
    (year) => {
        form.title = `${year} Annual Budget`;
        form.start_date = `${year}-01-01`;
        form.end_date = `${year}-12-31`;
    },
);

/* GET ACCOUNT */
const getAccount = (id) => {
    return props.budget_accounts.find((a) => a.id === id);
};

/* ADD ITEM */
const addBudgetItem = () => {
    form.budget_items.push({
        chart_of_account_id: '',
        description: '',
        budgeted_amount: 0,
    });
};

/* REMOVE ITEM */
const removeBudgetItem = (index) => {
    form.budget_items.splice(index, 1);
    calculateTotalBudget();
};

/* TOTAL */
const calculateTotalBudget = () => {
    form.total_budget = form.budget_items.reduce((sum, item) => {
        return sum + (parseFloat(item.budgeted_amount) || 0);
    }, 0);
};

/* SUBMIT */
const submitBudget = () => {
    processing.value = true;
    calculateTotalBudget();

    form.post(route('budgets.store'), {
        onFinish: () => (processing.value = false),
    });
};

/* FORMAT */
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount || 0);
};
</script>
