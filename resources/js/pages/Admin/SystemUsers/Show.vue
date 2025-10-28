<script setup lang="ts">
/**
 * Admin/SystemUsers/Show.vue
 * - Shows a system user's details, recent loans and transactions
 * - Actions: Edit, Toggle Active, Reset Password (modal), Delete (with confirm)
 * - Theme: dark blue (#0b1b3f), orange (#ff7b00), white + neutrals
 *
 * Expects prop: user (Eloquent User with loans and transactions preloaded)
 */

import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Trash2, Edit2, UserCheck, Key, ToggleLeft } from 'lucide-vue-next'

const props = defineProps({
  user: Object, // loaded with loans and transactions by controller
})

/* Password form (for admin to reset user password) */
const pwdForm = useForm({
  password: '',
  password_confirmation: '',
})

const pwdModal = ref(false)
const deleteConfirm = ref(false)
const processingToggle = ref(false)
const processingDelete = ref(false)

/* Toggle active status */
const toggleStatus = () => {
  if (props.user.id === $page.props.auth.user?.id) {
    // Prevent self deactivate handled server-side too, but UX notice
    alert("You cannot deactivate your own account.")
    return
  }
  processingToggle.value = true
  router.post(route('system-users.toggle-status', props.user.id), {
    preserveState: true,
    onFinish: () => (processingToggle.value = false),
  })
}

/* Delete user (with check done server-side) */
const confirmDelete = () => {
  if (props.user.id === $page.props.auth.user?.id) {
    alert("You cannot delete your own account.")
    return
  }
  deleteConfirm.value = true
}

const doDelete = () => {
  processingDelete.value = true
  router.delete(route('system-users.destroy', props.user.id), {
    preserveState: true,
    onFinish: () => (processingDelete.value = false),
  })
}

/* Submit password update */
const submitPassword = () => {
  pwdForm.post(route('system-users.update-password', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      pwdModal.value = false
      pwdForm.reset('password', 'password_confirmation')
    },
  })
}

/* small helper for date formatting */
const fmtDateTime = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleString()
}

/* currency */
const fmtCurrency = (v) => {
  const n = Number(v) || 0
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(n)
}
</script>

<template>
  <AppLayout>
    <Head title="System User" />

    <div class="max-w-6xl mx-auto p-6 space-y-6 animate-fadeIn">
      <!-- Top actions -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#0b1b3f] dark:text-white">{{ user.name }}</h1>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
            {{ user.email }} • {{ user.phone }}
          </p>
          <div class="mt-3 flex items-center gap-2">
            <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">{{ user.role.replace('_', ' ') }}</span>
            <span v-if="user.is_active" class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Active</span>
            <span v-else class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">Inactive</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('system-users.edit', user.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition"
          >
            <Edit2 class="h-4 w-4 text-[#0b1b3f]" /> Edit
          </Link>

          <button
            @click="toggleStatus"
            :disabled="processingToggle"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#0b1b3f] text-white rounded-lg hover:bg-[#0f294b] transition"
          >
            <ToggleLeft class="h-4 w-4" /> {{ user.is_active ? 'Deactivate' : 'Activate' }}
          </button>

          <button @click="pwdModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
            <Key class="h-4 w-4" /> Reset Password
          </button>

          <button
            @click="confirmDelete"
            class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
          >
            <Trash2 class="h-4 w-4" /> Delete
          </button>
        </div>
      </div>

      <!-- Main grid: profile left, history right -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Profile Card -->
        <div class="lg:col-span-1 bg-white dark:bg-[#0b1b3f] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5">
          <div class="flex flex-col items-start gap-3">
            <div class="w-full flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-300">Account created</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ fmtDateTime(user.created_at) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-300">Last login</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ fmtDateTime(user.last_login_at) }}</p>
              </div>
            </div>

            <div class="w-full border-t pt-4">
              <p class="text-sm text-gray-500 dark:text-gray-300">Contact</p>
              <p class="font-medium text-gray-800 dark:text-white">{{ user.phone }}</p>
              <p class="font-medium text-gray-800 dark:text-white">{{ user.email }}</p>
            </div>

            <div class="w-full border-t pt-4">
              <p class="text-sm text-gray-500 dark:text-gray-300">Role & Status</p>
              <div class="mt-2 flex items-center gap-2">
                <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs">{{ user.role }}</span>
                <span v-if="user.is_active" class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">Active</span>
                <span v-else class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs">Inactive</span>
              </div>
            </div>

            <div v-if="user.notes" class="w-full border-t pt-4">
              <p class="text-sm text-gray-500 dark:text-gray-300">Notes</p>
              <p class="text-sm text-gray-700 dark:text-gray-200 mt-2 whitespace-pre-line">{{ user.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Loans + Transactions (spans 2 cols) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Recent Loans -->
          <div class="bg-white dark:bg-[#0b1b3f] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-[#0b1b3f] dark:text-white">Recent Loans</h3>
              <small class="text-sm text-gray-500 dark:text-gray-300">{{ user.loans?.length || 0 }} shown</small>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                  <tr>
                    <th class="px-4 py-2">Loan#</th>
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Amount</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Disbursed</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(loan, idx) in (user.loans || [])" :key="loan.id" class="border-b dark:border-gray-700 hover:bg-orange-50 dark:hover:bg-gray-800 transition">
                    <td class="px-4 py-3 font-medium">#{{ loan.id }}</td>
                    <td class="px-4 py-3">{{ loan.loan_product?.name || '—' }}</td>
                    <td class="px-4 py-3">{{ fmtCurrency(loan.amount) }}</td>
                    <td class="px-4 py-3">
                      <span :class="loan.status === 'active' ? 'bg-green-100 text-green-700' : (loan.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')" class="px-2 py-1 rounded-full text-xs font-semibold">
                        {{ loan.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3">{{ new Date(loan.disbursed_at || loan.created_at).toLocaleDateString() }}</td>
                  </tr>

                  <tr v-if="!(user.loans?.length)">
                    <td colspan="5" class="text-center py-6 text-gray-400">No loans available</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Recent Transactions -->
          <div class="bg-white dark:bg-[#0b1b3f] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-[#0b1b3f] dark:text-white">Recent Transactions</h3>
              <small class="text-sm text-gray-500 dark:text-gray-300">{{ user.transactions?.length || 0 }} shown</small>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                  <tr>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Amount</th>
                    <th class="px-4 py-2">Balance After</th>
                    <th class="px-4 py-2">Description</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(tx, idx) in (user.transactions || [])" :key="tx.id" class="border-b dark:border-gray-700 hover:bg-orange-50 dark:hover:bg-gray-800 transition">
                    <td class="px-4 py-3">{{ fmtDateTime(tx.created_at) }}</td>
                    <td class="px-4 py-3">{{ (tx.transaction_type || '').replace('_',' ') }}</td>
                    <td class="px-4 py-3">{{ fmtCurrency(tx.amount) }}</td>
                    <td class="px-4 py-3">{{ fmtCurrency(tx.balance_after) }}</td>
                    <td class="px-4 py-3">{{ tx.description || '—' }}</td>
                  </tr>

                  <tr v-if="!(user.transactions?.length)">
                    <td colspan="5" class="text-center py-6 text-gray-400">No transactions available</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete confirmation modal -->
      <div v-if="deleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-semibold text-[#0b1b3f] dark:text-white">Confirm Delete</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
            Deleting this user is irreversible. If the user has loans or transactions, deletion will be blocked.
            Are you sure you want to proceed?
          </p>

          <div class="mt-6 flex justify-end gap-3">
            <button @click="deleteConfirm = false" class="px-4 py-2 rounded-lg border bg-white text-gray-700">Cancel</button>
            <button @click="doDelete" :disabled="processingDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white">
              {{ processingDelete ? 'Deleting...' : 'Delete User' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Password reset modal -->
      <div v-if="pwdModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-semibold text-[#0b1b3f] dark:text-white">Reset Password</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Enter a new password for this user.</p>

          <form @submit.prevent="submitPassword" class="mt-4 space-y-3">
            <div>
              <label class="block text-sm text-gray-700 dark:text-gray-300">New password</label>
              <input v-model="pwdForm.password" type="password" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 bg-white dark:bg-gray-800 dark:text-white" />
              <p v-if="pwdForm.errors.password" class="text-xs text-red-500 mt-1">{{ pwdForm.errors.password }}</p>
            </div>

            <div>
              <label class="block text-sm text-gray-700 dark:text-gray-300">Confirm password</label>
              <input v-model="pwdForm.password_confirmation" type="password" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-700 p-2 bg-white dark:bg-gray-800 dark:text-white" />
            </div>

            <div class="flex justify-end gap-3 mt-4">
              <button type="button" @click="pwdModal = false" class="px-4 py-2 rounded-lg border bg-white text-gray-700">Cancel</button>
              <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500 text-white">{{ pwdForm.processing ? 'Saving...' : 'Save Password' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn { animation: fadeIn 0.45s ease-out; }
</style>
