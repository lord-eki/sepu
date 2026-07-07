<template>
  <AppLayout
    :breadcrumbs="[
      {
        title: isMemberRole ? 'My Accounts' : 'Accounts',
        href: isMemberRole ? route('my-accounts') : route('accounts.index')
      },
      {
        title: account.account_type === 'share_capital'
          ? 'Share Capital'
          : 'Deposit'
      }
    ]"
  >

    <!-- HEADER -->
    <div
      class="bg-gradient-to-r from-[#0B2B40] via-blue-900 to-orange-900 text-white rounded-b-3xl px-[5%] py-8 shadow-xl"
    >
      <div class="flex justify-between items-center flex-wrap gap-4">

        <div>
          <h1 class="text-2xl font-bold">
            {{ account.account_type === 'share_capital'
                ? 'Share Capital Account'
                : 'Deposit Account'
            }}
          </h1>

          <p class="text-slate-200 mt-1">
            {{ account.account_number }}
          </p>

          <p class="text-slate-300">
            {{ account.member.first_name }}
            {{ account.member.last_name }}
          </p>
        </div>

        <Link
          :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
          class="bg-white/10 border border-white/20 px-5 py-2 rounded-lg hover:bg-white/20"
        >
          Back
        </Link>

      </div>
    </div>

    <div class="max-w-5xl mx-auto py-8 px-5">

      <!-- ACCOUNT SUMMARY -->

      <div class="grid md:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs uppercase text-gray-500">
            Account Type
          </p>

          <h3 class="font-semibold mt-2">
            {{ getAccountTypeLabel(account.account_type) }}
          </h3>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs uppercase text-gray-500">
            Current Balance
          </p>

          <h3 class="text-xl font-bold text-[#0B2B40]">
            {{ formatCurrency(account.balance) }}
          </h3>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs uppercase text-gray-500">
            Available Balance
          </p>

          <h3 class="text-xl font-bold text-blue-900">
            {{ formatCurrency(account.available_balance) }}
          </h3>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-xs uppercase text-gray-500">
            Status
          </p>

          <span
            class="inline-block mt-2 px-3 py-1 rounded-full text-sm"
            :class="account.is_active
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700'"
          >
            {{ account.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

      </div>

      <!-- ========================= -->
      <!-- MEMBER VIEW -->
      <!-- ========================= -->

      <div
        v-if="isMemberRole"
        class="bg-white rounded-2xl shadow-lg p-8"
      >

        <div
          class="bg-blue-50 border border-blue-200 rounded-xl p-6"
        >

          <h2 class="text-xl font-semibold text-blue-900">
            Deposit Information
          </h2>

          <p class="mt-3 text-gray-700">
            Deposits into this account are processed by authorized SACCO staff
            after payment verification.
          </p>

          <div class="mt-6">

            <h3 class="font-semibold">
              How to make a deposit
            </h3>

            <ol class="list-decimal ml-5 mt-3 space-y-2 text-gray-600">

              <li>
                Visit the SACCO office or approved collection point.
              </li>

              <li>
                Make payment using an approved payment method.
              </li>

              <li>
                Keep your payment reference.
              </li>

              <li>
                Your account will be updated after verification.
              </li>

            </ol>

          </div>

          <div
            class="mt-8 bg-yellow-50 border border-yellow-300 rounded-lg p-4"
          >
            <strong>Note:</strong>

            Members cannot directly credit their accounts from the portal.
            Deposits are posted by authorized administrators after payment
            confirmation.
          </div>

        </div>

      </div>

      <!-- ========================= -->
      <!-- ADMIN VIEW -->
      <!-- ========================= -->

      <div
        v-else
        class="bg-white rounded-2xl shadow-lg p-8"
      >

        <h2 class="text-xl font-semibold mb-6">
          Process Deposit
        </h2>

        <form
          @submit.prevent="submit"
          class="space-y-6"
        >

          <div>

            <label class="block mb-2 font-medium">
              Amount
            </label>

            <input
              v-model.number="form.amount"
              type="number"
              min="100"
              class="w-full rounded-lg border p-3"
              required
            />

          </div>

          <div>

            <label class="block mb-2 font-medium">
              Payment Method
            </label>

            <select
              v-model="form.payment_method"
              class="w-full rounded-lg border p-3"
              required
            >

              <option value="">
                Select Method
              </option>

              <option
                v-for="(label,value) in paymentMethods"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>

            </select>

          </div>

          <div v-if="form.payment_method">

            <label class="block mb-2 font-medium">
              Payment Reference
            </label>

            <input
              v-model="form.payment_reference"
              class="w-full rounded-lg border p-3"
              required
            />

          </div>

          <div>

            <label class="block mb-2 font-medium">
              Description
            </label>

            <textarea
              v-model="form.description"
              rows="3"
              class="w-full rounded-lg border p-3"
            />

          </div>

          <div class="flex justify-end">

            <button
              type="submit"
              :disabled="form.processing"
              class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-lg"
            >
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