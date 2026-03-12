<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    rows: Array,
    summary: Object,
    month: String,
    alreadyRun: Boolean
})

const selectedMonth = ref(props.month)
const previewData = ref(null)
const showPreview = ref(false)

function loadMonth() {
    router.get(route('schedule.loan-repayment'), {
        month: selectedMonth.value
    })
}

function previewSchedule() {

    axios.post(route('schedule.loan-repayment.preview'), {
        month: selectedMonth.value
    }).then(res => {

        previewData.value = res.data
        showPreview.value = true

    })

}

function runSchedule() {

    if (!confirm("Run Loan Repayment Schedule?")) return

    axios.post(route('schedule.loan-repayment.run'), {
        month: selectedMonth.value,
        entries: previewData.value.preview
    }).then(() => {

        router.reload()

    })
}
</script>

<template>

    <AppLayout :breadcrumbs="[
        { title: 'Financial Schedules', href: route('schedule.index') },
        { title: 'Loan Repayments' }
    ]">

        <Head title="Loan Repayment Schedule" />

        <div class="p-6 space-y-6">

            <!-- HEADER -->

            <div class="flex justify-between items-center">

                <h1 class="text-2xl font-bold">
                    Loan Repayment Schedule
                </h1>

                <div class="flex gap-3">

                    <input type="month" v-model="selectedMonth" class="border rounded p-2" />

                    <button @click="loadMonth" class="bg-gray-800 text-white px-4 py-2 rounded">
                        Load
                    </button>

                </div>

            </div>


            <!-- WARNING -->

            <div v-if="alreadyRun" class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-4 rounded">
                Loan repayment schedule already executed for this month.
            </div>



            <!-- SUMMARY CARDS -->

            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Active Loans</div>
                    <div class="text-xl font-bold">
                        {{ summary.total_loans }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Total Principal</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_principal }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Total Interest</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_interest }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Total Instalments</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_instalment }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Outstanding Balance</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_balance }}
                    </div>
                </div>

            </div>



            <!-- LOANS TABLE -->

            <div class="bg-white shadow rounded-lg overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-100">

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

                        <tr v-for="loan in rows" :key="loan.loan_id" class="border-t">

                            <td class="p-3">
                                {{ loan.loan_number }}
                            </td>

                            <td class="p-3">
                                {{ loan.member_name }}
                            </td>

                            <td class="p-3">
                                {{ loan.loan_product }}
                            </td>

                            <td class="p-3">
                                KES {{ loan.monthly_principal }}
                            </td>

                            <td class="p-3">
                                KES {{ loan.monthly_interest }}
                            </td>

                            <td class="p-3 font-semibold">
                                KES {{ loan.total_instalment }}
                            </td>

                            <td class="p-3">
                                KES {{ loan.loan_balance }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>



            <!-- ACTIONS -->

            <div class="flex gap-4">

                <button @click="previewSchedule" :disabled="alreadyRun"
                    class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50">
                    Preview Repayments
                </button>

            </div>



            <!-- PREVIEW MODAL -->

            <div v-if="showPreview" class="fixed inset-0 bg-black/40 flex items-center justify-center">

                <div class="bg-white rounded-xl w-[800px] p-6">

                    <h2 class="text-lg font-semibold mb-4">
                        Loan Repayment Preview
                    </h2>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2">Loan</th>
                                <th class="p-2">Member</th>
                                <th class="p-2">Principal</th>
                                <th class="p-2">Interest</th>
                                <th class="p-2">Total</th>
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

                                <td class="p-2">
                                    KES {{ item.principal }}
                                </td>

                                <td class="p-2">
                                    KES {{ item.interest }}
                                </td>

                                <td class="p-2 font-semibold">
                                    KES {{ item.total }}
                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <div class="mt-4 text-right font-semibold">
                        Total: KES {{ previewData.total_amount }}
                    </div>


                    <div class="flex justify-end gap-3 mt-6">

                        <button @click="showPreview = false" class="px-4 py-2 border rounded">
                            Cancel
                        </button>

                        <button @click="runSchedule" class="bg-green-600 text-white px-4 py-2 rounded">
                            Run Repayments
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>