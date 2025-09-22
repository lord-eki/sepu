<template>
    <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }, { title: 'Statement' }]">

        <Head title="Account Statement" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">

                    <!-- Statement Header -->
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold mb-1">Account Statement</h2>
                            <p class="text-gray-700">Name: {{ account.member?.first_name }} {{ account.member?.last_name
                                }}</p>
                            <p class="text-gray-700">Account No: {{ account.account_number }}</p>
                            <p class="text-gray-700">Balance: KES {{ formatCurrency(account.balance) }}</p>
                            <p class="text-gray-700">Period: {{ formatDate(period.from) }} - {{ formatDate(period.to) }}
                            </p>
                        </div>
                        <div>

                            <a :href="route('my-accounts.statement.pdf', { account: account.id, from: period.from, to: period.to })"
                                target="_blank" rel="noopener"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
                                <Download class="w-4 h-4" /> Download
                            </a>


                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm text-left text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase font-medium text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Date & Time</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Amount</th>
                                    <th class="px-4 py-3">Balance</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">{{ formatDateTime(tx.created_at) }}</td>
                                    <td class="px-4 py-3 capitalize">{{ tx.transaction_type.replace('_', ' ') }}</td>
                                    <td class="px-4 py-3 font-medium">KES {{ formatCurrency(tx.amount) }}</td>
                                    <td class="px-4 py-3 font-medium">KES {{ formatCurrency(tx.balance_after) }}</td>
                                    <td class="px-4 py-3 capitalize">{{ tx.status }}</td>
                                    <td class="px-4 py-3">{{ tx.description || '-' }}</td>
                                </tr>
                                <tr v-if="!transactions.length">
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500">No transactions in this
                                        period.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Download } from 'lucide-vue-next'

const props = defineProps<{
    account: any
    transactions: any[]
    period: { from: string, to: string }
}>()

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-KE').format(amount)

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-KE')

const formatDateTime = (date: string) =>
    new Date(date).toLocaleString('en-KE', { hour12: true })
</script>