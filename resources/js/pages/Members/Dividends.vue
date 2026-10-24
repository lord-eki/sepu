<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, CheckCircle, DollarSign } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    member: any;
    dividends: any[];
}>();

// Smart stats
const totalDividends = computed(() => props.dividends.length);
const totalAmount = computed(() => props.dividends.reduce((sum, d) => sum + Number(d.dividend_amount || 0), 0));
const formattedTotalAmount = computed(() => Number(totalAmount.value).toLocaleString());
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/my-dividends' }]">
        <Head title="Dividends" />

        <div
            class="min-h-screen space-y-8 bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-6 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
        >
            <!-- ================= HEADER ================= -->

            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-orange-600 shadow-2xl">
                <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-orange-400/20 blur-3xl"></div>

                <div class="absolute bottom-0 -left-20 h-56 w-56 rounded-full bg-cyan-500/20 blur-3xl"></div>

                <div class="relative flex flex-col gap-8 p-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex gap-5">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 backdrop-blur">
                            <DollarSign class="h-8 w-8 text-orange-300" />
                        </div>

                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-white">My Dividends</h1>

                            <p class="mt-2 max-w-2xl leading-relaxed text-slate-200">
                                View your dividend history, annual earnings, payment status and declared distributions.
                            </p>
                        </div>
                    </div>

                    <div class="inline-flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-6 py-4 backdrop-blur">
                        <div class="h-3 w-3 animate-pulse rounded-full bg-emerald-400"></div>

                        <div>
                            <p class="text-xs tracking-widest text-slate-300 uppercase">Account</p>

                            <p class="font-semibold text-white">Dividend Centre</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================= SUMMARY ================= -->

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <!-- Card -->

                <Card class="rounded-3xl border-0 bg-white/80 shadow-lg backdrop-blur transition hover:shadow-xl dark:bg-slate-900/70">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Dividends</p>

                                <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ totalDividends }}
                                </h2>
                            </div>

                            <div class="rounded-xl bg-slate-900 p-3 text-white dark:bg-orange-500">
                                <DollarSign class="h-5 w-5" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card -->

                <Card class="rounded-3xl border-0 bg-white/80 shadow-lg backdrop-blur transition hover:shadow-xl dark:bg-slate-900/70">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Amount</p>

                                <h2 class="mt-2 text-2xl font-bold text-orange-600">KES {{ formattedTotalAmount }}</h2>
                            </div>

                            <div class="rounded-xl bg-orange-100 p-3 dark:bg-orange-500/20">
                                <Calendar class="h-5 w-5 text-orange-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card -->

                <Card class="rounded-3xl border-0 bg-white/80 shadow-lg backdrop-blur transition hover:shadow-xl dark:bg-slate-900/70">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Status</p>

                                <h2 class="mt-2 text-xl font-bold" :class="props.dividends.length ? 'text-emerald-600' : 'text-slate-500'">
                                    {{ props.dividends.length ? 'Paid Dividends' : 'No Records' }}
                                </h2>
                            </div>

                            <div class="rounded-xl bg-emerald-100 p-3 dark:bg-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </section>

            <!-- ================= TABLE ================= -->

            <Card class="overflow-hidden rounded-3xl border-0 bg-white/90 shadow-xl backdrop-blur dark:bg-slate-900/70">
                <CardHeader class="border-b border-slate-200 dark:border-slate-700">
                    <CardTitle class="text-slate-800 dark:text-white"> Dividend History </CardTitle>
                </CardHeader>

                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-100 dark:bg-slate-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Year</th>

                                    <th class="px-6 py-4 text-left text-sm font-semibold">Declared</th>

                                    <th class="px-6 py-4 text-left text-sm font-semibold">Amount</th>

                                    <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="d in props.dividends" :key="d.id" class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-white">
                                        {{ d.dividend?.dividend_year || '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                        {{ d.dividend?.approval_date ? new Date(d.dividend.approval_date).toLocaleDateString() : '-' }}
                                    </td>

                                    <td class="px-6 py-4 font-semibold text-orange-600">KES {{ Number(d.dividend_amount || 0).toLocaleString() }}</td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="{
                                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400': d.status === 'paid',
                                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300': d.status === 'pending',
                                                'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400': d.status === 'rejected',
                                                'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300': !d.status,
                                            }"
                                        >
                                            {{ d.status || 'Unknown' }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- EMPTY -->

                                <tr v-if="!props.dividends.length">
                                    <td colspan="4">
                                        <div class="flex flex-col items-center py-20">
                                            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                                <DollarSign class="h-10 w-10 text-slate-400" />
                                            </div>

                                            <h3 class="mt-5 text-lg font-semibold text-slate-700 dark:text-slate-200">No Dividends Found</h3>

                                            <p class="mt-2 text-slate-500 dark:text-slate-400">Dividend records will appear here once declared.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
