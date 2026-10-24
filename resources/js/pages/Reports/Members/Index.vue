<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, PiggyBank, Users, Wallet } from 'lucide-vue-next';

const breadcrumbs = [{ title: 'Reports', href: '/reports' }, { title: 'Member Reports' }];
</script>

<template>
    <Head title="Member Reports" />

    <AppLayout title="Member Reports" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mx-5 mt-5 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Member Reports</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Membership, savings, shares and loan analytics</p>
        </div>

        <!-- Reports Grid -->
        <div class="m-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
            <Link
                :href="route('reports.members.register')"
                class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <Users class="mb-4 h-8 w-8 text-orange-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Member Register</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">All registered members</p>
            </Link>

            <Link
                :href="route('reports.members.shares')"
                class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <Wallet class="mb-4 h-8 w-8 text-blue-900" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Member Shares</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Share capital per member</p>
            </Link>

            <Link
                :href="route('reports.members.savings')"
                class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <PiggyBank class="mb-4 h-8 w-8 text-orange-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Member Savings</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Savings balances & activity</p>
            </Link>

            <Link
                :href="route('reports.members.loans')"
                class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <FileText class="mb-4 h-8 w-8 text-blue-900" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Member Loans</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Loans & outstanding balances</p>
            </Link>
        </div>
    </AppLayout>
</template>
