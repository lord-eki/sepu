<template>
    <AppLayout>

        <Head title="Monthly Deposits" />

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Monthly Deposits</h1>
            <div class="flex gap-4 items-center mt-4 md:mt-0">
                <input type="month" v-model="selectedMonth" @change="fetchDeposits" class="border rounded px-3 py-2" />
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="alreadyRun"
                    @click="runDeposits">
                    Post Deposits
                </button>
            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow text-center">
                <div class="text-gray-500">Eligible Members</div>
                <div class="font-bold text-xl">{{ summary.total_eligible }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow text-center">
                <div class="text-gray-500">Already Done</div>
                <div class="font-bold text-xl">{{ summary.already_done }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow text-center">
                <div class="text-gray-500">Pending</div>
                <div class="font-bold text-xl">{{ summary.pending }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow text-center">
                <div class="text-gray-500">Total Amount (KES)</div>
                <div class="font-bold text-xl">{{ summary.total_amount | currency }}</div>
            </div>
        </div>

        <!-- DEPOSITS TABLE -->
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Member ID</th>
                        <th class="px-4 py-2 text-left text-gray-600">Member Name</th>
                        <th class="px-4 py-2 text-left text-gray-600">Account Number</th>
                        <th class="px-4 py-2 text-left text-gray-600">Amount (KES)</th>
                        <th class="px-4 py-2 text-left text-gray-600">Already Deposited</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="row in rows" :key="row.config_id"
                        :class="{ 'bg-red-50': row.already_deposited_this_month }">
                        <td class="px-4 py-2">{{ row.membership_id }}</td>
                        <td class="px-4 py-2">{{ row.member_name }}</td>
                        <td class="px-4 py-2">{{ row.account_number }}</td>
                        <td class="px-4 py-2">{{ row.amount | currency }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-white text-sm"
                                :class="row.already_deposited_this_month ? 'bg-red-500' : 'bg-green-500'">
                                {{ row.already_deposited_this_month ? 'Yes' : 'No' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SUCCESS / ERROR NOTIFICATIONS -->
        <Notification v-if="flash.success" type="success" :message="flash.success" />
        <Notification v-if="flash.error" type="error" :message="flash.error" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Notification from '@/components/Notification.vue'

const selectedMonth = ref(new Date().toISOString().slice(0, 7))

const flash = reactive({ success: '', error: '' })

// Form data from backend
const { data: deposits, setData } = useForm({
    month: selectedMonth.value,
    entries: []
})

const rows = ref([])
const summary = reactive({
    total_eligible: 0,
    already_done: 0,
    pending: 0,
    total_amount: 0
})

const alreadyRun = ref(false)

const fetchDeposits = () => {
    router.get(route('schedule.monthly-deposit'), { month: selectedMonth.value }, {
        preserveState: true,
        onSuccess: (page) => {
            rows.value = page.props.rows
            summary.total_eligible = page.props.summary.total_eligible
            summary.already_done = page.props.summary.already_done
            summary.pending = page.props.summary.pending
            summary.total_amount = page.props.summary.total_amount
            alreadyRun.value = page.props.alreadyRun
        }
    })
}

const runDeposits = () => {
    if (!confirm('Are you sure you want to post monthly deposits?')) return

    const entries = rows.value.filter(r => !r.already_deposited_this_month)
        .map(r => ({ member_id: r.member_id, account_id: r.account_id, amount: r.amount }))

    router.post(route('schedule.run-monthly-deposits'), { month: selectedMonth.value, entries }, {
        onSuccess: page => {
            flash.success = page.props.flash.success
            fetchDeposits()
        },
        onError: page => flash.error = Object.values(page.props.errors || {}).join(', ')
    })
}

onMounted(() => fetchDeposits())

// Filters
const currency = (value: number) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(value)
</script>