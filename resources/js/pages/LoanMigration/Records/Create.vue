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
    members?: Member[]
    loanProducts?: LoanProduct[]
}

const props = defineProps<Props>()

const form = useForm({
    member_id: '',
    legacy_loan_number: '',
    loan_number: '',
    loan_product_id: '',
    loan_product: '',
    original_amount: '',
    amount_paid: '',
    outstanding_balance: '',
    interest_rate: '',
    repayment_period: '',
    disbursement_date: '',
    maturity_date: '',
    loan_status: 'active',
    source_reference: '',
    remarks: '',
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

const submit = () => {
    if (amountError.value) {
        return
    }

    if (!form.outstanding_balance) {
        form.outstanding_balance = calculatedOutstanding.value.toFixed(2)
    }

    form.post(
        route('loan-migration.records.store', props.batch.id),
        {
            preserveScroll: true,
        }
    )
}

const saveAndAddAnother = () => {
    if (amountError.value) {
        return
    }

    if (!form.outstanding_balance) {
        form.outstanding_balance = calculatedOutstanding.value.toFixed(2)
    }

    form.post(
        route('loan-migration.records.store', props.batch.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()

                form.loan_status = 'active'
                search.member = ''
            },
        }
    )
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
                title: 'Add Record'
            }
        ]"
    >
        <Head title="Add Migration Record" />

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
                        d="M9 5l7 7-7 7"
                    />
                </svg>

                <span class="font-medium text-slate-700 dark:text-slate-300">
                    Add Loan Record
                </span>
            </div>

            <!-- Page Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Add Loan Record
                            </h1>

                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                {{ batch.batch_number }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Capture an existing loan from the SACCO's legacy records.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('loan-migration.records.index', batch.id)"
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

                    Back to Records
                </Link>
            </div>

            <!-- Warning -->
            <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13.5A2 2 0 004.2 20h15.6a2 2 0 001.73-2.64l-7.82-13.5a2 2 0 00-3.42 0z"
                    />
                </svg>

                <div>
                    <p class="text-xs font-bold text-amber-800 dark:text-amber-300">
                        Migration data accuracy
                    </p>

                    <p class="mt-1 text-xs leading-5 text-amber-700 dark:text-amber-400">
                        Enter the information exactly as it appears in the
                        original loan documents. The record will be validated
                        before it can proceed to verification and approval.
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form
                class="space-y-6"
                @submit.prevent="submit"
            >

                <!-- Member Section -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
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
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-bold">
                                    Member Information
                                </h2>

                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Link this legacy loan to an existing SACCO member.
                                </p>
                            </div>
                        </div>
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
                                    placeholder="Search member number or name..."
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
                                v-if="search.member && !form.member_id"
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

                        <!-- Selected Member -->
                        <div
                            v-if="selectedMember"
                            class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/20"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ selectedMember.name.charAt(0).toUpperCase() }}
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold">
                                        {{ selectedMember.name }}
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">
                                        Member No:
                                        {{ selectedMember.member_number }}
                                    </p>
                                </div>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                Member matched
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Loan Identification -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Loan Identification
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Enter the identifiers found in the legacy records.
                        </p>
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
                                placeholder="e.g. LN/2019/0045"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                New/System Loan Number
                            </label>

                            <input
                                v-model="form.loan_number"
                                type="text"
                                placeholder="Optional"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            />

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Leave blank if the system should generate it later.
                            </p>

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
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                placeholder="Enter legacy loan product"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            >
                                <option value="active">
                                    Active
                                </option>

                                <option value="closed">
                                    Closed
                                </option>

                                <option value="written_off">
                                    Written Off
                                </option>

                                <option value="restructured">
                                    Restructured
                                </option>

                                <option value="defaulted">
                                    Defaulted
                                </option>

                                <option value="other">
                                    Other
                                </option>
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

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Enter the financial position of the loan as recorded in the legacy system.
                        </p>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-3">

                        <div>
                            <label class="mb-2 block text-xs font-semibold">
                                Original Loan Amount
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">
                                    KES
                                </span>

                                <input
                                    v-model="form.original_amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">
                                    KES
                                </span>

                                <input
                                    v-model="form.amount_paid"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">
                                    KES
                                </span>

                                <input
                                    v-model="form.outstanding_balance"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-24 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                />

                                <button
                                    type="button"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg bg-slate-100 px-2 py-1.5 text-[9px] font-semibold text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                    @click="useCalculatedOutstanding"
                                >
                                    Calculate
                                </button>
                            </div>

                            <p
                                v-if="amountError"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ amountError }}
                            </p>

                            <p
                                v-else
                                class="mt-1 text-[10px] text-slate-500 dark:text-slate-400"
                            >
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
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 pr-10 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                    placeholder="e.g. 12"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 pr-20 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Use the dates from the original loan documentation.
                        </p>
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
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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

                <!-- Source Information -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Source & Remarks
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Keep a reference to the original document or source.
                        </p>
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
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
                                placeholder="Enter any relevant observations about this legacy loan..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            ></textarea>

                            <div class="mt-1 flex justify-between">
                                <p
                                    v-if="form.errors.remarks"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.remarks }}
                                </p>

                                <span class="ml-auto text-[10px] text-slate-400">
                                    {{ form.remarks.length }}/5000
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Server Errors -->
                <div
                    v-if="form.hasErrors"
                    class="rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/20"
                >
                    <div class="flex gap-3">
                        <svg
                            class="h-5 w-5 shrink-0 text-red-600 dark:text-red-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4m0 4h.01M10.29 3.86l-7.82 13.5A2 2 0 004.2 20h15.6a2 2 0 001.73-2.64l-7.82-13.5a2 2 0 00-3.42 0z"
                            />
                        </svg>

                        <div>
                            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                                Please correct the errors below.
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
                    </div>
                </div>

                <!-- Actions -->
                <div class="sticky bottom-3 z-10 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 sm:p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <p class="hidden text-[10px] text-slate-500 dark:text-slate-400 sm:block">
                            Fields marked
                            <span class="text-red-500">*</span>
                            are required.
                        </p>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <Link
                                :href="route('loan-migration.records.index', batch.id)"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancel
                            </Link>

                            <button
                                type="button"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-300 dark:hover:bg-blue-950/50"
                                @click="saveAndAddAnother"
                            >
                                <svg
                                    v-if="!form.processing"
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>

                                {{ form.processing ? 'Saving...' : 'Save & Add Another' }}
                            </button>

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

                                {{ form.processing ? 'Saving Record...' : 'Save Loan Record' }}
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</AppLayout>
</template>
