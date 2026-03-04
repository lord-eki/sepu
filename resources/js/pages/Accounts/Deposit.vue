<template>
  <AppLayout :breadcrumbs="[
    { title: isMemberRole ? 'My Accounts' : 'Accounts', href: isMemberRole ? route('my-accounts') : route('accounts.index') },
    { title: 'Deposit' }
  ]">

    <!-- ========== FLASH MESSAGES ========== -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="successMessage || errorMessages"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md shadow-xl rounded-2xl overflow-hidden"
        :class="successMessage ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
      >
        <div class="flex items-center justify-between px-5 py-4">
          <div>
            <p v-if="successMessage" class="font-semibold">{{ successMessage }}</p>
            <ul v-else class="list-disc pl-5 text-sm space-y-1">
              <li v-for="(errs, field) in errorMessages" :key="field">
                <template v-if="field === 'general'">{{ errs.join(', ') }}</template>
                <template v-else><strong>{{ field }}:</strong> {{ errs.join(', ') }}</template>
              </li>
            </ul>
          </div>
          <button @click="() => { successMessage = null; errorMessages = null }"
            class="text-gray-500 hover:text-gray-700 text-lg">✕</button>
        </div>
      </div>
    </transition>

    <!-- ========== HEADER ========== -->
    <div class="relative overflow-hidden bg-gradient-to-r from-[#0B2B40] via-blue-900 to-orange-900 py-8 px-[5%] rounded-b-3xl shadow-2xl">
      <div class="absolute -top-20 -right-20 w-72 h-72 bg-orange-500/20 blur-3xl rounded-full"></div>

      <div class="relative flex flex-wrap sm:flex-row justify-between items-start sm:items-center gap-4 text-white">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
            Deposit Funds
          </h2>
          <p class="text-slate-200 mt-1 text-sm">
            {{ account.account_number }} — {{ account.member.first_name }} {{ account.member.last_name }}
          </p>
        </div>

        <Link
          :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
          class="bg-white/10 backdrop-blur border border-white/20 px-5 py-2 rounded-xl hover:bg-white/20 transition"
        >
          Back
        </Link>
      </div>
    </div>

    <!-- ========== SHARE CAPITAL BLOCK ========== -->
    <div v-if="account.account_type === 'share_capital'" class="py-12 px-[5%] bg-slate-50">
      <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-xl p-10 text-center">
        <h3 class="text-xl font-semibold text-yellow-700 mb-3">
          Deposits Not Available
        </h3>
        <p class="text-slate-600">
          Share Capital accounts do not accept regular deposits.
        </p>
      </div>
    </div>

    <!-- ========== MAIN FORM SECTION ========== -->
    <div v-else class="pb-10 pt-5 px-[5%] bg-gradient-to-br from-slate-50 via-white to-slate-100">
      <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-xl border border-slate-200 rounded-3xl shadow-2xl p-10">

        <!-- LOCK NOTICE FOR NON-ADMIN -->
        <div v-if="depositLocked" class="mb-8 rounded-2xl border border-rose-200 bg-rose-50 p-4 sm:p-6">
          <h3 class="font-semibold text-rose-800 text-sm">🔒 Deposits Restricted</h3>
          <p class="text-sm text-rose-700 mt-1">
            Deposits are currently restricted to authorized admin only.
          </p>
        </div>

        <!-- ACCOUNT SUMMARY -->
        <div class="grid sm:grid-cols-4 gap-6 mb-10">
          <div>
            <p class="text-xs text-slate-500 uppercase">Account Type</p>
            <p class="font-semibold text-slate-800">{{ getAccountTypeLabel(account.account_type) }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 uppercase">Current Balance</p>
            <p class="text-xl font-bold text-[#0B2B40]">{{ formatCurrency(account.balance) }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 uppercase">Available</p>
            <p class="text-xl font-semibold text-blue-900">{{ formatCurrency(account.available_balance) }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 uppercase">Status</p>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
              :class="account.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
              {{ account.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>

        <!-- DEPOSIT FORM -->
        <form @submit.prevent="submit" class="space-y-8" :class="{ 'opacity-50 pointer-events-none': depositLocked }">

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Deposit Amount (KES)</label>
            <input v-model.number="form.amount" type="number" min="100" placeholder="Enter amount"
              class="w-full rounded-xl border border-slate-300 px-4 py-3
                     focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
              required />
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method</label>
            <select v-model="form.payment_method"
              class="w-full rounded-xl border border-slate-300 px-4 py-3
                     focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
              required>
              <option value="">Select method</option>
              <option v-for="(label, value) in props.paymentMethods" :key="value" :value="value">{{ label }}</option>
            </select>
          </div>

          <!-- Reference -->
          <div v-if="form.payment_method">
            <label class="block text-sm font-medium text-slate-700 mb-2">Payment Reference</label>
            <input v-model="form.payment_reference" type="text" placeholder="Enter reference number"
              class="w-full rounded-xl border border-slate-300 px-4 py-3
                     focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
              required />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Description (Optional)</label>
            <textarea v-model="form.description" rows="3"
              class="w-full rounded-xl border border-slate-300 px-4 py-3
                     focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"></textarea>
          </div>

          <!-- Submit -->
          <div class="flex justify-between items-center pt-6 border-t border-slate-200">
            <Link :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
              class="text-slate-600 hover:text-slate-900">Cancel</Link>

            <button type="submit"
              :disabled="depositLocked || form.processing || !form.amount || form.amount < 100 || !form.payment_reference"
              class="bg-gradient-to-r from-orange-500 to-orange-600
                     hover:from-orange-600 hover:to-orange-700
                     text-white font-semibold px-6 py-2 rounded-xl
                     shadow-lg hover:shadow-xl transition disabled:opacity-40">
              {{ form.processing ? 'Processing...' : 'Process Deposit' }}
            </button>
          </div>

        </form>
      </div>
    </div>

  </AppLayout>
</template>

<script setup lang="ts">
import { Link, usePage, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'

const page = usePage()
const authUser = page.props.auth 

const props = defineProps({
  account: Object,
  paymentMethods: Object,
})

const isMemberRole = computed(() => authUser.user.role?.includes('member'))
const isAdmin = computed(() => authUser.user.role?.includes('admin'))
const depositLocked = computed(() => !isAdmin.value)

const successMessage = ref(null)
const errorMessages = ref(null)

const form = useForm({
  amount: '',
  payment_method: '',
  payment_reference: '',
  description: '',
})

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)

const getAccountTypeLabel = (type) => ({
  share_capital: 'Share Capital',
  share_deposits: 'Share Deposits',
}[type] || type)

const submit = () => {
  if (depositLocked.value) return

  form.post(
    isMemberRole.value
      ? route('members.accounts.deposit', {
          member: props.account.member.id,
          account: props.account.id,
        })
      : route('accounts.deposit', props.account.id)
  )
}
</script>