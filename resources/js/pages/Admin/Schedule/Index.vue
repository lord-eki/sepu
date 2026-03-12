<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    recentLogs: Array,
    currentMonth: Number,
    currentYear: Number
})
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Financial Schedules' }]">

        <Head title="Financial Schedules" />

        <div class="p-6 space-y-8">

            <!-- PAGE HEADER -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">
                    Financial Schedule Processing
                </h1>

                <div class="text-sm text-gray-500">
                    Period: {{ currentMonth }}
                </div>
            </div>


            <!-- SCHEDULE CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Monthly Deposits -->
                <Link :href="route('schedule.monthly-deposit')"
                    class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="text-blue-600 text-lg font-semibold">
                    Monthly Deposits
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Generate monthly member contribution transactions
                </p>
                </Link>

                <!-- Loan Repayments -->
                <Link :href="route('schedule.loan-repayment')"
                    class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="text-green-600 text-lg font-semibold">
                    Loan Repayments
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Process monthly loan repayments
                </p>
                </Link>

                <!-- Loan Disbursements -->
                <Link :href="route('schedule.loan-disbursement')"
                    class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="text-purple-600 text-lg font-semibold">
                    Loan Disbursements
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Disburse approved loans to members
                </p>
                </Link>

                <!-- Dividend Payments -->
                <Link :href="route('schedule.dividend-payment')"
                    class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
                <div class="text-yellow-600 text-lg font-semibold">
                    Dividend Payments
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Pay dividends to eligible members
                </p>
                </Link>

            </div>


            <!-- RECENT EXECUTIONS -->
            <div class="bg-white shadow rounded-xl p-6">

                <h2 class="text-lg font-semibold mb-4">
                    Recent Schedule Executions
                </h2>

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Schedule</th>
                                <th class="p-3 text-left">Period</th>
                                <th class="p-3 text-left">Execution Date</th>
                                <th class="p-3 text-left">Executed By</th>
                                <th class="p-3 text-left">Records</th>
                                <th class="p-3 text-left">Amount</th>
                                <th class="p-3 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr v-for="log in recentLogs" :key="log.id" class="border-t">

                                <td class="p-3">
                                    {{ log.schedule_type }}
                                </td>

                                <td class="p-3">
                                    {{ log.processing_month }}/{{ log.processing_year }}
                                </td>

                                <td class="p-3">
                                    {{ log.execution_date }}
                                </td>

                                <td class="p-3">
                                    {{ log.executed_by }}
                                </td>

                                <td class="p-3">
                                    {{ log.total_records_processed }}
                                </td>

                                <td class="p-3">
                                    {{ log.total_amount }}
                                </td>

                                <td class="p-3">

                                    <span v-if="log.status === 'Completed'" class="text-green-600 font-semibold">
                                        Completed
                                    </span>

                                    <span v-else class="text-red-600 font-semibold">
                                        Failed
                                    </span>

                                </td>

                            </tr>

                            <tr v-if="recentLogs.length === 0">
                                <td colspan="7" class="text-center p-6 text-gray-400">
                                    No schedules executed yet
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </AppLayout>
</template>