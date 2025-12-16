<template>

  <Head title="Create Payment Voucher" />

  <AppLayout :breadcrumbs="[{ title: 'Vouchers', href: '/vouchers' }, { title: 'Create' }]">

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" class="flex gap-3" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700',
    'mb-4 rounded-md p-4 shadow flex items-center'
  ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'" />
          <p class="ml-3 text-sm">{{ flashMessage }}</p>
          <button type="button" class="ml-auto text-gray-500 hover:text-gray-700" @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>

    <!-- Page Header -->
    <div class="bg-[#0a2342] rounded-xl px-6 py-6 text-white shadow-md mb-8 flex justify-between items-center">
      <div>
        <h2 class="font-semibold text-2xl">Create Payment Voucher</h2>
        <p class="text-sm opacity-80">Create a new payment voucher for approval</p>
      </div>

      <Link :href="route('vouchers.index')"
        class="bg-white text-[#0a2342] hover:bg-gray-100 font-medium px-4 py-2 rounded-lg shadow flex items-center transition">
      <ArrowBigLeft class="h-5 w-5 mr-2" />
      Back to Vouchers
      </Link>
    </div>

    <div class="py-6">
      <div class="max-w-4xl mx-auto">

        <form @submit.prevent="submit" class="space-y-8">

          <!-- CARD COMPONENT -->
          <div class="bg-white shadow-[0_3px_10px_rgb(0,0,0,0.1)] rounded-xl border">

            <!-- Section Header -->
            <div class="px-6 py-4 border-b flex flex-col">
              <h3 class="text-lg font-semibold text-[#0a2342]">Basic Information</h3>
              <p class="text-sm text-gray-600">Enter the basic details of the payment voucher</p>
            </div>

            <!-- Section Body -->
            <div class="p-6 space-y-6">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Voucher Type -->
                <div>
                  <label class="block text-sm font-medium text-[#0a2342] mb-2">
                    Voucher Type <span class="text-red-500">*</span>
                  </label>

                  <select v-model="form.voucher_type"
                    class="w-full rounded-lg border-gray-300 border p-2 focus:border-[#ff7a00] focus:ring-[#ff7a00]"
                    @change="handleVoucherTypeChange">
                    <option value="">Select Voucher Type</option>
                    <option v-for="(label, value) in voucherTypes" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>

                  <p v-if="$page.props.errors.voucher_type" class="text-red-600 text-sm mt-1">
                    {{ $page.props.errors.voucher_type }}
                  </p>
                </div>

                <!-- Amount -->
                <div>
                  <label class="block text-sm font-medium text-[#0a2342] mb-2">
                    Amount <span class="text-red-500">*</span>
                  </label>

                  <input v-model="form.amount" type="number" min="0" step="0.01" placeholder="0.00"
                    class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00] pl-3" />

                  <p v-if="$page.props.errors.amount" class="text-red-600 text-sm mt-1">
                    {{ $page.props.errors.amount }}
                  </p>
                </div>
              </div>

              <!-- Purpose -->
              <div>
                <label class="block text-sm font-medium text-[#0a2342] mb-2">
                  Purpose <span class="text-red-500">*</span>
                </label>

                <input v-model="form.purpose" type="text"
                  class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]" />

                <p v-if="$page.props.errors.purpose" class="text-red-600 text-sm mt-1">{{ $page.props.errors.purpose }}
                </p>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-[#0a2342] mb-2">
                  Description
                </label>

                <textarea v-model="form.description" rows="3"
                  class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]"></textarea>

                <p v-if="$page.props.errors.description" class="text-red-600 text-sm mt-1">{{
    $page.props.errors.description }}</p>
              </div>

            </div>
          </div>

          <!-- Payee Type -->
          <div>
            <label class="block text-sm font-medium text-[#0a2342] mb-2">
              Payee Type <span class="text-red-500">*</span>
            </label>

            <select v-model="form.payee_type"
              class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]">
              <option value="">Select Payee Type</option>
              <option value="direct">Direct Payee</option>
              <option value="member">Member</option>
            </select>

          </div>


          <!-- Member Selection -->
          <div v-if="form.payee_type === 'member'">
            <label class="block text-sm font-medium text-[#0a2342] mb-2">
              Select Member <span class="text-red-500">*</span>
            </label>

            <select v-model="form.member_id"
              class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]">
              <option value="">Select Member</option>
              <option v-for="member in props.members" :key="member.id" :value="member.id">
                {{ member.first_name }} {{ member.last_name }} – {{ member.membership_id }}
              </option>
            </select>
          </div>


          <!-- PAYEE INFORMATION -->
          <div class="bg-white shadow-[0_3px_10px_rgb(0,0,0,0.1)] rounded-xl border">
            <div class="px-6 py-4 border-b">
              <h3 class="text-lg font-semibold text-[#0a2342]">Payee Information</h3>
              <p class="text-sm text-gray-600">Details about who will be paid</p>
            </div>

            <div class="p-6 space-y-6">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Payee Name -->
                <div>
                  <label class="block text-sm font-medium text-[#0a2342] mb-2">
                    Payee Name <span class="text-red-500">*</span>
                  </label>

                  <input v-model="form.payee_name" type="text"
                    class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]" />

                  <p v-if="$page.props.errors.payee_name" class="text-red-600 text-sm mt-1">
                    {{ $page.props.errors.payee_name }}
                  </p>
                </div>

                <!-- Payee Phone -->
                <div>
                  <label class="block text-sm font-medium text-[#0a2342] mb-2">
                    Phone Number
                  </label>

                  <input v-model="form.payee_phone" type="text" placeholder="+2547..."
                    class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]" />

                  <p v-if="$page.props.errors.payee_phone" class="text-red-600 text-sm mt-1">
                    {{ $page.props.errors.payee_phone }}
                  </p>
                </div>

              </div>
            </div>
          </div>


          <!-- Payment Type -->
          <div>
            <label class="block text-sm font-medium text-[#0a2342] mb-2">
              Payment Type <span class="text-red-500">*</span>
            </label>

            <select v-model="form.payment_type"
              class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]">
              <option value="">Select Payment Type</option>
              <option value="mpesa">M-Pesa</option>
              <option value="bank">Bank Transfer</option>
            </select>
          </div>

          <div v-if="form.payment_type === 'mpesa'">
            <label class="block text-sm font-medium text-[#0a2342] mb-2">
              M-Pesa Number <span class="text-red-500">*</span>
            </label>

            <input v-model="form.payment_phone" type="text" placeholder="07XXXXXXXX"
              class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]" />
          </div>

          <div v-if="form.payment_type === 'bank'" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
              <label class="block text-sm font-medium text-[#0a2342] mb-2">
                Bank Name <span class="text-red-500">*</span>
              </label>
              <input v-model="form.bank_name" type="text"
                class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]" />
            </div>

            <div>
              <label class="block text-sm font-medium text-[#0a2342] mb-2">
                Account Number <span class="text-red-500">*</span>
              </label>
              <input v-model="form.payment_account" type="text"
                class="w-full rounded-lg border p-2 border-gray-300 focus:border-[#ff7a00] focus:ring-[#ff7a00]" />
            </div>

          </div>




          <!-- SUPPORTING DOCUMENTS -->
          <div class="bg-white shadow-[0_3px_10px_rgb(0,0,0,0.1)] rounded-xl border">
            <div class="px-6 py-4 border-b">
              <h3 class="text-lg font-semibold text-[#0a2342]">Supporting Documents</h3>
              <p class="text-sm text-gray-600">Upload receipts or proof</p>
            </div>

            <div class="p-6">

              <!-- Upload Box -->
              <div
                class="border-2 border-dashed border-gray-300 p-6 text-center rounded-xl hover:bg-gray-50 transition">

                <File class="mx-auto h-12 w-12 text-gray-400" />

                <label class="cursor-pointer mt-4 block">
                  <span class="font-medium text-[#0a2342]">Upload Files</span>
                  <span class="block text-sm text-gray-500">PDF, DOC, JPG, PNG (5MB max)</span>

                  <input type="file" multiple ref="fileInput" class="hidden" @change="handleFileUpload" />

                  <div
                    class="inline-block mt-3 bg-[#ff7a00] text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition shadow">
                    Choose Files
                  </div>
                </label>

              </div>

              <!-- Selected Files -->
              <div v-if="form.supporting_documents.length > 0" class="mt-4 space-y-3">
                <h4 class="text-sm font-medium text-[#0a2342]">Selected Files</h4>

                <div v-for="(file, index) in form.supporting_documents" :key="index"
                  class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">

                  <div class="flex items-center">
                    <File class="h-5 w-5 text-gray-500 mr-3" />
                    <div>
                      <p class="text-sm font-medium">{{ file.name }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                    </div>
                  </div>

                  <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700 transition">
                    <Cross class="h-5 w-5" />
                  </button>

                </div>
              </div>

            </div>
          </div>

          <!-- FORM ACTIONS -->
          <div class="flex justify-end space-x-4 mt-6">

            <Link :href="route('vouchers.index')"
              class="px-4 py-2 rounded-lg border bg-white text-[#0a2342] hover:bg-gray-50 transition shadow-sm">
            Cancel
            </Link>

            <button type="submit" :disabled="form.processing"
              class="px-5 py-2 bg-[#0a2342] text-white rounded-lg shadow hover:bg-[#0c2f63] transition disabled:opacity-50 flex items-center">
              <svg v-if="form.processing" class="animate-spin h-4 w-4 mr-2 text-white" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4z" />
              </svg>
              <span>{{ form.processing ? "Creating..." : "Create Voucher" }}</span>
            </button>


          </div>

        </form>
      </div>
    </div>

    <!-- FULL SCREEN LOADER -->
    <div v-if="form.processing"
      class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[9999]">
      <div class="bg-white p-6 rounded-xl shadow-xl flex flex-col items-center space-y-3">
        <svg class="animate-spin h-8 w-8 text-[#0a2342]" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.38 0 0 5.38 0 12h4z" />
        </svg>
        <p class="text-[#0a2342] font-medium">Creating Voucher...</p>
      </div>
    </div>

  </AppLayout>
</template>



<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import { ArrowBigLeft, Cross, File } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'


const props = defineProps({
  budgetItems: Array,
  pendingLoans: Array,
  voucherTypes: Object,
  members: Array,
})


// Flash handling
const page = usePage()
const flash = computed(() => page.props?.flash || {})

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
      window.scrollTo({ top: 0, behavior: 'smooth' })
      flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)


const fileInput = ref(null)

// Initialize the form with useForm
const form = useForm({
  voucher_type: '',
  payee_type: '',
  member_id: '',
  payee_name: '',
  payee_phone: '',
  payment_phone: '',
  payment_account: '',
  payment_type: '',
  bank_name: '',
  amount: '',
  purpose: '',
  description: '',
  budget_item_id: '',
  loan_id: '',
  supporting_documents: []
})


watch(() => form.member_id, (id) => {
  if (!id) return

  const member = props.members.find(m => m.id === id)
  if (member) {
    form.payee_name = `${member.first_name} ${member.last_name}`
    form.payee_phone = member.phone
  }
})



const selectedBudgetItem = computed(() => {
  if (!form.budget_item_id) return null
  return props.budgetItems.find(item => item.id === parseInt(form.budget_item_id))
})

const selectedLoan = computed(() => {
  if (!form.loan_id) return null
  return props.pendingLoans.find(loan => loan.id === parseInt(form.loan_id))
})

const handleVoucherTypeChange = () => {
  // Clear related fields when voucher type changes
  if (form.voucher_type !== 'loan_disbursement') {
    form.loan_id = ''
  }

  // Auto-populate amount and payee for loan disbursement
  if (form.voucher_type === 'loan_disbursement' && form.loan_id) {
    const loan = selectedLoan.value
    if (loan) {
      form.amount = loan.approved_amount
      form.payee_name = `${loan.member?.first_name} ${loan.member?.last_name}`
      form.payee_phone = loan.member?.phone
    }
  }
}

// Watch for loan selection changes
watch(() => form.loan_id, (newLoanId) => {
  if (newLoanId && form.voucher_type === 'loan_disbursement') {
    const loan = selectedLoan.value
    if (loan) {
      form.amount = loan.approved_amount
      form.payee_name = `${loan.member?.first_name} ${loan.member?.last_name}`
      form.payee_phone = loan.member?.phone
      form.purpose = `Loan disbursement for ${loan.loan_number}`
    }
  }
})

watch(() => form.payment_type, () => {
  form.bank_name = ''
  form.payee_account = ''
})


watch(() => form.payment_type, (type) => {
  if (type === 'mpesa' && !form.payment_phone) {
    form.payment_phone = form.payee_phone
  }

  if (type === 'bank') {
    form.payment_phone = ''
  }
})



const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  const validFiles = []

  files.forEach(file => {
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
      alert(`File ${file.name} is too large. Maximum size is 5MB.`)
      return
    }

    // Validate file type
    const allowedTypes = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'image/jpeg',
      'image/jpg',
      'image/png'
    ]

    if (!allowedTypes.includes(file.type)) {
      alert(`File ${file.name} has an invalid type. Only PDF, DOC, DOCX, JPG, JPEG, PNG are allowed.`)
      return
    }

    validFiles.push(file)
  })

  if (validFiles.length > 0) {
    form.supporting_documents = [...(form.supporting_documents || []), ...validFiles]
  }

  // Clear the input
  event.target.value = ''
}

const removeFile = (index) => {
  form.supporting_documents.splice(index, 1)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount)
}

const submit = () => {
  form.post(route('vouchers.store'), {
    forceFormData: true,
    onSuccess: () => {
      // Form will redirect on success
      console.log('Voucher created successfully')
    },
    onError: (errors) => {
      console.log('Validation errors:', errors)
    }
  })
}
</script>