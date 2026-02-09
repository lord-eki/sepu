<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'

const props = defineProps<{
    disbursements: Array<any>
    daily_disbursements: Record<string, { count: number; total_amount: number }>
    summary: {
        total_disbursements: number
        total_amount: number
        average_loan_size: number
        daily_average: number
    }
    start_date: string
    end_date: string
}>()

const startDate = ref(props.start_date)
const endDate = ref(props.end_date)

function applyDateFilter() {
    router.get(
        route('reports.loans.disbursement'),
        { start_date: startDate.value, end_date: endDate.value },
        { preserveState: true, replace: true }
    )
}

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0)
}

const breadcrumbs = [
    { title: 'Loan Reports', href: route('reports.loansReport.index') },
    { title: 'Disbursement' },
]
</script>

<template>

    <Head title="Loan Disbursement" />

    <AppLayout title="Loan Disbursement" :breadcrumbs="breadcrumbs">

        <!-- Date Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mx-5 mt-5 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-[#0a2342]">Loan Disbursement</h2>
                <p class="text-sm text-blue-700 mt-1">View all loan disbursements for the selected period</p>
            </div>

            <div class="flex items-center gap-2">
                <CalendarDays class="h-5 w-5 text-orange-500" />
                <input type="date" v-model="startDate" @change="applyDateFilter"
                    class="rounded-xl border border-blue-200 px-3 py-2 text-sm bg-gray-50 text-[#0a2342] focus:outline-none focus:ring-2 focus:ring-orange-500" />
                <span class="text-blue-700">to</span>
                <input type="date" v-model="endDate" @change="applyDateFilter"
                    class="rounded-xl border border-blue-200 px-3 py-2 text-sm bg-gray-50 text-[#0a2342] focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-5 mx-5 mb-8">
            <div class="p-5 rounded-xl bg-gray-50 border border-blue-200 shadow-sm text-center">
                <div class="text-sm text-blue-700">Total Disbursements</div>
                <div class="mt-2 text-lg font-bold text-[#0a2342]">{{ summary.total_disbursements }}</div>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 border border-blue-200 shadow-sm text-center">
                <div class="text-sm text-blue-700">Total Amount</div>
                <div class="mt-2 text-lg font-bold text-[#0a2342]">{{ money(summary.total_amount) }}</div>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 border border-blue-200 shadow-sm text-center">
                <div class="text-sm text-blue-700">Average Loan Size</div>
                <div class="mt-2 text-lg font-bold text-[#0a2342]">{{ money(summary.average_loan_size) }}</div>
            </div>
            <div class="p-5 rounded-xl bg-gray-50 border border-blue-200 shadow-sm text-center">
                <div class="text-sm text-blue-700">Daily Average</div>
                <div class="mt-2 text-lg font-bold text-[#0a2342]">{{ money(summary.daily_average) }}</div>
            </div>
        </div>

        <!-- Daily Disbursement Cards -->
        <div class="mx-5 mt-5 mb-8">
            <h3 class="text-lg font-semibold text-[#0a2342] mb-4">Daily Disbursements</h3>
            <div v-if="Object.keys(daily_disbursements).length === 0"
                class="p-6 text-center text-orange-500 italic bg-gray-50 rounded-xl border border-blue-200">
                No daily disbursements found for the selected period.
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div v-for="(day, date) in daily_disbursements" :key="date"
                    class="p-4 rounded-xl bg-white border border-blue-200 shadow hover:shadow-lg transition text-center">
                    <div class="text-sm text-blue-700">{{ date }}</div>
                    <div class="mt-1 font-bold text-[#0a2342]">{{ day.count }} loans</div>
                    <div class="text-blue-800">Total: {{ money(day.total_amount) }}</div>
                </div>
            </div>
        </div>

        <!-- Loan Disbursement Table -->
        <div class="overflow-x-auto rounded-xl border border-blue-200 bg-white m-5 shadow-sm">
            <table v-if="disbursements.length > 0" class="min-w-full divide-y divide-blue-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342]">Loan Number</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342]">Member</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342]">Product</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342]">Disbursed Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342]">Disbursement Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-200">
                    <tr v-for="loan in disbursements" :key="loan.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2 text-sm text-[#0a2342]">{{ loan.loan_number }}</td>
                        <td class="px-4 py-2 text-sm text-[#0a2342]">{{ loan.member.first_name }} {{
        loan.member.last_name }}</td>
                        <td class="px-4 py-2 text-sm text-[#0a2342]">{{ loan.loanProduct.name }}</td>
                        <td class="px-4 py-2 text-sm text-[#0a2342]">{{ money(loan.disbursed_amount) }}</td>
                        <td class="px-4 py-2 text-sm text-[#0a2342]">{{ loan.disbursement_date }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="p-6 text-center text-orange-500 italic">
                No loan disbursements found for the selected period.
            </div>
        </div>


    </AppLayout>
</template>
