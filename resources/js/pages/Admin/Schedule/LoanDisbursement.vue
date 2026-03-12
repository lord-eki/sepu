<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    loans: Array,
    summary: Object,
    filters: Object
})

const selectedLoans = ref([])

const filterDateFrom = ref(props.filters.date_from)
const filterDateTo = ref(props.filters.date_to)

function applyFilters() {

    router.get(route('schedule.loan-disbursement'), {
        date_from: filterDateFrom.value,
        date_to: filterDateTo.value
    })

}

function toggleLoan(id) {

    if (selectedLoans.value.includes(id)) {
        selectedLoans.value =
            selectedLoans.value.filter(l => l !== id)
    } else {
        selectedLoans.value.push(id)
    }

}

function runDisbursement() {

    if (selectedLoans.value.length === 0) {
        alert("Select loans to disburse")
        return
    }

    if (!confirm("Disburse selected loans?")) return

    axios.post(route('schedule.loan-disbursement.run'), {

        loan_ids: selectedLoans.value,
        year: new Date().getFullYear()

    }).then(() => {

        router.reload()

    })

}
</script>


<template>

    <AppLayout :breadcrumbs="[
        { title: 'Financial Schedules', href: route('schedule.index') },
        { title: 'Loan Disbursement' }
    ]">

        <Head title="Loan Disbursement Schedule" />

        <div class="p-6 space-y-6">

            <!-- PAGE HEADER -->

            <h1 class="text-2xl font-bold">
                Loan Disbursement Processor
            </h1>


            <!-- SUMMARY -->

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Approved Loans</div>
                    <div class="text-xl font-bold">
                        {{ summary.total_loans }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Total Approved</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_approved }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Processing Fees</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_processing_fee }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Net Disbursement</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_net }}
                    </div>
                </div>

            </div>



            <!-- FILTERS -->

            <div class="flex gap-4 items-end">

                <div>

                    <label class="text-sm text-gray-600">
                        From
                    </label>

                    <input type="date" v-model="filterDateFrom" class="border rounded p-2" />

                </div>


                <div>

                    <label class="text-sm text-gray-600">
                        To
                    </label>

                    <input type="date" v-model="filterDateTo" class="border rounded p-2" />

                </div>


                <button @click="applyFilters" class="bg-gray-800 text-white px-4 py-2 rounded">
                    Filter
                </button>

            </div>



            <!-- LOANS TABLE -->

            <div class="bg-white shadow rounded-lg overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-100">

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

                        <tr v-for="loan in loans" :key="loan.id" class="border-t">

                            <td class="p-3">

                                <input type="checkbox" :checked="selectedLoans.includes(loan.id)"
                                    @click="toggleLoan(loan.id)" />

                            </td>

                            <td class="p-3">
                                {{ loan.loan_number }}
                            </td>

                            <td class="p-3">
                                {{ loan.member_name }}
                            </td>

                            <td class="p-3">
                                {{ loan.product }}
                            </td>

                            <td class="p-3">
                                KES {{ loan.approved_amount }}
                            </td>

                            <td class="p-3">
                                KES {{ loan.processing_fee }}
                            </td>

                            <td class="p-3">
                                KES {{ loan.insurance_fee }}
                            </td>

                            <td class="p-3 font-semibold">
                                KES {{ loan.net_disbursement }}
                            </td>

                            <td class="p-3">
                                {{ loan.approval_date }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>



            <!-- ACTION BUTTONS -->

            <div class="flex gap-4">

                <button @click="runDisbursement" class="bg-green-600 text-white px-6 py-2 rounded">
                    Disburse Selected Loans
                </button>

                <a :href="route('schedule.loan-disbursement.export')" class="bg-blue-600 text-white px-6 py-2 rounded">
                    Export CSV
                </a>

            </div>


        </div>

    </AppLayout>

</template>