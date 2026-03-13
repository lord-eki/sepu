<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    dividend: Object,
    memberDividends: Array,
    summary: Object,
    year: Number,
    alreadyRun: Boolean
})

const selectedYear = ref(props.year)
const selectedMembers = ref([])

function loadYear() {
    router.get(route('schedule.dividend-payments'), {
        year: selectedYear.value
    })
}

function toggleMember(id) {

    if (selectedMembers.value.includes(id)) {
        selectedMembers.value =
            selectedMembers.value.filter(m => m !== id)
    } else {
        selectedMembers.value.push(id)
    }

}

function runDividend() {

    if (selectedMembers.value.length === 0) {
        alert("Select members to pay")
        return
    }

    if (!confirm("Process dividend payments?")) return

    axios.post(route('schedule.dividend-payments.run'), {

        dividend_id: props.dividend?.id,
        year: selectedYear.value,
        entries: selectedMembers.value

    }).then(() => {

        router.reload()

    })

}
</script>


<template>

    <AppLayout :breadcrumbs="[
        { title: 'Financial Schedules', href: route('schedule.index') },
        { title: 'Dividend Payments' }
    ]">

        <Head title="Dividend Payments" />

        <div class="p-6 space-y-6">

            <!-- HEADER -->

            <div class="flex justify-between items-center">

                <h1 class="text-2xl font-bold">
                    Dividend Payment Schedule
                </h1>

                <div class="flex gap-3">

                    <select v-model="selectedYear" class="border rounded p-2">

                        <option v-for="y in [2022, 2023, 2024, 2025, 2026]" :key="y" :value="y">
                            {{ y }}
                        </option>

                    </select>

                    <button @click="loadYear" class="bg-gray-800 text-white px-4 py-2 rounded">
                        Load
                    </button>

                </div>

            </div>



            <!-- WARNING -->

            <div v-if="alreadyRun" class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-4 rounded">
                Dividend payments already processed for this year.
            </div>



            <!-- SUMMARY -->

            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Dividend Rate</div>
                    <div class="text-xl font-bold">
                        {{ summary?.dividend_rate ?? 0 }}%
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Eligible Members</div>
                    <div class="text-xl font-bold">
                        {{ summary?.total_members ?? 0 }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Total Dividend</div>
                    <div class="text-xl font-bold">
                        KES {{ summary?.total_dividend ?? 0 }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Paid Members</div>
                    <div class="text-xl font-bold">
                        {{ summary?.paid_members ?? 0 }}
                    </div>
                </div>

                <div class="bg-white shadow rounded p-4">
                    <div class="text-gray-500 text-sm">Pending Members</div>
                    <div class="text-xl font-bold">
                        {{ summary?.pending_members ?? 0 }}
                    </div>
                </div>

            </div>



            <!-- MEMBERS TABLE -->

            <div class="bg-white shadow rounded-lg overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="p-3"></th>
                            <th class="p-3 text-left">Member</th>
                            <th class="p-3 text-left">Shares</th>
                            <th class="p-3 text-left">Deposits</th>
                            <th class="p-3 text-left">Dividend</th>
                            <th class="p-3 text-left">Status</th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr v-for="member in memberDividends" :key="member.id" class="border-t">

                            <td class="p-3">

                                <input
                                    type="checkbox"
                                    :disabled="member.status === 'paid'"
                                    :checked="selectedMembers.includes(member.id)"
                                    @click="toggleMember(member.id)"
                                />

                            </td>

                            <td class="p-3">
                                {{ member.member_name }}
                            </td>

                            <td class="p-3">
                                KES {{ member.share_capital ?? 0 }}
                            </td>

                            <td class="p-3">
                                KES {{ member.deposits ?? 0 }}
                            </td>

                            <td class="p-3 font-semibold">
                                KES {{ member.dividend_amount ?? 0 }}
                            </td>

                            <td class="p-3">

                                <span v-if="member.status === 'paid'" class="text-green-600 font-semibold">
                                    Paid
                                </span>

                                <span v-else class="text-orange-500">
                                    Pending
                                </span>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>



            <!-- ACTION -->

            <div class="flex gap-4">

                <button
                    @click="runDividend"
                    :disabled="alreadyRun"
                    class="bg-green-600 text-white px-6 py-2 rounded disabled:opacity-50"
                >
                    Pay Selected Members
                </button>

            </div>

        </div>

    </AppLayout>

</template>