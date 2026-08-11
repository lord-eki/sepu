<script setup lang="ts">
import { computed, reactive, watch } from 'vue'

interface MemberOption {
    id: number
    member_number?: string
    name: string
}

interface LoanProductOption {
    id: number
    name: string
    code?: string
}

interface RecordData {
    id?: number | null
    member_id?: number | null
    loan_product_id?: number | null
    legacy_loan_number?: string | null
    loan_date?: string | null
    original_amount?: number | string | null
    amount_paid?: number | string | null
    outstanding_balance?: number | string | null
    interest_rate?: number | string | null
    repayment_period?: number | string | null
    repayment_frequency?: string | null
    maturity_date?: string | null
    status?: string | null
    remarks?: string | null
}

interface Props {
    record?: RecordData | null
    members?: MemberOption[]
    loanProducts?: LoanProductOption[]
    readonly?: boolean
    processing?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    record: null,
    members: () => [],
    loanProducts: () => [],
    readonly: false,
    processing: false,
})

const emit = defineEmits<{
    submit: [data: Record<string, unknown>]
    cancel: []
}>()

const form = reactive({
    member_id: props.record?.member_id ?? null,
    loan_product_id: props.record?.loan_product_id ?? null,
    legacy_loan_number: props.record?.legacy_loan_number ?? '',
    loan_date: props.record?.loan_date ?? '',
    original_amount: props.record?.original_amount ?? '',
    amount_paid: props.record?.amount_paid ?? '',
    outstanding_balance: props.record?.outstanding_balance ?? '',
    interest_rate: props.record?.interest_rate ?? '',
    repayment_period: props.record?.repayment_period ?? '',
    repayment_frequency: props.record?.repayment_frequency ?? '',
    maturity_date: props.record?.maturity_date ?? '',
    status: props.record?.status ?? 'active',
    remarks: props.record?.remarks ?? '',
})

const errors = reactive<Record<string, string>>({})

watch(
    () => props.record,
    (record) => {
        if (!record) {
            return
        }

        form.member_id = record.member_id ?? null
        form.loan_product_id = record.loan_product_id ?? null
        form.legacy_loan_number = record.legacy_loan_number ?? ''
        form.loan_date = record.loan_date ?? ''
        form.original_amount = record.original_amount ?? ''
        form.amount_paid = record.amount_paid ?? ''
        form.outstanding_balance = record.outstanding_balance ?? ''
        form.interest_rate = record.interest_rate ?? ''
        form.repayment_period = record.repayment_period ?? ''
        form.repayment_frequency = record.repayment_frequency ?? ''
        form.maturity_date = record.maturity_date ?? ''
        form.status = record.status ?? 'active'
        form.remarks = record.remarks ?? ''
    },
    { deep: true },
)

const calculatedBalance = computed(() => {
    const original = Number(form.original_amount) || 0
    const paid = Number(form.amount_paid) || 0

    return Math.max(0, original - paid)
})

const balanceMatches = computed(() => {
    const entered = Number(form.outstanding_balance) || 0

    return Math.abs(entered - calculatedBalance.value) < 0.01
})

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value)
})

const clearError = (field: string) => {
    delete errors[field]
}

const validate = () => {
    Object.keys(errors).forEach((key) => delete errors[key])

    if (!form.member_id) {
        errors.member_id = 'Please select a member.'
    }

    if (!form.loan_product_id) {
        errors.loan_product_id = 'Please select a loan product.'
    }

    if (!form.legacy_loan_number.trim()) {
        errors.legacy_loan_number = 'Legacy loan number is required.'
    }

    if (!form.loan_date) {
        errors.loan_date = 'Loan date is required.'
    }

    if (
        form.original_amount === '' ||
        Number(form.original_amount) < 0
    ) {
        errors.original_amount = 'Enter a valid original loan amount.'
    }

    if (
        form.amount_paid === '' ||
        Number(form.amount_paid) < 0
    ) {
        errors.amount_paid = 'Enter a valid amount paid.'
    }

    if (
        Number(form.amount_paid) >
        Number(form.original_amount)
    ) {
        errors.amount_paid =
            'Amount paid cannot exceed the original loan amount.'
    }

    if (
        form.outstanding_balance === '' ||
        Number(form.outstanding_balance) < 0
    ) {
        errors.outstanding_balance =
            'Enter a valid outstanding balance.'
    }

    if (!balanceMatches.value) {
        errors.outstanding_balance =
            'Outstanding balance must equal original amount minus amount paid.'
    }

    if (
        form.interest_rate !== '' &&
        Number(form.interest_rate) < 0
    ) {
        errors.interest_rate = 'Interest rate cannot be negative.'
    }

    if (
        form.repayment_period !== '' &&
        Number(form.repayment_period) <= 0
    ) {
        errors.repayment_period =
            'Repayment period must be greater than zero.'
    }

    return Object.keys(errors).length === 0
}

const submit = () => {
    if (props.readonly || props.processing) {
        return
    }

    if (!validate()) {
        return
    }

    emit('submit', {
        member_id: form.member_id,
        loan_product_id: form.loan_product_id,
        legacy_loan_number: form.legacy_loan_number.trim(),
        loan_date: form.loan_date,
        original_amount: Number(form.original_amount),
        amount_paid: Number(form.amount_paid),
        outstanding_balance: Number(form.outstanding_balance),
        interest_rate:
            form.interest_rate === ''
                ? null
                : Number(form.interest_rate),
        repayment_period:
            form.repayment_period === ''
                ? null
                : Number(form.repayment_period),
        repayment_frequency:
            form.repayment_frequency || null,
        maturity_date:
            form.maturity_date || null,
        status: form.status,
        remarks: form.remarks.trim() || null,
    })
}

const selectedMember = computed(() =>
    props.members.find(
        (member) => member.id === Number(form.member_id),
    ),
)

const selectedProduct = computed(() =>
    props.loanProducts.find(
        (product) => product.id === Number(form.loan_product_id),
    ),
)
</script>

<template>
    <form
        class="space-y-6"
        @submit.prevent="submit"
    >
        <!-- Member & Loan Information -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
        >
            <div class="mb-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    Loan Identification
                </h2>

                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                    Identify the member and legacy loan being migrated.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <!-- Member -->
                <div>
                    <label
                        for="member_id"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Member
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="member_id"
                        v-model="form.member_id"
                        :disabled="readonly || processing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                        @change="clearError('member_id')"
                    >
                        <option :value="null">
                            Select member
                        </option>

                        <option
                            v-for="member in members"
                            :key="member.id"
                            :value="member.id"
                        >
                            {{ member.member_number ? `${member.member_number} — ` : '' }}{{ member.name }}
                        </option>
                    </select>

                    <p
                        v-if="errors.member_id"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.member_id }}
                    </p>
                </div>

                <!-- Loan Product -->
                <div>
                    <label
                        for="loan_product_id"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Loan Product
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="loan_product_id"
                        v-model="form.loan_product_id"
                        :disabled="readonly || processing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                        @change="clearError('loan_product_id')"
                    >
                        <option :value="null">
                            Select loan product
                        </option>

                        <option
                            v-for="product in loanProducts"
                            :key="product.id"
                            :value="product.id"
                        >
                            {{ product.code ? `${product.code} — ` : '' }}{{ product.name }}
                        </option>
                    </select>

                    <p
                        v-if="errors.loan_product_id"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.loan_product_id }}
                    </p>
                </div>

                <!-- Legacy Loan Number -->
                <div>
                    <label
                        for="legacy_loan_number"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Legacy Loan Number
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="legacy_loan_number"
                        v-model="form.legacy_loan_number"
                        type="text"
                        :disabled="readonly || processing"
                        placeholder="e.g. LN/2019/00125"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder:text-slate-500 dark:disabled:bg-slate-900"
                        @input="clearError('legacy_loan_number')"
                    />

                    <p
                        v-if="errors.legacy_loan_number"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.legacy_loan_number }}
                    </p>
                </div>

                <!-- Loan Date -->
                <div>
                    <label
                        for="loan_date"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Original Loan Date
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="loan_date"
                        v-model="form.loan_date"
                        type="date"
                        :disabled="readonly || processing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                        @change="clearError('loan_date')"
                    />

                    <p
                        v-if="errors.loan_date"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.loan_date }}
                    </p>
                </div>
            </div>

            <!-- Selection Preview -->
            <div
                v-if="selectedMember || selectedProduct"
                class="mt-5 grid gap-3 sm:grid-cols-2"
            >
                <div
                    v-if="selectedMember"
                    class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50"
                >
                    <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">
                        Selected Member
                    </p>

                    <p class="mt-1 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        {{ selectedMember.name }}
                    </p>

                    <p
                        v-if="selectedMember.member_number"
                        class="mt-0.5 text-[9px] text-slate-400"
                    >
                        {{ selectedMember.member_number }}
                    </p>
                </div>

                <div
                    v-if="selectedProduct"
                    class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50"
                >
                    <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">
                        Loan Product
                    </p>

                    <p class="mt-1 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        {{ selectedProduct.name }}
                    </p>

                    <p
                        v-if="selectedProduct.code"
                        class="mt-0.5 text-[9px] text-slate-400"
                    >
                        {{ selectedProduct.code }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Financial Information -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
        >
            <div class="mb-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    Financial Information
                </h2>

                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                    Enter the financial figures recorded in the legacy documents.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                <!-- Original Amount -->
                <div>
                    <label
                        for="original_amount"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Original Loan Amount
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-slate-400"
                        >
                            KES
                        </span>

                        <input
                            id="original_amount"
                            v-model="form.original_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            :disabled="readonly || processing"
                            placeholder="0.00"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-3 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                            @input="clearError('original_amount')"
                        />
                    </div>

                    <p
                        v-if="errors.original_amount"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.original_amount }}
                    </p>
                </div>

                <!-- Amount Paid -->
                <div>
                    <label
                        for="amount_paid"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Amount Paid
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-slate-400"
                        >
                            KES
                        </span>

                        <input
                            id="amount_paid"
                            v-model="form.amount_paid"
                            type="number"
                            min="0"
                            step="0.01"
                            :disabled="readonly || processing"
                            placeholder="0.00"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-12 pr-3 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                            @input="clearError('amount_paid')"
                        />
                    </div>

                    <p
                        v-if="errors.amount_paid"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.amount_paid }}
                    </p>
                </div>

                <!-- Outstanding Balance -->
                <div>
                    <label
                        for="outstanding_balance"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Outstanding Balance
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-slate-400"
                        >
                            KES
                        </span>

                        <input
                            id="outstanding_balance"
                            v-model="form.outstanding_balance"
                            type="number"
                            min="0"
                            step="0.01"
                            :disabled="readonly || processing"
                            placeholder="0.00"
                            class="w-full rounded-xl border py-2.5 pl-12 pr-3 text-xs outline-none transition focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:disabled:bg-slate-900"
                            :class="
                                balanceMatches
                                    ? 'border-slate-200 bg-white text-slate-700 focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200'
                                    : 'border-red-300 bg-red-50 text-red-700 focus:border-red-500 dark:border-red-800 dark:bg-red-950/20 dark:text-red-300'
                            "
                            @input="clearError('outstanding_balance')"
                        />
                    </div>

                    <p
                        v-if="errors.outstanding_balance"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.outstanding_balance }}
                    </p>

                    <p
                        v-else
                        class="mt-1.5 text-[9px] text-slate-400"
                    >
                        Expected:
                        {{ formatCurrency(calculatedBalance) }}
                    </p>
                </div>
            </div>

            <!-- Balance Calculation -->
            <div
                class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-700 dark:text-slate-200">
                            Balance Reconciliation
                        </p>

                        <p class="mt-1 text-[9px] text-slate-400">
                            Original amount − amount paid = outstanding balance
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ formatCurrency(calculatedBalance) }}
                        </p>

                        <p
                            class="mt-0.5 text-[9px]"
                            :class="
                                balanceMatches
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-600 dark:text-red-400'
                            "
                        >
                            {{
                                balanceMatches
                                    ? 'Balance reconciles'
                                    : 'Balance does not reconcile'
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Repayment Information -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
        >
            <div class="mb-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    Repayment Information
                </h2>

                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                    Capture the repayment terms from the legacy loan records.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

                <!-- Interest Rate -->
                <div>
                    <label
                        for="interest_rate"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Interest Rate
                    </label>

                    <div class="relative">
                        <input
                            id="interest_rate"
                            v-model="form.interest_rate"
                            type="number"
                            min="0"
                            step="0.01"
                            :disabled="readonly || processing"
                            placeholder="0.00"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 pr-8 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                            @input="clearError('interest_rate')"
                        />

                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">
                            %
                        </span>
                    </div>

                    <p
                        v-if="errors.interest_rate"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.interest_rate }}
                    </p>
                </div>

                <!-- Repayment Period -->
                <div>
                    <label
                        for="repayment_period"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Repayment Period
                    </label>

                    <input
                        id="repayment_period"
                        v-model="form.repayment_period"
                        type="number"
                        min="1"
                        step="1"
                        :disabled="readonly || processing"
                        placeholder="e.g. 12"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                        @input="clearError('repayment_period')"
                    />

                    <p
                        v-if="errors.repayment_period"
                        class="mt-1.5 text-[10px] text-red-600 dark:text-red-400"
                    >
                        {{ errors.repayment_period }}
                    </p>
                </div>

                <!-- Frequency -->
                <div>
                    <label
                        for="repayment_frequency"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Repayment Frequency
                    </label>

                    <select
                        id="repayment_frequency"
                        v-model="form.repayment_frequency"
                        :disabled="readonly || processing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                    >
                        <option value="">
                            Select frequency
                        </option>

                        <option value="daily">
                            Daily
                        </option>

                        <option value="weekly">
                            Weekly
                        </option>

                        <option value="monthly">
                            Monthly
                        </option>

                        <option value="quarterly">
                            Quarterly
                        </option>
                    </select>
                </div>

                <!-- Maturity Date -->
                <div>
                    <label
                        for="maturity_date"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Maturity Date
                    </label>

                    <input
                        id="maturity_date"
                        v-model="form.maturity_date"
                        type="date"
                        :disabled="readonly || processing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                    />
                </div>
            </div>
        </section>

        <!-- Status & Remarks -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
        >
            <div class="mb-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    Record Details
                </h2>

                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                    Set the current legacy loan status and add supporting remarks.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <!-- Status -->
                <div>
                    <label
                        for="status"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Loan Status
                    </label>

                    <select
                        id="status"
                        v-model="form.status"
                        :disabled="readonly || processing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:disabled:bg-slate-900"
                    >
                        <option value="active">
                            Active
                        </option>

                        <option value="cleared">
                            Cleared
                        </option>

                        <option value="defaulted">
                            Defaulted
                        </option>

                        <option value="written_off">
                            Written Off
                        </option>

                        <option value="restructured">
                            Restructured
                        </option>
                    </select>
                </div>

                <!-- Remarks -->
                <div>
                    <label
                        for="remarks"
                        class="mb-1.5 block text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                    >
                        Remarks
                    </label>

                    <textarea
                        id="remarks"
                        v-model="form.remarks"
                        rows="4"
                        :disabled="readonly || processing"
                        placeholder="Enter any relevant notes from the legacy records..."
                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder:text-slate-500 dark:disabled:bg-slate-900"
                    />
                </div>
            </div>
        </section>

        <!-- Actions -->
        <div
            v-if="!readonly"
            class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
        >
            <button
                type="button"
                :disabled="processing"
                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                @click="emit('cancel')"
            >
                Cancel
            </button>

            <button
                type="submit"
                :disabled="processing"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg
                    v-if="processing"
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

                {{ processing ? 'Saving...' : 'Save Record' }}
            </button>
        </div>
    </form>
</template>
