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
    router.get(route('schedule.monthly-deposit'), {
        month: selectedMonth.value
    })
}

function previewSchedule() {

    axios.post(route('schedule.monthly-deposit.preview'), {
        month: selectedMonth.value
    }).then(res => {

        previewData.value = res.data
        showPreview.value = true

    })
}

function runSchedule() {

    if (!confirm("Run Monthly Deposit Schedule?")) return

    axios.post(route('schedule.monthly-deposit.run'), {
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
        { title: 'Monthly Deposits' }
    ]">

        <Head title="Monthly Deposits Schedule" />

        <div class="p-6 space-y-6">


            <!-- PAGE HEADER -->

            <div class="flex justify-between items-center">

                <h1 class="text-2xl font-bold">
                    Monthly Deposits Schedule
                </h1>

                <div class="flex gap-3">

                    <input type="month" v-model="selectedMonth" class="border rounded p-2" />

                    <button @click="loadMonth" class="bg-gray-800 text-white px-4 py-2 rounded">
                        Load
                    </button>

                </div>

            </div>



            <!-- WARNING IF ALREADY RUN -->

            <div v-if="alreadyRun" class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-4 rounded">
                This schedule has already been executed for this month.
            </div>



            <!-- SUMMARY CARDS -->

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Eligible Members</div>
                    <div class="text-xl font-bold">{{ summary.total_members }}</div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Already Deposited</div>
                    <div class="text-xl font-bold">{{ summary.already_deposited }}</div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Pending Deposits</div>
                    <div class="text-xl font-bold">{{ summary.pending_deposits }}</div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Total Amount</div>
                    <div class="text-xl font-bold">
                        KES {{ summary.total_amount }}
                    </div>
                </div>

            </div>



            <!-- MEMBERS TABLE -->

            <div class="bg-white shadow rounded-lg overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-100">

                        <tr>
                            <th class="p-3 text-left">Member ID</th>
                            <th class="p-3 text-left">Member Name</th>
                            <th class="p-3 text-left">Account</th>
                            <th class="p-3 text-left">Contribution</th>
                            <th class="p-3 text-left">Deposited</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr v-for="member in rows" :key="member.member_id" class="border-t">

                            <td class="p-3">
                                {{ member.membership_number }}
                            </td>

                            <td class="p-3">
                                {{ member.member_name }}
                            </td>

                            <td class="p-3">
                                {{ member.account_number }}
                            </td>

                            <td class="p-3">
                                KES {{ member.monthly_contribution }}
                            </td>

                            <td class="p-3">

                                <span v-if="member.already_deposited" class="text-green-600 font-semibold">
                                    Yes
                                </span>

                                <span v-else class="text-red-500">
                                    No
                                </span>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>



            <!-- ACTION BUTTONS -->

            <div class="flex gap-4">

                <button @click="previewSchedule" :disabled="alreadyRun"
                    class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50">
                    Preview Schedule
                </button>

            </div>



            <!-- PREVIEW MODAL -->

            <div v-if="showPreview" class="fixed inset-0 bg-black/40 flex items-center justify-center">

                <div class="bg-white rounded-xl w-[700px] p-6">

                    <h2 class="text-lg font-semibold mb-4">
                        Schedule Preview
                    </h2>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2">Member</th>
                                <th class="p-2">Amount</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr v-for="item in previewData.preview" :key="item.member_id" class="border-t">

                                <td class="p-2">
                                    {{ item.member_name }}
                                </td>

                                <td class="p-2">
                                    KES {{ item.amount }}
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
                            Run Schedule
                        </button>

                    </div>

                </div>

            </div>


        </div>

    </AppLayout>
</template>