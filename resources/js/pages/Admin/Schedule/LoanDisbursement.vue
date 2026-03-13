<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    loans: Array,
    summary: Object,
    filters: Object,
});

const selectedLoans = ref([]);

const filterDateFrom = ref(props.filters.date_from);
const filterDateTo = ref(props.filters.date_to);

function applyFilters() {
    router.get(route('schedule.loan-disbursement'), {
        date_from: filterDateFrom.value,
        date_to: filterDateTo.value,
    });
}

function toggleLoan(id) {
    if (selectedLoans.value.includes(id)) {
        selectedLoans.value = selectedLoans.value.filter((l) => l !== id);
    } else {
        selectedLoans.value.push(id);
    }
}

function runDisbursement() {
    if (selectedLoans.value.length === 0) {
        alert('Select loans to disburse');
        return;
    }

    if (!confirm('Disburse selected loans?')) return;

    axios
        .post(route('schedule.loan-disbursement.run'), {
            loan_ids: selectedLoans.value,
            year: new Date().getFullYear(),
        })
        .then(() => {
            router.reload();
        });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Financial Schedules', href: route('schedule.index') }, { title: 'Loan Disbursement' }]">
        <Head title="Loan Disbursement Schedule" />

        <div class="space-y-8 p-6">
            <!-- HEADER -->

            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Loan Disbursement Processor</h1>
                    <p class="text-sm text-gray-500">Process approved loans for member disbursement</p>
                </div>
            </div>

            <!-- SUMMARY CARDS -->

            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Approved Loans</div>
                    <div class="text-2xl font-bold text-blue-900">
                        {{ summary.total_loans }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Total Approved</div>
                    <div class="text-2xl font-bold text-blue-900">KES {{ summary.total_approved }}</div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Processing Fees</div>
                    <div class="text-2xl font-bold text-orange-500">KES {{ summary.total_processing_fee }}</div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Net Disbursement</div>
                    <div class="text-2xl font-bold text-green-600">KES {{ summary.total_net }}</div>
                </div>
            </div>

            <!-- FILTERS -->

            <div class="rounded-xl border bg-white p-6 shadow">
                <h2 class="mb-4 font-semibold text-gray-700">Filter Approved Loans</h2>

                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="text-sm text-gray-600"> From </label>

                        <input type="date" v-model="filterDateFrom" class="rounded-lg border px-3 py-2" />
                    </div>

                    <div>
                        <label class="text-sm text-gray-600"> To </label>

                        <input type="date" v-model="filterDateTo" class="rounded-lg border px-3 py-2" />
                    </div>

                    <button @click="applyFilters" class="rounded-lg bg-blue-900 px-5 py-2 text-white hover:bg-blue-800">Apply Filter</button>
                </div>
            </div>

            <!-- LOANS TABLE -->

            <div class="overflow-hidden rounded-xl border bg-white shadow-xl">
                <div class="border-b p-5 font-semibold text-gray-700">Approved Loans Awaiting Disbursement</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-3"></th>
                                <th class="p-3 text-left">Loan Number</th>
                                <th class="p-3 text-left">Member</th>
                                <th class="p-3 text-left">Product</th>
                                <th class="p-3 text-left">Approved</th>
                                <th class="p-3 text-left">Processing Fee</th>
                                <th class="p-3 text-left">Insurance</th>
                                <th class="p-3 text-left">Net Disbursement</th>
                                <th class="p-3 text-left">Approval Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="loan in loans" :key="loan.id" class="border-t hover:bg-gray-50">
                                <td class="p-3">
                                    <input type="checkbox" :checked="selectedLoans.includes(loan.id)" @click="toggleLoan(loan.id)" class="h-4 w-4" />
                                </td>

                                <td class="p-3 font-medium">
                                    {{ loan.loan_number }}
                                </td>

                                <td class="p-3">
                                    {{ loan.member_name }}
                                </td>

                                <td class="p-3">
                                    {{ loan.product }}
                                </td>

                                <td class="p-3">KES {{ loan.approved_amount }}</td>

                                <td class="p-3">KES {{ loan.processing_fee }}</td>

                                <td class="p-3">KES {{ loan.insurance_fee }}</td>

                                <td class="p-3 font-semibold text-green-600">KES {{ loan.net_disbursement }}</td>

                                <td class="p-3">
                                    {{ loan.approval_date }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ACTIONS -->

            <div class="rounded-xl border bg-white p-6 shadow">
                <h2 class="mb-4 font-semibold text-gray-700">Disbursement Actions</h2>

                <div class="flex flex-wrap gap-4">
                    <button @click="runDisbursement" class="rounded-lg bg-green-600 px-6 py-2 text-white hover:bg-green-700">
                        Disburse Selected Loans
                    </button>

                    <a :href="route('schedule.loan-disbursement.export')" class="rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
                        Export CSV
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
