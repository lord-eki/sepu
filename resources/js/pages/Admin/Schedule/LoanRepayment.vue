<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    rows: Array,
    summary: Object,
    month: String,
    alreadyRun: Boolean,
});

const selectedMonth = ref(props.month);
const previewData = ref(null);
const showPreview = ref(false);

function loadMonth() {
    router.get(route('schedule.loan-repayment'), {
        month: selectedMonth.value,
    });
}

function previewSchedule() {
    axios
        .post(route('schedule.loan-repayment.preview'), {
            month: selectedMonth.value,
        })
        .then((res) => {
            previewData.value = res.data;
            showPreview.value = true;
        });
}

function runSchedule() {
    if (!previewData.value) return;

    if (!confirm('Run Loan Repayment Schedule?')) return;

    axios
        .post(route('schedule.loan-repayment.run'), {
            month: selectedMonth.value,
            entries: previewData.value.preview,
        })
        .then(() => {
            router.reload();
        });
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Financial Schedules', href: route('schedule.index') }, { title: 'Loan Repayments' }]">
        <Head title="Loan Repayment Schedule" />

        <div class="space-y-8 p-6">
            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Loan Repayment Schedule</h1>
                    <p class="text-sm text-gray-500">Run automated monthly loan repayment deductions</p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="month" v-model="selectedMonth" class="rounded-lg border px-3 py-2 shadow-sm" />

                    <button @click="loadMonth" class="rounded-lg bg-blue-900 px-5 py-2 text-white shadow hover:bg-blue-800">Load Data</button>
                </div>
            </div>

            <!-- WARNING -->

            <div v-if="alreadyRun" class="rounded-lg border border-yellow-300 bg-yellow-100 p-4 text-yellow-800">
                Loan repayment schedule already executed for this month.
            </div>

            <!-- SCHEDULE STATUS -->

            <div class="rounded-xl border bg-white p-6 shadow">
                <h2 class="mb-3 font-semibold text-gray-700">Schedule Status</h2>

                <div class="flex flex-wrap gap-6 text-sm">
                    <div>
                        <span class="text-gray-500">Month</span>
                        <div class="font-semibold">{{ selectedMonth }}</div>
                    </div>

                    <div>
                        <span class="text-gray-500">Status</span>

                        <div v-if="alreadyRun" class="font-semibold text-green-600">Completed</div>

                        <div v-else class="font-semibold text-orange-500">Not Executed</div>
                    </div>

                    <div>
                        <span class="text-gray-500">Active Loans</span>
                        <div class="font-semibold">{{ summary.total_loans }}</div>
                    </div>

                    <div>
                        <span class="text-gray-500">Outstanding Balance</span>
                        <div class="font-semibold">KES {{ summary.total_balance }}</div>
                    </div>
                </div>
            </div>

            <!-- SUMMARY CARDS -->

            <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Active Loans</div>
                    <div class="text-2xl font-bold text-blue-900">
                        {{ summary.total_loans }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Total Principal</div>
                    <div class="text-2xl font-bold text-blue-900">KES {{ summary.total_principal }}</div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Total Interest</div>
                    <div class="text-2xl font-bold text-orange-500">KES {{ summary.total_interest }}</div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Monthly Instalments</div>
                    <div class="text-2xl font-bold text-green-600">KES {{ summary.total_instalment }}</div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Outstanding Balance</div>
                    <div class="text-2xl font-bold text-red-500">KES {{ summary.total_balance }}</div>
                </div>
            </div>

            <!-- ACTIONS -->

            <div class="rounded-xl border bg-white p-6 shadow">
                <h2 class="mb-4 font-semibold text-gray-700">Schedule Actions</h2>

                <div class="flex flex-wrap gap-4">
                    <button
                        @click="previewSchedule"
                        :disabled="alreadyRun"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        Preview Repayments
                    </button>
                </div>
            </div>

            <!-- LOANS TABLE -->

            <div class="overflow-hidden rounded-xl border bg-white shadow-xl">
                <div class="border-b p-5 font-semibold text-gray-700">Active Loan Repayment Schedule</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Loan No</th>
                                <th class="p-3 text-left">Member</th>
                                <th class="p-3 text-left">Product</th>
                                <th class="p-3 text-left">Principal</th>
                                <th class="p-3 text-left">Interest</th>
                                <th class="p-3 text-left">Instalment</th>
                                <th class="p-3 text-left">Loan Balance</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="loan in rows" :key="loan.loan_id" class="border-t hover:bg-gray-50">
                                <td class="p-3 font-medium">
                                    {{ loan.loan_number }}
                                </td>

                                <td class="p-3">
                                    {{ loan.member_name }}
                                </td>

                                <td class="p-3">
                                    {{ loan.loan_product }}
                                </td>

                                <td class="p-3">KES {{ loan.monthly_principal }}</td>

                                <td class="p-3">KES {{ loan.monthly_interest }}</td>

                                <td class="p-3 font-semibold text-blue-900">KES {{ loan.total_instalment }}</td>

                                <td class="p-3">KES {{ loan.loan_balance }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PREVIEW MODAL -->

            <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="max-h-[80vh] w-[820px] overflow-auto rounded-xl bg-white p-6 shadow-xl">
                    <h2 class="mb-4 text-lg font-semibold">Loan Repayment Preview</h2>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Loan</th>
                                <th class="p-2 text-left">Member</th>
                                <th class="p-2 text-left">Principal</th>
                                <th class="p-2 text-left">Interest</th>
                                <th class="p-2 text-left">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in previewData.preview" :key="item.loan_id" class="border-t">
                                <td class="p-2">
                                    {{ item.loan_number }}
                                </td>

                                <td class="p-2">
                                    {{ item.member_name }}
                                </td>

                                <td class="p-2">KES {{ item.principal }}</td>

                                <td class="p-2">KES {{ item.interest }}</td>

                                <td class="p-2 font-semibold">KES {{ item.total }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 text-right text-lg font-semibold">Total: KES {{ previewData.total_amount }}</div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showPreview = false" class="rounded-lg border px-4 py-2">Cancel</button>

                        <button @click="runSchedule" class="rounded-lg bg-green-600 px-5 py-2 text-white hover:bg-green-700">Run Repayments</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
