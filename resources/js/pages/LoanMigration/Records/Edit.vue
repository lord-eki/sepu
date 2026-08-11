<script setup lang="ts">
import { computed, reactive } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Batch {
    id: number
    batch_number: string
    description?: string | null
    status: string
}

interface LoanMigrationRecord {
    id: number
    member_id?: number | null
    legacy_loan_number?: string | null
    loan_number?: string | null
    loan_product_id?: number | null
    loan_product?: string | null
    original_amount?: number | string | null
    amount_paid?: number | string | null
    outstanding_balance?: number | string | null
    interest_rate?: number | string | null
    repayment_period?: number | string | null
    disbursement_date?: string | null
    maturity_date?: string | null
    loan_status?: string | null
    source_reference?: string | null
    remarks?: string | null
}

interface Member {
    id: number
    member_number: string
    name: string
    phone?: string | null
}

interface LoanProduct {
    id: number
    name: string
    code?: string | null
    interest_rate?: number | string | null
}

interface Props {
    batch: Batch
    record: LoanMigrationRecord
    members?: Member[]
    loanProducts?: LoanProduct[]
}

const props = defineProps<Props>()

const form = useForm({
    member_id: props.record.member_id
        ? String(props.record.member_id)
        : '',

    legacy_loan_number: props.record.legacy_loan_number ?? '',

    loan_number: props.record.loan_number ?? '',

    loan_product_id: props.record.loan_product_id
        ? String(props.record.loan_product_id)
        : '',

    loan_product: props.record.loan_product ?? '',

    original_amount: props.record.original_amount !== null &&
        props.record.original_amount !== undefined
        ? String(props.record.original_amount)
        : '',

    amount_paid: props.record.amount_paid !== null &&
        props.record.amount_paid !== undefined
        ? String(props.record.amount_paid)
        : '',

    outstanding_balance: props.record.outstanding_balance !== null &&
        props.record.outstanding_balance !== undefined
        ? String(props.record.outstanding_balance)
        : '',

    interest_rate: props.record.interest_rate !== null &&
        props.record.interest_rate !== undefined
        ? String(props.record.interest_rate)
        : '',

    repayment_period: props.record.repayment_period !== null &&
        props.record.repayment_period !== undefined
        ? String(props.record.repayment_period)
        : '',

    disbursement_date: props.record.disbursement_date ?? '',

    maturity_date: props.record.maturity_date ?? '',

    loan_status: props.record.loan_status ?? 'active',

    source_reference: props.record.source_reference ?? '',

    remarks: props.record.remarks ?? '',
})

const search = reactive({
    member: '',
})

const selectedMember = computed(() => {
    if (!form.member_id) {
        return null
    }

    return props.members?.find(
        member => String(member.id) === String(form.member_id)
    ) ?? null
})

const filteredMembers = computed(() => {
    const term = search.member.trim().toLowerCase()

    if (!term) {
        return props.members ?? []
    }

    return (props.members ?? []).filter(member => {
        return (
            member.name.toLowerCase().includes(term) ||
            member.member_number.toLowerCase().includes(term) ||
            (member.phone ?? '').toLowerCase().includes(term)
        )
    })
})

const calculatedOutstanding = computed(() => {
    const original = Number(form.original_amount || 0)
    const paid = Number(form.amount_paid || 0)

    if (Number.isNaN(original) || Number.isNaN(paid)) {
        return 0
    }

    return Math.max(original - paid, 0)
})

const amountError = computed(() => {
    const original = Number(form.original_amount || 0)
    const paid = Number(form.amount_paid || 0)

    if (paid > original && original > 0) {
        return 'Amount paid cannot exceed the original loan amount.'
    }

    return null
})

const selectMember = (member: Member) => {
    form.member_id = String(member.id)
    search.member = `${member.member_number} — ${member.name}`
}

const clearMember = () => {
    form.member_id = ''
    search.member = ''
}

const useCalculatedOutstanding = () => {
    form.outstanding_balance = calculatedOutstanding.value.toFixed(2)
}

const selectLoanProduct = () => {
    const product = props.loanProducts?.find(
        item => String(item.id) === String(form.loan_product_id)
    )

    if (!product) {
        return
    }

    form.loan_product = product.name

    if (
        product.interest_rate !== null &&
        product.interest_rate !== undefined &&
        !form.interest_rate
    ) {
        form.interest_rate = String(product.interest_rate)
    }
}

const submit = () => {
    if (amountError.value) {
        return
    }

    if (!form.outstanding_balance) {
        form.outstanding_balance = calculatedOutstanding.value.toFixed(2)
    }

    form.put(
        route(
            'loan-migration.records.update',
            [
                props.batch.id,
                props.record.id,
            ]
        ),
        {
            preserveScroll: true,
        }
    )
}

const formatCurrency = (amount: number | string | null | undefined) => {
    const value = Number(amount ?? 0)

    if (Number.isNaN(value)) {
        return 'KES 0.00'
    }

    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value)
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Loan Migration',
                href: route('loan-migration.index')
            },
            {
                title: 'Migration Records',
                href: route('loan-migration.records.index', batch.id)
            },
            {
                title: 'Edit Record'
            }
        ]"
    >
        <Head title="Edit Migration Record" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-6">

            <!-- Breadcrumb -->
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <Link
                    :href="route('loan-migration.index')"
                    class="transition hover:text-blue-600 dark:hover:text-blue-400"
                >
                    Loan Migration
                </Link>

                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>

                <Link
                    :href="route('loan-migration.show', batch.id)"
                    class="transition hover:text-blue-600 dark:hover:text-blue-400"
                >
                    {{ batch.batch_number }}
                </Link>

                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    />
                </svg>

                <span class="font-medium text-slate-700 dark:text-slate-300">
                    Edit Loan Record
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-8.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Edit Loan Record
                            </h1>

                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                {{ batch.batch_number }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Correct or update the migrated legacy loan information.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('loan-migration.records.show', [
                        batch.id,
                        record.id,
                    ])"
                    class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m7 7H3"
                        />
                    </svg>

                    Back to Record
                </Link>
            </div>

            <!-- Editing Notice -->
            <div class="flex gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-950/20">
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"
                    />
                </svg>

                <div>
                    <p class="text-xs font-bold text-blue-800 dark:text-blue-300">
                        Editing migrated data
                    </p>

                    <p class="mt-1 text-xs leading-5 text-blue-700 dark:text-blue-400">
                        Make corrections based on the original loan documentation.
                        Changes may trigger validation again before the record can
                        proceed through the migration workflow.
                    </p>
                </div>
            </div>

            <form
                class="space-y-6"
                @submit.prevent="submit"
            >

                <!-- Member -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Member Information
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Confirm that the loan is linked to the correct member.
                        </p>
                    </div>

                    <div class="p-5">
                        <label class="mb-2 block text-xs font-semibold">
                            Member
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <div class="relative">
                                <svg
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"
                                    />
                                </svg>

                                <input
                                    v-model="search.member"
                                    type="text"
                                    :placeholder="selectedMember ? `${selectedMember.member_number} — ${selectedMember.name}` : 'Search member number or name...'"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-9 pr-10 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />

                                <button
                                    v-if="form.member_id"
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    @click="clearMember"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <div
                                v-if="search.member && !selectedMember"
                                class="absolute z-20 mt-2 max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                            >
                                <button
                                    v-for="member in filteredMembers"
                                    :key="member.id"
                                    type="button"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800"
                                    @click="selectMember(member)"
                                >
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        {{ member.name.charAt(0).toUpperCase() }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-semibold">
                                            {{ member.name }}
                                        </p>

                                        <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">
                                            {{ member.member_number }}
                                            <span v-if="member.phone">
                                                · {{ member.phone }}
                                            </span>
                                        </p>
                                    </div>
                                </button>

                                <div
                                    v-if="filteredMembers.length === 0"
                                    class="px-4 py-5 text-center text-xs text-slate-500 dark:text-slate-400"
                                >
                                    No members found.
                                </div>
                            </div>
                        </div>

                        <p
                            v-if="form.errors.member_id"
                            class="mt-2 text-xs text-red-500"
                        >
                            {{ form.errors.member_id }}
                        </p>

                        <div
                            v-if="selectedMember"
                            class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/20"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                {{ selectedMember.name.charAt(0).toUpperCase() }}
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold">
                                    {{ selectedMember.name }}
                                </p>

                                <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ selectedMember.member_number }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Identification -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Loan Identification
                        </h2>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Legacy Loan Number
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="form.legacy_loan_number"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p
                                v-if="form.errors.legacy_loan_number"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.legacy_loan_number }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                System Loan Number
                            </label>

                            <input
                                v-model="form.loan_number"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p
                                v-if="form.errors.loan_number"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.loan_number }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Loan Product
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-if="loanProducts?.length"
                                v-model="form.loan_product_id"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                @change="selectLoanProduct"
                            >
                                <option value="">
                                    Select loan product
                                </option>

                                <option
                                    v-for="product in loanProducts"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.name }}
                                    <template v-if="product.code">
                                        — {{ product.code }}
                                    </template>
                                </option>
                            </select>

                            <input
                                v-else
                                v-model="form.loan_product"
                                type="text"
                                placeholder="Enter loan product"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p
                                v-if="form.errors.loan_product_id || form.errors.loan_product"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.loan_product_id || form.errors.loan_product }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Loan Status
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="form.loan_status"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            >
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                                <option value="written_off">Written Off</option>
                                <option value="restructured">Restructured</option>
                                <option value="defaulted">Defaulted</option>
                                <option value="other">Other</option>
                            </select>

                            <p
                                v-if="form.errors.loan_status"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.loan_status }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Financial Information -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Financial Information
                        </h2>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-3">

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Original Loan Amount
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                                    KES
                                </span>

                                <input
                                    v-model="form.original_amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />
                            </div>

                            <p
                                v-if="form.errors.original_amount"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.original_amount }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Amount Paid
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                                    KES
                                </span>

                                <input
                                    v-model="form.amount_paid"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />
                            </div>

                            <p
                                v-if="form.errors.amount_paid"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.amount_paid }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Outstanding Balance
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                                    KES
                                </span>

                                <input
                                    v-model="form.outstanding_balance"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-24 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />

                                <button
                                    type="button"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg bg-slate-100 px-2 py-1.5 text-[9px] font-semibold text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
                                    @click="useCalculatedOutstanding"
                                >
                                    Calculate
                                </button>
                            </div>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Calculated:
                                {{ formatCurrency(calculatedOutstanding) }}
                            </p>

                            <p
                                v-if="form.errors.outstanding_balance"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.outstanding_balance }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Interest Rate
                            </label>

                            <div class="relative">
                                <input
                                    v-model="form.interest_rate"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 pr-9 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />

                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                                    %
                                </span>
                            </div>

                            <p
                                v-if="form.errors.interest_rate"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.interest_rate }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Repayment Period
                            </label>

                            <div class="relative">
                                <input
                                    v-model="form.repayment_period"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 pr-20 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />

                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">
                                    months
                                </span>
                            </div>

                            <p
                                v-if="form.errors.repayment_period"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.repayment_period }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Dates -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Loan Dates
                        </h2>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Disbursement Date
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="form.disbursement_date"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p
                                v-if="form.errors.disbursement_date"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.disbursement_date }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Maturity Date
                            </label>

                            <input
                                v-model="form.maturity_date"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p
                                v-if="form.errors.maturity_date"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.maturity_date }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Source -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Source & Remarks
                        </h2>
                    </div>

                    <div class="space-y-5 p-5">
                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Source Reference
                            </label>

                            <input
                                v-model="form.source_reference"
                                type="text"
                                placeholder="e.g. Loan Register Page 42 / File No. 0187"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p
                                v-if="form.errors.source_reference"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.source_reference }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Migration Remarks
                            </label>

                            <textarea
                                v-model="form.remarks"
                                rows="4"
                                maxlength="5000"
                                placeholder="Enter any relevant observations..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            ></textarea>

                            <div class="mt-1 flex justify-end">
                                <span class="text-[10px] text-slate-400">
                                    {{ form.remarks.length }}/5000
                                </span>
                            </div>

                            <p
                                v-if="form.errors.remarks"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.remarks }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Errors -->
                <div
                    v-if="form.hasErrors"
                    class="rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/20"
                >
                    <p class="text-xs font-bold text-red-700 dark:text-red-300">
                        Please correct the highlighted errors before saving.
                    </p>

                    <ul class="mt-2 space-y-1 text-xs text-red-600 dark:text-red-400">
                        <li
                            v-for="(error, field) in form.errors"
                            :key="field"
                        >
                            {{ error }}
                        </li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="sticky bottom-3 z-10 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 sm:p-4">
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">

                        <Link
                            :href="route('loan-migration.records.show', [
                                batch.id,
                                record.id,
                            ])"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing || !!amountError"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <svg
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                />
                            </svg>

                            <svg
                                v-else
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            {{ form.processing ? 'Updating...' : 'Update Loan Record' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
</template>