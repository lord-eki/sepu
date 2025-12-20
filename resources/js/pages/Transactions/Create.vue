<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Transactions', href: '/transactions' },
      { title: 'New Transaction' }
    ]"
  >
    <Head title="New Transaction" />

    <!-- GLOBAL MESSAGE -->
    <div
      v-if="message.visible"
      :class="message.type === 'success' ? 'alert success' : 'alert error'"
    >
      <span>{{ message.text }}</span>
      <button @click="message.visible = false">&times;</button>
    </div>

    <div class="container">

      <!-- HEADER -->
      <div class="page-header">
        <h1>New Transaction</h1>
        <p>Record deposits, withdrawals and transfers securely</p>
      </div>

      <!-- FORM -->
      <form @submit.prevent="submitTransaction" class="form-card">

        <!-- MEMBER -->
        <div class="form-group">
          <label>Member</label>
          <select v-model="form.member_id">
            <option value="">Select member</option>
            <option v-for="m in members" :key="m.id" :value="m.id">
              {{ m.first_name }} {{ m.last_name }} ({{ m.membership_id }})
            </option>
          </select>
          <span v-if="fieldError('member_id')" class="error">
            {{ fieldError('member_id') }}
          </span>
        </div>

        <!-- ACCOUNT -->
        <div class="form-group">
          <label>Account</label>
          <select v-model="form.account_id">
            <option value="">Select account</option>
            <option v-for="a in accounts" :key="a.id" :value="a.id">
              {{ a.account_number }} –
              {{ a.member ? `${a.member.first_name} ${a.member.last_name}` : 'No Member' }}
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
            <option v-for="(label, key) in transactionTypes" :key="key" :value="key">
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
          <span v-if="fieldError('payment_reference')" class="error">
            {{ fieldError('payment_reference') }}
          </span>
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">
          <label>Description</label>
          <textarea rows="3" v-model="form.description"></textarea>
          <span v-if="fieldError('description')" class="error">
            {{ fieldError('description') }}
          </span>
        </div>

        <!-- DESTINATION ACCOUNT -->
        <div v-if="form.transaction_type === 'transfer'" class="form-group">
          <label>Destination Account</label>
          <select v-model="form.destination_account_id">
            <option value="">Select destination</option>
            <option v-for="a in accounts" :key="a.id" :value="a.id">
              {{ a.account_number }} –
              {{ a.member ? `${a.member.first_name} ${a.member.last_name}` : 'No Member' }}
            </option>
          </select>
          <span v-if="fieldError('destination_account_id')" class="error">
            {{ fieldError('destination_account_id') }}
          </span>
        </div>

        <!-- ACTIONS -->
        <div class="form-actions">
          <Link href="/transactions" class="btn-outline">Cancel</Link>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Submitting...' : 'Submit Transaction' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  accounts: any[]
  members: any[]
  transactionTypes: Record<string, string>
  paymentMethods: Record<string, string>
}>()

const page = usePage()

const form = reactive({
  member_id: '',
  account_id: '',
  transaction_type: '',
  amount: '',
  description: '',
  payment_method: '',
  payment_reference: '',
  destination_account_id: '',
})

const loading = ref(false)
const errors = computed(() => page.props.errors || {})

const message = reactive({
  text: '',
  type: 'success',
  visible: false,
})

function fieldError(field: string) {
  const error = errors.value?.[field]
  if (!error) return null
  return Array.isArray(error) ? error[0] : error
}

function submitTransaction() {
  loading.value = true

  router.post('/transactions', form, {
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
