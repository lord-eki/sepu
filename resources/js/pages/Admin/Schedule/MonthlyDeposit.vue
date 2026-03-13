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
    router.get(route('schedule.monthly-deposit'), {
        month: selectedMonth.value,
    });
}

function previewSchedule() {
    axios
        .post(route('schedule.monthly-deposit.preview'), {
            month: selectedMonth.value,
        })
        .then((res) => {
            previewData.value = res.data;
            showPreview.value = true;
        });
}

function runSchedule() {
    if (!previewData.value) return;

    if (!confirm('Run Monthly Deposit Schedule?')) return;

    router.post(
        route('schedule.monthly-deposit.run'),
        {
            month: selectedMonth.value,
            entries: previewData.value.preview,
        },
        {
            preserveState: false, // refresh the page data after redirect
            onSuccess: () => {
                showPreview.value = false; // close the preview modal
            },
            onError: (errors) => {
                console.error(errors);
            },
        }
    );
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Financial Schedules', href: route('schedule.index') }, { title: 'Monthly Deposits' }]">
        <Head title="Monthly Deposits Schedule" />

        <div class="space-y-8 p-6">
            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Monthly Deposits Schedule</h1>
                    <p class="text-sm text-gray-500">Run automated monthly member contributions</p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="month" v-model="selectedMonth" class="rounded-lg border px-3 py-2 shadow-sm" />

                    <button @click="loadMonth" class="rounded-lg bg-blue-900 px-5 py-2 text-white shadow hover:bg-blue-800">Load Data</button>
                </div>
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
                        <span class="text-gray-500">Eligible Members</span>
                        <div class="font-semibold">{{ summary?.total_eligible ?? 0 }}</div>
                    </div>

                    <div>
                        <span class="text-gray-500">Pending Deposits</span>
                        <div class="font-semibold">{{ summary?.pending ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- SUMMARY CARDS -->

            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Eligible Members</div>
                    <div class="text-2xl font-bold text-blue-900">
                        {{ summary?.total_eligible ?? 0 }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Already Deposited</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ summary?.already_done ?? 0 }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Pending Deposits</div>
                    <div class="text-2xl font-bold text-orange-500">
                        {{ summary?.pending ?? 0 }}
                    </div>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-lg">
                    <div class="text-sm text-gray-500">Total Amount</div>
                    <div class="text-2xl font-bold text-blue-900">
                        KES {{ summary?.total_amount ?? 0 }}
                    </div>
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
                        Preview Schedule
                    </button>
                </div>
            </div>

            <!-- MEMBERS TABLE -->

            <div class="overflow-hidden rounded-xl border bg-white shadow-xl">
                <div class="border-b p-5 font-semibold text-gray-700">Member Contribution Status</div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Member ID</th>
                                <th class="p-3 text-left">Member Name</th>
                                <th class="p-3 text-left">Account</th>
                                <th class="p-3 text-left">Contribution</th>
                                <th class="p-3 text-left">Deposited</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="member in rows" :key="member.member_id" class="border-t hover:bg-gray-50">
                                <td class="p-3 font-medium">
                                    {{ member.membership_id }}
                                </td>

                                <td class="p-3">
                                    {{ member.member_name }}
                                </td>

                                <td class="p-3">
                                    {{ member.account_number }}
                                </td>

                                <td class="p-3 font-semibold">KES {{ member.monthly_contribution_amount }}</td>

                                <td class="p-3">
                                    <span v-if="member.already_deposited_this_month" class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                                        Deposited
                                    </span>

                                    <span v-else class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-600"> Pending </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PREVIEW MODAL -->

            <div v-if="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="max-h-[80vh] w-[720px] overflow-auto rounded-xl bg-white p-6 shadow-xl">
                    <h2 class="mb-4 text-lg font-semibold">Schedule Preview</h2>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Member</th>
                                <th class="p-2 text-left">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in previewData.preview" :key="item.member_id" class="border-t">
                                <td class="p-2">
                                    {{ item.member_name }}
                                </td>

                                <td class="p-2 font-semibold">KES {{ item.amount }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 text-right text-lg font-semibold">Total: KES {{ previewData.total_amount }}</div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showPreview = false" class="rounded-lg border px-4 py-2">Cancel</button>

                        <button @click="runSchedule" class="rounded-lg bg-green-600 px-5 py-2 text-white hover:bg-green-700">Run Schedule</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
