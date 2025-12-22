<template>
  <AppLayout :breadcrumbs="[
    { title: 'Transactions', href: '/transactions' },
    { title: 'New Transaction', href: '#' }
  ]">

    <Head title="New Transaction" />

    <!-- FLASH BOX -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
        <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
          <div v-if="flashMessage" :class="[
      'mb-4 rounded-md p-4 shadow flex items-center gap-3 border',
      flashType === 'success'
        ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
        : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-700'
    ]">
            <p class="ml-3 text-sm">{{ flashMessage }}</p>

            <button class="ml-auto text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200"
              @click="flashMessage = null">
              ✕
            </button>
          </div>
        </transition>
      </div>


    <div class="container">

      <!-- HEADER -->
      <div class="page-header">
        <h1>New Transaction</h1>
        <p>Record deposits and withdrawals securely</p>
      </div>

      <!-- FORM -->
      <form @submit.prevent="submitTransaction" class="form-card">

        <!-- ACCOUNT -->
        <div class="form-group">
          <label>Account</label>
          <select v-model="form.account_id">
            <option value="">Select account</option>
            <option v-for="a in shareAccounts" :key="a.id" :value="a.id">
              {{ a.account_number }} –
              {{ a.member.first_name }} {{ a.member.last_name }}
            </option>
          </select>
          <span v-if="fieldError('account_id')" class="error">
            {{ fieldError('account_id') }}
          </span>
        </div>

        <!-- TRANSACTION TYPE -->
        <div class="form-group">
          <label>Transaction Type</label>
          <select v-model="form.transaction_type">
            <option value="">Select type</option>
            <option v-for="(label, key) in filteredTransactionTypes" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
          <span v-if="fieldError('transaction_type')" class="error">
            {{ fieldError('transaction_type') }}
          </span>
        </div>

        <!-- AMOUNT -->
        <div class="form-group">
          <label>Amount</label>
          <input type="number" v-model="form.amount" />
          <span v-if="fieldError('amount')" class="error">
            {{ fieldError('amount') }}
          </span>
        </div>

        <!-- PAYMENT METHOD -->
        <div class="form-group">
          <label>Payment Method</label>
          <select v-model="form.payment_method">
            <option value="">Select method</option>
            <option v-for="(label, key) in paymentMethods" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
          <span v-if="fieldError('payment_method')" class="error">
            {{ fieldError('payment_method') }}
          </span>
        </div>

        <!-- PAYMENT REFERENCE -->
        <div class="form-group">
          <label>Payment Reference</label>
          <input v-model="form.payment_reference" />
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">
          <label>Description</label>
          <textarea rows="3" v-model="form.description"></textarea>
          <span v-if="fieldError('description')" class="error">
            {{ fieldError('description') }}
          </span>
        </div>

        <!-- ACTIONS -->
        <div class="form-actions">
          <Link href="/transactions" class="btn-outline">Cancel</Link>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Submitting…' : 'Submit Transaction' }}
          </button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref, watch, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  accounts: any[]
  transactionTypes: Record<string, string>
  paymentMethods: Record<string, string>
}>()

const page = usePage()

const form = reactive({
  account_id: '',
  transaction_type: '',
  amount: '',
  description: '',
  payment_method: '',
  payment_reference: '',
})


// FLASH HANDLING
const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

watch(
  () => page.props,
  (props) => {
    if (props.flash?.success) {
      flashMessage.value = props.flash.success
      flashType.value = 'success'
    } else if (props.flash?.error) {
      flashMessage.value = props.flash.error
      flashType.value = 'error'
    } else if (props.errors?.error) {
      flashMessage.value = props.errors.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)


const loading = ref(false)
const errors = computed(() => page.props.errors || {})

function fieldError(field: string) {
  const error = errors.value?.[field]
  if (!error) return null
  return Array.isArray(error) ? error[0] : error
}

// Filter accounts to only share deposit accounts
const shareAccounts = computed(() =>
  props.accounts.filter(acc => acc.account_type === 'share_deposits' && acc.is_active)
)

// Filter transaction types to only deposit and withdrawal
const filteredTransactionTypes = computed(() =>
  Object.fromEntries(
    Object.entries(props.transactionTypes).filter(([key]) =>
      ['deposit', 'withdrawal'].includes(key)
    )
  )
)

function submitTransaction() {
  loading.value = true
  router.post(route('transactions.store'), form, {
    onFinish: () => (loading.value = false),
  })
}
</script>


<style scoped>
/* Layout */
.container {
  max-width: 800px;
  margin: auto;
  padding: 20px;
}

/* Header */
.page-header {
  background: linear-gradient(90deg, #0a2342, #0c2e55, #103a66);
  color: white;
  padding: 24px;
  border-radius: 18px;
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
}

.page-header p {
  margin-top: 6px;
  color: #cfe3ff;
}

/* Form */
.form-card {
  background: white;
  padding: 24px;
  border-radius: 18px;
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
}

.form-group {
  margin-bottom: 18px;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
}

input,
select,
textarea {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #ccc;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #0a2342;
}

/* Errors */
.error {
  color: #dc2626;
  font-size: 13px;
  margin-top: 4px;
  display: block;
}

/* Buttons */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-primary {
  background: #f97316;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 600;
}

.btn-primary:hover {
  background: #ea580c;
}

.btn-outline {
  padding: 10px 18px;
  border-radius: 10px;
  border: 1px solid #ccc;
  background: transparent;
}

/* Alerts */
.alert {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 20px;
  border-radius: 12px;
  display: flex;
  gap: 10px;
  align-items: center;
  z-index: 50;
}

.alert.success {
  background: #d1fae5;
  color: #065f46;
}

.alert.error {
  background: #fee2e2;
  color: #991b1b;
}

.alert button {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
}
</style>
