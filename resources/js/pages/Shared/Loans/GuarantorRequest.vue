<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Loader2 } from 'lucide-vue-next'

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
const successMessage = computed(() => page.props.flash?.success || null)
const errorMessage = computed(() => page.props.flash?.error || null)

/* ---------------- BACK NAVIGATION ---------------- */
const goBack = () => {
    router.visit(route('my-guarantees'))
}

/* ---------------- STATUS COLOR ---------------- */
const statusColor = computed(() => {
    switch (props.guarantor?.status) {
        case 'accepted': return 'bg-emerald-100 text-emerald-700'
        case 'rejected': return 'bg-rose-100 text-rose-700'
        case 'pending': return 'bg-yellow-100 text-yellow-700'
        default: return 'bg-blue-100 text-blue-700'
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

    router.post(route(routeName, {
        loan: props.loan.id,
        guarantor: props.guarantor.id
    }), {}, {
        onStart: () => loading.value = true,
        onFinish: () => {
            loading.value = false
            showConfirm.value = false
        }
    })
}
</script>

<template>
<AppLayout :breadcrumbs="[
    { title: 'Loans', href: '/my-loans' },
    { title: 'Guarantor Request' }
]">

<div class="min-h-screen bg-gradient-to-b from-blue-50 via-white to-orange-50 p-4 sm:p-6">
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- BACK BUTTON -->
        <div>
            <button
                @click="goBack"
                class="text-base text-blue-950 hover:text-orange-600 flex items-center gap-1"
            >
                ← Back to My Guarantees
            </button>
        </div>

        <!-- FLASH -->
        <transition
            enter-active-class="transition duration-300"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="successMessage || errorMessage"
                :class="[
                    successMessage
                        ? 'bg-emerald-100 text-emerald-900 border-emerald-300'
                        : 'bg-rose-100 text-rose-900 border-rose-300',
                    'px-5 py-3 rounded-xl border shadow-sm'
                ]"
            >
                {{ successMessage || errorMessage }}
            </div>
        </transition>

        <!-- CARD -->
        <div v-if="!pageLoading" class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

            <!-- HEADER -->
            <div class="bg-blue-900 text-white px-6 py-5 flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold">Guarantor Request</h1>
                    <p class="text-sm text-blue-200">Review and respond</p>
                </div>

                <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusColor">
                    {{ guarantor.status }}
                </span>
            </div>

            <!-- BODY -->
            <div class="p-6 space-y-6">
                <div>
                    <p class="text-xs text-gray-500">Borrower</p>
                    <p class="font-semibold text-gray-900 text-base">
                        {{ loan.member.first_name }} {{ loan.member.last_name }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border">
                    <div>
                        <p class="text-xs text-gray-500">Loan Amount</p>
                        <p class="font-semibold text-gray-900">KES {{ loan.applied_amount }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Your Guarantee</p>
                        <p class="font-bold text-blue-700 text-lg">KES {{ guarantor.guaranteed_amount }}</p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-xs text-gray-500">Purpose</p>
                        <p class="text-gray-700">{{ loan.purpose }}</p>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg text-sm text-yellow-800">
                    ⚠️ You are committing to cover
                    <strong>KES {{ guarantor.guaranteed_amount }}</strong>
                    if the borrower defaults.
                </div>

                <!-- ACTIONS -->
                <div v-if="guarantor.status === 'pending'" class="flex gap-3">
                    <button
                        @click="openConfirm('approve')"
                        :disabled="loading"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-70 text-white py-2 rounded-lg font-medium flex items-center justify-center gap-2"
                    >
                        <Loader2 v-if="loading && actionType === 'approve'" class="w-4 h-4 animate-spin" />
                        <span>{{ loading && actionType === 'approve' ? 'Processing...' : 'Accept' }}</span>
                    </button>

                    <button
                        @click="openConfirm('reject')"
                        :disabled="loading"
                        class="flex-1 bg-rose-600 hover:bg-rose-700 disabled:opacity-70 text-white py-2 rounded-lg font-medium flex items-center justify-center gap-2"
                    >
                        <Loader2 v-if="loading && actionType === 'reject'" class="w-4 h-4 animate-spin" />
                        <span>{{ loading && actionType === 'reject' ? 'Processing...' : 'Reject' }}</span>
                    </button>
                </div>

                <div v-else class="text-center text-sm text-gray-500">
                    You have already {{ guarantor.status }} this request.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL -->
<div v-if="showConfirm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-3">
            Confirm {{ actionType === 'approve' ? 'Approval' : 'Rejection' }}
        </h3>

        <p class="text-sm text-gray-600 mb-5">
            Are you sure you want to <strong>{{ actionType }}</strong> this guarantor request?
        </p>

        <div class="flex justify-end gap-3">
            <button
                @click="showConfirm = false"
                :disabled="loading"
                class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100"
            >
                Cancel
            </button>

            <button
                @click="confirmAction"
                :disabled="loading"
                :class="[
                    actionType === 'approve'
                        ? 'bg-emerald-600 hover:bg-emerald-700'
                        : 'bg-rose-600 hover:bg-rose-700',
                    'px-4 py-2 text-white rounded-lg flex items-center gap-2 disabled:opacity-70'
                ]"
            >
                <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                <span>{{ loading ? 'Submitting...' : 'Yes, Continue' }}</span>
            </button>
        </div>
    </div>
</div>

</AppLayout>
</template>