<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    member: any;
    transactions: any[];
    period: { from: string; to: string };
}>();

const fromDate = ref(props.period.from);
const toDate = ref(props.period.to);

const applyFilter = () => {
    router.get(route('my-transactions.statement'), {
        from: fromDate.value,
        to: toDate.value,
    });
};

const formatCurrency = (amount: number) => new Intl.NumberFormat('en-KE').format(amount);

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-KE');

const formatDateTime = (date: string) => new Date(date).toLocaleString('en-KE');

const amountColor = (tx: any) => {
    if (tx.transaction_type.includes('deposit')) return 'text-green-600';
    if (tx.transaction_type.includes('withdraw')) return 'text-red-600';
    return 'text-gray-800';
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'My Transactions', href: '/my-transactions' }, { title: 'Statement' }]">
        <Head title="Transaction Statement" />

        <div class="min-h-screen space-y-6 bg-gray-50 p-6">
            <!-- HEADER -->
            <div class="flex justify-between rounded-2xl bg-gradient-to-r from-[#0a2342] via-blue-900 to-orange-500 p-6 text-white shadow-lg">
                <div>
                    <h1 class="text-xl font-bold">TRANSACTION STATEMENT</h1>
                    <p>{{ member.first_name }} {{ member.last_name }}</p>
                    <p class="mt-1 text-sm">{{ formatDate(fromDate) }} → {{ formatDate(toDate) }}</p>
                </div>

                <a
                    :href="route('my-transactions.statement.pdf', { from: fromDate, to: toDate })"
                    target="_blank"
                    class="flex h-fit items-center gap-2 rounded-lg bg-white px-4 py-2 text-[#0a2342]"
                >
                    <Download class="h-4 w-4" />
                    Download PDF
                </a>
            </div>

            <!-- FILTER -->
            <div class="flex gap-4 rounded-xl bg-white p-4 shadow">
                <input type="date" v-model="fromDate" class="rounded border px-3 py-2" />
                <input type="date" v-model="toDate" class="rounded border px-3 py-2" />

                <button @click="applyFilter" class="rounded bg-[#0a2342] px-4 py-2 text-white">Apply</button>
            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto rounded-2xl bg-white shadow">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3">Type</th>
                            <th class="p-3 text-right">Amount</th>
                            <th class="p-3 text-right">Balance</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="tx in transactions" :key="tx.id" class="border-b">
                            <td class="p-3">{{ formatDateTime(tx.created_at) }}</td>

                            <td class="p-3 capitalize">
                                {{ tx.transaction_type }}
                            </td>

                            <td class="p-3 text-right font-semibold" :class="amountColor(tx)">KES {{ formatCurrency(tx.amount) }}</td>

                            <td class="p-3 text-right">KES {{ formatCurrency(tx.balance_after) }}</td>

                            <!-- STATUS -->
                            <td class="p-3 text-center">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-700': tx.status === 'completed',
                                        'bg-yellow-100 text-yellow-700': tx.status === 'pending',
                                        'bg-red-100 text-red-700': tx.status === 'failed',
                                        'bg-gray-100 text-gray-700': !tx.status,
                                    }"
                                >
                                    {{ tx.status || 'completed' }}
                                </span>
                            </td>
                        </tr>

                        <tr v-if="!transactions.length">
                            <td colspan="5" class="p-6 text-center text-gray-500">No transactions found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
