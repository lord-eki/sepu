<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Users, Wallet, PiggyBank, FileText } from 'lucide-vue-next'

const breadcrumbs = [
    { title: 'Reports', href: '/reports' },
    { title: 'Member Reports' },
]
</script>

<template>

    <Head title="Member Reports" />

    <AppLayout title="Member Reports" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mb-8 mx-5 mt-5">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Member Reports
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Membership, savings, shares and loan analytics
            </p>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 m-6">
            <Link :href="route('reports.members.register')" class="rounded-3xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-lg transition">
            <Users class="h-8 w-8 text-orange-500 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Member Register
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                All registered members
            </p>
            </Link>

            <Link :href="route('reports.members.shares')" class="rounded-3xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-lg transition">
            <Wallet class="h-8 w-8 text-blue-500 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Member Shares
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Share capital per member
            </p>
            </Link>

            <Link :href="route('reports.members.savings')" class="rounded-3xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-lg transition">
            <PiggyBank class="h-8 w-8 text-green-500 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Member Savings
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Savings balances & activity
            </p>
            </Link>

            <Link :href="route('reports.members.loans')" class="rounded-3xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-lg transition">
            <FileText class="h-8 w-8 text-purple-500 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Member Loans
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Loans & outstanding balances
            </p>
            </Link>
        </div>
    </AppLayout>
</template>
