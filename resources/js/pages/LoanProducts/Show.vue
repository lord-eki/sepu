<template>
    <AppLayout :breadcrumbs="[{ title: 'Loan Products', href: route('loan-products.index') }, { title: loanProduct.name }]">
        <!-- Flash Message -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div
                v-if="showFlash"
                class="mx-auto mb-4 w-fit rounded-xl p-4 text-center font-medium shadow-md"
                :class="flash.error ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
            >
                {{ flash.error || flash.success }}
            </div>
        </transition>

        <!-- Header -->
        <div class="mx-4 mt-4 rounded-2xl bg-[#0B2B40] p-6 text-white shadow-lg">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <!-- Back Arrow -->
                <Link
                    :href="route('loan-products.index')"
                    class="flex items-center gap-2 text-sm font-medium text-white/80 transition hover:text-white"
                >
                    <span class="text-xl">←</span> Back
                </Link>

                <h1 class="text-xl font-bold tracking-wide text-white sm:ml-2">
                    {{ loanProduct.name }}
                </h1>

                <div class="mt-2 flex flex-wrap gap-3 sm:mt-0">
                    <Link
                        :href="route('loan-products.edit', loanProduct.id)"
                        class="flex items-center rounded-xl bg-blue-700 px-5 text-sm font-medium text-white shadow transition hover:bg-blue-800"
                    >
                        Edit
                    </Link>

                    <Button
                        @click="toggleStatus(loanProduct)"
                        class="rounded-xl px-5 py-2.5 font-medium text-white shadow"
                        :class="loanProduct.is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'"
                    >
                        {{ loanProduct.is_active ? 'Deactivate' : 'Activate' }}
                    </Button>

                    <Button @click="confirmDelete" class="rounded-xl bg-red-600 px-5 py-2.5 font-medium text-white shadow hover:bg-red-700">
                        Delete
                    </Button>
                </div>
            </div>
        </div>

        <!-- Details Card -->
        <Card class="m-4 mt-6 overflow-hidden rounded-2xl border border-gray-200 shadow-lg">
            <CardHeader class="border-b border-gray-200 bg-blue-50 p-5">
                <CardTitle class="flex items-center gap-2 text-lg font-semibold text-[#0B2B40]">
                    <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                    Product Information
                </CardTitle>
            </CardHeader>

            <CardContent class="bg-white px-6 pt-6 pb-10">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(value, label) in displayData"
                        :key="label"
                        class="space-y-1 rounded-xl border border-gray-100 bg-gray-50 p-4 shadow-sm transition hover:bg-blue-50"
                    >
                        <dt class="font-medium text-gray-600">{{ label }}</dt>
                        <dd class="font-semibold text-gray-900">{{ value }}</dd>
                    </div>
                </dl>

                <!-- Eligibility Section -->
                <div class="mt-10">
                    <h2 class="mb-3 border-l-4 border-orange-500 pl-2 text-lg font-semibold text-[#0B2B40]">Eligibility Criteria</h2>
                    <ul class="list-inside list-disc space-y-1 text-gray-700">
                        <li>Minimum Membership Months: {{ loanProduct.eligibility_criteria.minimum_membership_months }}</li>
                        <li>Minimum Shares Balance: {{ loanProduct.eligibility_criteria.minimum_shares_balance }}</li>
                        <li>Max Loan to Shares Ratio: {{ loanProduct.eligibility_criteria.maximum_loan_to_shares_ratio }}</li>
                        <li>Max Salary Deduction Ratio: {{ loanProduct.eligibility_criteria.maximum_salary_deduction_ratio }}</li>
                        <li>
                            Clean Credit History:
                            <span :class="loanProduct.eligibility_criteria.clean_credit_history ? 'text-green-600' : 'text-red-600'">
                                {{ loanProduct.eligibility_criteria.clean_credit_history ? 'Yes' : 'No' }}
                            </span>
                        </li>
                        <li>
                            Regular Deposits Required:
                            <span :class="loanProduct.eligibility_criteria.regular_deposits_required ? 'text-green-600' : 'text-red-600'">
                                {{ loanProduct.eligibility_criteria.regular_deposits_required ? 'Yes' : 'No' }}
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Documents -->
                <div class="mt-10">
                    <h2 class="mb-3 border-l-4 border-orange-500 pl-2 text-lg font-semibold text-[#0B2B40]">Required Documents</h2>
                    <ul class="list-inside list-disc space-y-1 text-gray-700">
                        <li v-for="doc in loanProduct.required_documents" :key="doc">{{ doc }}</li>
                    </ul>
                </div>

                <!-- Guarantor -->
                <div class="mt-10">
                    <h2 class="mb-3 border-l-4 border-orange-500 pl-2 text-lg font-semibold text-[#0B2B40]">Guarantor Requirements</h2>
                    <p>
                        Requires Guarantor:
                        <span :class="loanProduct.requires_guarantor ? 'text-green-600' : 'text-red-600'">
                            {{ loanProduct.requires_guarantor ? 'Yes' : 'No' }}
                        </span>
                    </p>
                    <p v-if="loanProduct.requires_guarantor" class="text-gray-700">Minimum Guarantors: {{ loanProduct.min_guarantors }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Status Toggle Confirmation Modal -->
        <transition name="fade">
            <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="mb-3 text-lg font-semibold text-[#0B2B40]">Confirm Status Change</h3>
                    <p class="mb-6 text-gray-600">
                        Are you sure you want to
                        <span :class="loanProduct.is_active ? 'font-semibold text-orange-600' : 'font-semibold text-green-600'">
                            {{ loanProduct.is_active ? 'Deactivate' : 'Activate' }}
                        </span>
                        this loan product?
                    </p>

                    <div class="flex justify-end gap-3">
                        <button @click="showStatusModal = false" class="rounded-xl bg-gray-200 px-4 py-2 text-gray-700 transition hover:bg-gray-300">
                            Cancel
                        </button>

                        <button
                            @click="confirmToggleStatus"
                            class="flex items-center justify-center gap-2 rounded-xl px-4 py-2 font-medium text-white shadow transition"
                            :class="loanProduct.is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700'"
                            :disabled="toggling"
                        >
                            <span
                                v-if="toggling"
                                class="loader-border h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span>{{
                                toggling
                                    ? loanProduct.is_active
                                        ? 'Deactivating...'
                                        : 'Activating...'
                                    : `Yes, ${loanProduct.is_active ? 'Deactivate' : 'Activate'}`
                            }}</span>
                        </button>
                    </div>

                    <!-- Close Icon -->
                    <button @click="showStatusModal = false" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600">✕</button>
                </div>
            </div>
        </transition>

        <!-- Inline Delete Modal -->
        <transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="mb-3 text-lg font-semibold text-[#0B2B40]">Confirm Deletion</h3>
                    <p class="mb-6 text-gray-600">
                        Are you sure you want to delete this loan product? This action
                        <span class="font-semibold text-red-600">cannot be undone.</span>
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showDeleteModal = false"
                            class="rounded-xl bg-gray-200 px-4 py-2 text-gray-700 transition hover:cursor-pointer hover:bg-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteProduct"
                            class="flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-white transition hover:cursor-pointer hover:bg-red-700"
                            :disabled="deleting"
                        >
                            <span
                                v-if="deleting"
                                class="loader-border h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            <span>{{ deleting ? 'Deleting...' : 'Yes, Delete' }}</span>
                        </button>
                    </div>

                    <!-- Close Icon -->
                    <button @click="showDeleteModal = false" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600">✕</button>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const deleting = ref(false);
const props = defineProps<{ loanProduct: any }>();
const flash = reactive({ success: '', error: '' });
const showFlash = ref(false);
const showDeleteModal = ref(false);

const displayData = computed(() => ({
    Code: props.loanProduct.code,
    Description: props.loanProduct.description,
    'Min Amount': `Ksh ${props.loanProduct.min_amount.toLocaleString()}`,
    'Max Amount': `Ksh ${props.loanProduct.max_amount.toLocaleString()}`,
    'Interest Rate': `${props.loanProduct.interest_rate}%`,
    'Interest Method': props.loanProduct.interest_method === 'flat_rate' ? 'Flat Rate' : 'Reducing Balance',
    'Min Term (Months)': props.loanProduct.min_term_months,
    'Max Term (Months)': props.loanProduct.max_term_months,
    'Processing Fee Rate': `${props.loanProduct.processing_fee_rate}%`,
    'Insurance Rate': `${props.loanProduct.insurance_rate}%`,
    'Grace Period (Days)': props.loanProduct.grace_period_days,
    'Penalty Rate': `${props.loanProduct.penalty_rate}%`,
    Status: props.loanProduct.is_active ? 'Active' : 'Inactive',
    'Created At': new Date(props.loanProduct.created_at).toLocaleString(),
}));

const confirmDelete = () => (showDeleteModal.value = true);

const deleteProduct = () => {
    deleting.value = true;
    router.delete(route('loan-products.destroy', props.loanProduct.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const message = page.props.flash.success || page.props.flash.error;
            const isError = !!page.props.flash.error;
            showFlashMsg(message, isError);
            showDeleteModal.value = false;
            setTimeout(() => router.visit(route('loan-products.index')), 500);
        },
        onError: () => {
            showFlashMsg('Failed to delete product. Please try again.', true);
            showDeleteModal.value = false;
        },
        onFinish: () => (deleting.value = false),
    });
};

const showStatusModal = ref(false);
const toggling = ref(false);
const selectedProduct = ref<any>(null);

function toggleStatus(product: any) {
    selectedProduct.value = product;
    showStatusModal.value = true;
}

function confirmToggleStatus() {
    if (!selectedProduct.value) return;
    const product = selectedProduct.value;
    toggling.value = true;

    router.post(
        route('loan-products.toggle-status', { loanProduct: product.id }),
        {},
        {
            preserveState: true,
            onSuccess: (page) => {
                const message = page.props.flash.success || page.props.flash.error || 'Action completed';
                const isError = !!page.props.flash.error;
                showFlashMsg(message, isError);
                product.is_active = !product.is_active;
                showStatusModal.value = false;
            },
            onError: () => showFlashMsg('Error updating status.', true),
            onFinish: () => (toggling.value = false),
        },
    );
}

function showFlashMsg(message: string, isError = false) {
    flash.success = isError ? '' : message;
    flash.error = isError ? message : '';
    showFlash.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => (showFlash.value = false), 4000);
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
