<template>
  <AppLayout
    :breadcrumbs="[
      {
        title: isMemberRole ? 'My Accounts' : 'Accounts',
        href: isMemberRole ? route('my-accounts') : route('accounts.index')
      },
      {
        title:
          account.account_type === 'share_capital'
            ? 'Share Capital'
            : 'Deposit'
      }
    ]"
  >
    <!-- Hero -->
    <section
      class="relative overflow-hidden rounded-b-3xl bg-gradient-to-r from-slate-900 via-blue-900 to-orange-700 text-white"
    >
      <div class="absolute inset-0 bg-black/10"></div>

      <div
        class="relative mx-auto flex max-w-7xl flex-col gap-6 px-6 py-10 md:flex-row md:items-center md:justify-between"
      >
        <div>
          <p class="text-sm uppercase tracking-widest text-blue-200">
            Account Details
          </p>

          <h1 class="mt-2 text-3xl font-bold">
            {{
              account.account_type === 'share_capital'
                ? 'Share Capital Account'
                : 'Deposit Account'
            }}
          </h1>

          <p class="mt-3 text-blue-100">
            {{ account.account_number }}
          </p>

          <p class="text-blue-200">
            {{ account.member.first_name }}
            {{ account.member.last_name }}
          </p>
        </div>

        <Link
          :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
          class="inline-flex items-center justify-center rounded-xl bg-white/10 px-6 py-3 backdrop-blur transition hover:bg-white/20"
        >
          ← Back
        </Link>
      </div>
    </section>

    <div
      class="mx-auto max-w-7xl space-y-8 px-5 py-8 bg-slate-50 dark:bg-slate-950"
    >
      <!-- Summary Cards -->

      <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div
          class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
        >
          <p
            class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400"
          >
            Account Type
          </p>

          <h3 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">
            {{ getAccountTypeLabel(account.account_type) }}
          </h3>
        </div>

        <div
          class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
        >
          <p
            class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400"
          >
            Current Balance
          </p>

          <h3 class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">
            {{ formatCurrency(account.balance) }}
          </h3>
        </div>

        <div
          class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
        >
          <p
            class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400"
          >
            Available Balance
          </p>

          <h3 class="mt-3 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
            {{ formatCurrency(account.available_balance) }}
          </h3>
        </div>

        <div
          class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
        >
          <p
            class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400"
          >
            Status
          </p>

          <span
            class="mt-4 inline-flex rounded-full px-4 py-2 text-sm font-semibold"
            :class="
              account.is_active
                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
            "
          >
            {{ account.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
      </div>

      <!-- MEMBER -->

      <div
        v-if="isMemberRole"
        class="rounded-3xl border border-slate-200 bg-white p-8 shadow-lg dark:border-slate-800 dark:bg-slate-900"
      >
        <div
          class="rounded-2xl border border-blue-200 bg-blue-50 p-8 dark:border-blue-800 dark:bg-blue-950/30"
        >
          <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-300">
            Deposit Information
          </h2>

          <p class="mt-4 text-slate-700 dark:text-slate-300">
            Deposits into this account are processed by authorized SACCO staff
            after payment verification.
          </p>

          <div class="mt-8">
            <h3 class="font-semibold text-slate-900 dark:text-white">
              How to make a deposit
            </h3>

            <ol
              class="mt-4 list-decimal space-y-3 pl-5 text-slate-600 dark:text-slate-400"
            >
              <li>Visit the SACCO office or approved collection point.</li>
              <li>Make payment using an approved payment method.</li>
              <li>Keep your payment reference.</li>
              <li>Your account will be updated after verification.</li>
            </ol>
          </div>

          <div
            class="mt-8 rounded-2xl border border-yellow-300 bg-yellow-50 p-5 text-yellow-900 dark:border-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300"
          >
            <strong>Important:</strong>

            Members cannot directly credit their accounts from the portal.
            Deposits are posted after payment confirmation by authorized staff.
          </div>
        </div>
      </div>

      <!-- ADMIN -->

      <div
        v-else
        class="rounded-3xl border border-slate-200 bg-white p-8 shadow-lg dark:border-slate-800 dark:bg-slate-900"
      >
        <h2 class="mb-8 text-2xl font-bold text-slate-900 dark:text-white">
          Process Deposit
        </h2>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label
              class="mb-2 block font-medium text-slate-700 dark:text-slate-300"
            >
              Amount
            </label>

            <input
              v-model.number="form.amount"
              type="number"
              min="100"
              required
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-orange-500 focus:ring-4 focus:ring-orange-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-orange-900"
            />
          </div>

          <div>
            <label
              class="mb-2 block font-medium text-slate-700 dark:text-slate-300"
            >
              Payment Method
            </label>

            <select
              v-model="form.payment_method"
              required
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-orange-500 focus:ring-4 focus:ring-orange-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-orange-900"
            >
              <option value="">Select Method</option>

              <option
                v-for="(label, value) in paymentMethods"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </select>
          </div>

          <div v-if="form.payment_method">
            <label
              class="mb-2 block font-medium text-slate-700 dark:text-slate-300"
            >
              Payment Reference
            </label>

            <input
              v-model="form.payment_reference"
              required
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-orange-500 focus:ring-4 focus:ring-orange-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-orange-900"
            />
          </div>

          <div>
            <label
              class="mb-2 block font-medium text-slate-700 dark:text-slate-300"
            >
              Description
            </label>

            <textarea
              v-model="form.description"
              rows="4"
              class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-orange-500 focus:ring-4 focus:ring-orange-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-orange-900"
            />
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-gradient-to-r from-orange-600 to-orange-500 px-8 py-3 font-semibold text-white shadow-lg transition hover:scale-[1.02] hover:from-orange-700 hover:to-orange-600 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{
                form.processing
                  ? 'Processing...'
                  : 'Process Deposit'
              }}
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
import { ref, computed, watch } from 'vue'

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

watch(
  () => props.account.account_type,
  (type) => {
    if (type === 'share_capital') {
      form.description = 'Share capital contribution'
    }
  },
  { immediate: true }
)

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)

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