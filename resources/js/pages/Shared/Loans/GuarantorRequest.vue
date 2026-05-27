<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import {
    Loader2,
    ShieldCheck,
    ArrowLeft,
    AlertTriangle,
    BadgeCheck,
    XCircle,
} from 'lucide-vue-next'

const props = defineProps({
    loan: Object,
    guarantor: Object,
})

const page = usePage()

/* ---------------- STATE ---------------- */
const loading = ref(false)
const pageLoading = ref(false)
const showConfirm = ref(false)
const actionType = ref(null)


/* ---------------- FLASH ---------------- */
const flashVisible = ref(true)

const successMessage = computed(() =>
    flashVisible.value ? page.props.flash?.success || null : null
)

const errorMessage = computed(() =>
    flashVisible.value ? page.props.flash?.error || null : null
)

/* AUTO HIDE FLASH */
watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (success || error) {
            flashVisible.value = true

            setTimeout(() => {
                flashVisible.value = false
            }, 4000) // disappears after 4 seconds
        }
    },
    { immediate: true }
)

/* ---------------- BACK NAVIGATION ---------------- */
const goBack = () => {
    router.visit(route('my-guarantees'))
}

/* ---------------- STATUS ---------------- */
const statusConfig = computed(() => {
    switch (props.guarantor?.status) {
        case 'accepted':
            return {
                badge: 'bg-emerald-500/10 text-emerald-500 border border-emerald-200',
                icon: BadgeCheck,
                text: 'Accepted',
            }

        case 'rejected':
            return {
                badge: 'bg-rose-500/10 text-rose-500 border border-rose-200',
                icon: XCircle,
                text: 'Rejected',
            }

        default:
            return {
                badge: 'bg-yellow-500/10 text-yellow-500 border border-yellow-200',
                icon: AlertTriangle,
                text: 'Pending',
            }
    }
})

/* ---------------- ACTIONS ---------------- */
const openConfirm = (type) => {
    actionType.value = type
    showConfirm.value = true
}

const confirmAction = () => {
    if (!actionType.value) return

    loading.value = true

    const routeName =
        actionType.value === 'approve'
            ? 'loans.accept-guarantee'
            : 'loans.reject-guarantee'

    router.post(
        route(routeName, {
            loan: props.loan.id,
            guarantor: props.guarantor.id,
        }),
        {},
        {
            onStart: () => (loading.value = true),
            onFinish: () => {
                loading.value = false
                showConfirm.value = false
            },
        }
    )
}

const formatCurrency = (amount) =>
    new Intl.NumberFormat('en-KE').format(amount || 0)
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Loans', href: '/my-loans' },
        { title: 'Guarantor Request' },
    ]">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-6">
            <div class="mx-auto max-w-5xl space-y-6">

                <!-- BACK -->
                <button @click="goBack"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <ArrowLeft class="h-4 w-4" />
                    Back to My Guarantees
                </button>

                <!-- FLASH -->
                <transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100" leave-active-class="transition duration-200"
                    leave-to-class="opacity-0">
                    <div v-if="successMessage || errorMessage" :class="[
        successMessage
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-rose-200 bg-rose-50 text-rose-700',
        'rounded-2xl border px-5 py-4 shadow-sm',
    ]">
                        {{ successMessage || errorMessage }}
                    </div>
                </transition>

                <!-- HERO -->
                <section
                    class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0F172A] via-[#132F57] to-[#1E3A8A] p-6 sm:p-8 shadow-2xl">
                    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-orange-400/20 blur-3xl"></div>

                    <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"></div>

                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-1.5 backdrop-blur">
                                <ShieldCheck class="h-4 w-4 text-emerald-400" />

                                <span class="text-xs font-medium text-white">
                                    Loan Guarantee Request
                                </span>
                            </div>

                            <h1 class="mt-4 text-2xl font-bold tracking-tight text-white">
                                Review Guarantee
                            </h1>

                            <p class="mt-2 max-w-xl text-sm text-slate-300">
                                Carefully review the borrower information and decide whether to accept or reject this
                                guarantee request.
                            </p>
                        </div>

                        <!-- STATUS -->
                        <div :class="[
        statusConfig.badge,
        'inline-flex items-center gap-2 rounded-2xl px-4 py-3 font-semibold backdrop-blur',
    ]">
                            <component :is="statusConfig.icon" class="h-5 w-5" />

                            {{ statusConfig.text }}
                        </div>
                    </div>
                </section>

                <!-- MAIN GRID -->
                <div class="grid gap-6 xl:grid-cols-[1fr,360px]">

                    <!-- DETAILS -->
                    <div v-if="!pageLoading"
                        class="overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm">
                        <!-- HEADER -->
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-xl font-bold text-slate-900">
                                Borrower Details
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Loan and guarantee information
                            </p>
                        </div>

                        <!-- CONTENT -->
                        <div class="space-y-6 p-6">

                            <!-- MEMBER -->
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <p class="text-xs uppercase tracking-wide text-slate-500">
                                    Borrower
                                </p>

                                <h3 class="mt-2 text-xl font-bold text-slate-900">
                                    {{ loan.member.first_name }}
                                    {{ loan.member.last_name }}
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Loan Request Information
                                </p>
                            </div>

                            <!-- STATS -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <p class="text-sm text-slate-500">
                                        Loan Amount
                                    </p>

                                    <h3 class="mt-2 text-2xl font-bold text-slate-900">
                                        KES {{ formatCurrency(loan.applied_amount) }}
                                    </h3>
                                </div>

                                <div class="rounded-3xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                                    <p class="text-sm text-blue-600">
                                        Your Guarantee
                                    </p>

                                    <h3 class="mt-2 text-2xl font-bold text-blue-900">
                                        KES {{ formatCurrency(guarantor.guaranteed_amount) }}
                                    </h3>
                                </div>
                            </div>

                            <!-- PURPOSE -->
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-sm font-medium text-slate-500">
                                    Loan Purpose
                                </p>

                                <p class="mt-3 leading-relaxed text-slate-700">
                                    {{ loan.purpose }}
                                </p>
                            </div>

                            <!-- WARNING -->
                            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                                <div class="flex gap-3">
                                    <AlertTriangle class="mt-0.5 h-5 w-5 text-amber-600" />

                                    <div>
                                        <h4 class="font-semibold text-amber-800">
                                            Financial Responsibility
                                        </h4>

                                        <p class="mt-1 text-sm leading-relaxed text-amber-700">
                                            By accepting this request, you agree to cover
                                            <strong>
                                                KES {{ formatCurrency(guarantor.guaranteed_amount) }}
                                            </strong>
                                            if the borrower defaults on the loan repayments.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ACTIONS -->
                            <div v-if="guarantor.status === 'pending'" class="grid gap-4 sm:grid-cols-2">
                                <!-- ACCEPT -->
                                <button @click="openConfirm('approve')" :disabled="loading"
                                    class="flex h-14 items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 font-semibold text-white shadow-lg transition-all hover:scale-[1.01] hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                                    <Loader2 v-if="loading && actionType === 'approve'" class="h-5 w-5 animate-spin" />

                                    {{
        loading && actionType === 'approve'
            ? 'Processing...'
            : 'Accept Request'
    }}
                                </button>

                                <!-- REJECT -->
                                <button @click="openConfirm('reject')" :disabled="loading"
                                    class="flex h-14 items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-rose-600 font-semibold text-white shadow-lg transition-all hover:scale-[1.01] hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                                    <Loader2 v-if="loading && actionType === 'reject'" class="h-5 w-5 animate-spin" />

                                    {{
        loading && actionType === 'reject'
            ? 'Processing...'
            : 'Reject Request'
    }}
                                </button>
                            </div>

                            <!-- STATUS TEXT -->
                            <div v-else
                                class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-center text-sm text-slate-600">
                                You have already
                                <span class="font-semibold">
                                    {{ guarantor.status }}
                                </span>
                                this guarantor request.
                            </div>

                        </div>
                    </div>

                    <!-- SIDE PANEL -->
                    <div class="space-y-6">

                        <!-- QUICK INFO -->
                        <div class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-bold text-slate-900">
                                Quick Overview
                            </h3>

                            <div class="mt-6 space-y-5">

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500">
                                        Borrower
                                    </span>

                                    <span class="text-sm font-semibold text-slate-800">
                                        {{ loan.member.first_name }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500">
                                        Status
                                    </span>

                                    <span class="text-sm font-semibold text-slate-800 capitalize">
                                        {{ guarantor.status }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500">
                                        Guarantee
                                    </span>

                                    <span class="text-sm font-semibold text-blue-900">
                                        KES {{ formatCurrency(guarantor.guaranteed_amount) }}
                                    </span>
                                </div>

                            </div>
                        </div>

                        <!-- HELP -->
                        <div
                            class="rounded-[30px] bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white shadow-xl">
                            <h3 class="text-lg font-bold">
                                Important Notice
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-orange-50">
                                Guaranteeing a loan creates a financial obligation. Make sure you understand the
                                repayment responsibility before approving this request.
                            </p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!-- MODAL -->
        <transition enter-active-class="transition duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200" leave-to-class="opacity-0">
            <div v-if="showConfirm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-[30px] bg-white p-6 shadow-2xl">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl" :class="actionType === 'approve'
        ? 'bg-emerald-100 text-emerald-600'
        : 'bg-rose-100 text-rose-600'
        ">
                        <component :is="
                actionType === 'approve'
                  ? BadgeCheck
                  : XCircle
              " class="h-7 w-7" />
                    </div>

                    <h3 class="mt-5 text-2xl font-bold text-slate-900">
                        Confirm
                        {{ actionType === 'approve' ? 'Approval' : 'Rejection' }}
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Are you sure you want to
                        <strong>
                            {{ actionType }}
                        </strong>
                        this guarantor request?
                    </p>

                    <div class="mt-6 flex gap-3">
                        <button @click="showConfirm = false" :disabled="loading"
                            class="flex-1 rounded-2xl border border-slate-200 px-4 py-3 font-medium text-slate-700 transition hover:bg-slate-100">
                            Cancel
                        </button>

                        <button @click="confirmAction" :disabled="loading" :class="[
        actionType === 'approve'
            ? 'bg-emerald-600 hover:bg-emerald-700'
            : 'bg-rose-600 hover:bg-rose-700',
        'flex flex-1 items-center justify-center gap-2 rounded-2xl px-4 py-3 font-semibold text-white transition',
    ]">
                            <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />

                            {{
                            loading
                            ? 'Submitting...'
                            : 'Yes, Continue'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>