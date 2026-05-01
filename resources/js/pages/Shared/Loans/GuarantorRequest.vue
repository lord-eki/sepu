<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    loan: Object,
    guarantor: Object, 
});

const loading = ref(false);
const actionType = ref(null);

const approve = () => {
    loading.value = true

    router.post(route('accept-guarantee', {
        loan: props.loan.id,
        guarantor: props.guarantor.id
    }), {}, {
        onFinish: () => loading.value = false
    })
}

const reject = () => {
    loading.value = true

    router.post(route('reject-guarantee', {
        loan: props.loan.id,
        guarantor: props.guarantor.id
    }), {}, {
        onFinish: () => loading.value = false
    })
}
const statusColor = computed(() => {
    switch (props.guarantor.status) {
        case 'accepted': return 'bg-emerald-100 text-emerald-700';
        case 'rejected': return 'bg-rose-100 text-rose-700';
        default: return 'bg-yellow-100 text-yellow-700';
    }
});

console.log('GUARANTOR:', props.guarantor)
</script>

<template>
<AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 flex items-center justify-center p-4">

        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

            <!-- Header -->
            <div class="bg-blue-900 text-white px-6 py-5">
                <h1 class="text-xl font-bold">Guarantor Request</h1>
                <p class="text-sm text-blue-100">Loan guarantee approval request</p>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-5">

                <!-- Borrower -->
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500">Borrower</p>
                        <p class="font-semibold text-gray-900">
                            {{ loan.member.first_name }} {{ loan.member.last_name }}
                        </p>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                        :class="statusColor">
                        {{ guarantor.status }}
                    </span>
                </div>

                <!-- Loan details -->
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border">

                    <div>
                        <p class="text-xs text-gray-500">Loan Amount</p>
                        <p class="font-semibold text-gray-900">
                            KES {{ loan.applied_amount }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Your Guarantee</p>
                        <p class="font-bold text-blue-700 text-lg">
                            KES {{ guarantor.guaranteed_amount }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-xs text-gray-500">Loan Purpose</p>
                        <p class="text-gray-700">
                            {{ loan.purpose }}
                        </p>
                    </div>
                </div>

                <!-- Warning -->
                <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg text-sm text-yellow-800">
                    You are legally committing to cover <strong>KES {{ guarantor.guaranteed_amount }}</strong> if the borrower defaults.
                </div>

                <!-- Actions -->
                <div v-if="guarantor.status === 'pending'" class="flex gap-3 pt-2">

                    <button
                        @click="approve"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-medium transition"
                        :disabled="loading">
                        Approve Guarantee
                    </button>

                    <button
                        @click="reject"
                        class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2 rounded-lg font-medium transition"
                        :disabled="loading">
                        Reject
                    </button>

                </div>

                <!-- Already decided -->
                <div v-else class="text-center text-sm text-gray-500 pt-2">
                    You have already {{ guarantor.status }} this request.
                </div>

            </div>
        </div>
    </div>
</AppLayout>
</template>