<template>
  <AppLayout :breadcrumbs="[
    { title: 'Members', href: route('members.index') },
    { title: `${member.first_name} ${member.last_name}` }
  ]">

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700',
    'mb-4 rounded-xl p-4 shadow-sm flex items-center'
  ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'" />
          <div class="flex gap-2 items-center">
            <p class="ml-3 text-sm">{{ flashMessage }}</p>
            <button @click="flashMessage = null" class="ml-auto text-gray-500 hover:text-gray-700">x</button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between  max-sm:mx-2 gap-4 px-4 sm:px-8 py-6 bg-[#0a2342] text-white rounded-b-3xl shadow-md">
      <div class="flex items-center gap-3">
        <Link :href="route('members.index')" class="hover:text-orange-400 transition">
        <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="font-bold text-lg sm:text-xl">{{ member.first_name }} {{ member.last_name }}</h2>
          <p class="text-sm opacity-75">Member ID: {{ member.membership_id }}</p>
        </div>
      </div>

      <div v-if="canEdit" class="flex flex-wrap gap-2">
        <Link :href="route('members.edit', member.id)"
          class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
        <Pencil class="w-4 h-4" /> Edit
        </Link>

        <div class="relative" ref="dropdown">
          <button @click="showDropdown = !showDropdown"
            class="inline-flex items-center gap-2 bg-white text-[#0a2342] border border-gray-200 rounded-xl px-4 py-2 text-sm font-medium hover:bg-gray-50">
            Actions
            <ChevronDown class="w-4 h-4" />
          </button>
          <div v-if="showDropdown"
            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 z-10 border border-gray-100">
            <button v-if="member.membership_status !== 'active' && canManageStatus" @click="openConfirm('activate')"
              class="block w-full text-left px-4 py-2 hover:cursor-pointer text-sm hover:bg-blue-50 text-gray-700">
              Activate Member
            </button>
            <button v-if="member.membership_status === 'active' && canManageStatus" @click="openConfirm('deactivate')"
              class="block w-full text-left px-4 py-2 hover:cursor-pointer text-sm hover:bg-blue-50 text-gray-700">
              Deactivate Member
            </button>
            <button v-if="member.membership_status !== 'suspended' && canManageStatus" @click="openConfirm('suspend')"
              class="block w-full text-left px-4 py-2 hover:cursor-pointer  text-sm hover:bg-blue-50 text-gray-700">
              Suspend Member
            </button>

            <!-- Confirmation Modal -->
            <div v-if="showConfirmModal" class="fixed inset-0 flex items-center justify-center bg-black/70 z-50">
              <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Confirm Action</h3>
                <p class="text-gray-600 mb-6">
                  Are you sure you want to <span class="font-semibold text-orange-600">{{ actionType }}</span> this
                  member?
                </p>

                <div class="flex justify-end space-x-3">
                  <button @click="showConfirmModal = false"
                    class="px-4 py-2 bg-gray-200 hover:cursor-pointer text-gray-800 rounded-md hover:bg-gray-300">
                    Cancel
                  </button>
                  <button @click="updateStatus"
                    class="px-4 py-2 bg-[#0a2342] hover:cursor-pointer text-white rounded-md hover:bg-orange-600">
                    Yes, {{ actionType }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="py-10 px-4 sm:px-8 max-w-7xl mx-auto space-y-8">

      <!-- Profile Overview -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-6 flex flex-col md:flex-row items-center gap-6 border-b border-gray-100">
          <div>
            <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`" :alt="member.first_name"
              class="h-24 w-24 rounded-full object-cover border-2 border-orange-500" />
            <div v-else
              class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center border-2 border-orange-500">
              <User class="h-10 w-10 text-gray-600" />
            </div>
          </div>

          <div class="flex-1">
            <h3 class="text-lg font-semibold text-[#0a2342]">
              {{ member.first_name }} {{ member.last_name }}
            </h3>
            <p class="text-sm text-gray-500">{{ member.membership_id }}</p>
            <div class="mt-2 flex items-center gap-3">
              <span :class="[
    'inline-flex px-3 py-1 rounded-full text-xs font-medium',
    member.membership_status === 'active' ? 'bg-green-100 text-green-700' :
      member.membership_status === 'inactive' ? 'bg-red-100 text-red-700' :
        'bg-yellow-100 text-yellow-700'
  ]">
                {{ member.membership_status }}
              </span>
              <span class="text-sm text-gray-500">Joined {{ formatDate(member.membership_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-6">
          <div class="text-center bg-blue-50 rounded-xl p-4">
            <p class="text-lg sm:text-xl font-semibold text-[#0a2342]">{{ formatCurrency(stats.total_savings) }}</p>
            <p class="text-sm text-gray-600">Total Savings</p>
          </div>
          <div class="text-center bg-blue-50 rounded-xl p-4">
            <p class="text-lg sm:text-xl font-semibold text-[#0a2342]">{{ formatCurrency(stats.total_shares) }}</p>
            <p class="text-sm text-gray-600">Total Shares</p>
          </div>
          <div class="text-center bg-blue-50 rounded-xl p-4">
            <p class="text-lg sm:text-xl font-semibold text-[#0a2342]">{{ formatCurrency(stats.total_loans) }}</p>
            <p class="text-sm text-gray-600">Active Loans</p>
          </div>
          <div class="text-center bg-blue-50 rounded-xl p-4">
            <p class="text-lg sm:text-xl font-semibold text-[#0a2342]">{{ formatCurrency(stats.total_dividends) }}</p>
            <p class="text-sm text-gray-600">Total Dividends</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <nav class="flex flex-wrap gap-2 sm:gap-6 border-b border-gray-100 px-6 py-3 bg-[#f9fafb]">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
    'px-4 py-2 rounded-xl text-sm font-medium transition-all',
    activeTab === tab.id
      ? 'bg-[#0a2342] text-white shadow-sm'
      : 'text-gray-600 hover:text-[#0a2342] hover:bg-blue-50'
  ]">
            {{ tab.name }}
          </button>
        </nav>

        <!-- Tab Panels -->
        <div class="p-6">
          <!-- Personal Information Tab -->
          <div v-if="activeTab === 'personal'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h4>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                    <dd class="text-sm text-gray-900">
                      {{ member.first_name }} {{ member.last_name }}
                      <span v-if="member.other_names">{{ member.other_names }}</span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                    <dd class="text-sm text-gray-900">{{ formatDate(member.date_of_birth) }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                    <dd class="text-sm text-gray-900">{{ capitalize(member.gender) }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Marital Status</dt>
                    <dd class="text-sm text-gray-900">{{ capitalize(member.marital_status) }}</dd>
                  </div>
                </dl>
              </div>

              <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h4>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="text-sm text-gray-900">{{ member.user.email }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="text-sm text-gray-900">{{ member.user.phone }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Physical Address</dt>
                    <dd class="text-sm text-gray-900">{{ member.physical_address }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Postal Address</dt>
                    <dd class="text-sm text-gray-900">{{ member.postal_address }}</dd>
                  </div>
                  <div class="flex gap-4">
                    <span>
                      <dt class="text-sm font-medium text-gray-500">City</dt>
                      <dd class="text-sm text-gray-900">{{ member.city }}</dd>
                    </span>
                    <span>
                      <dt class="text-sm font-medium text-gray-500">County</dt>
                      <dd class="text-sm text-gray-900">{{ member.county }}</dd>
                    </span>
                  </div>

                </dl>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Identification</h4>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">ID Type</dt>
                    <dd class="text-sm text-gray-900">{{ capitalize(member.id_type?.replace('_', ' ')) }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">ID Number</dt>
                    <dd class="text-sm text-gray-900">{{ member.id_number }}</dd>
                  </div>
                </dl>
              </div>

              <div v-if="member.occupation || member.employer">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Employment</h4>
                <dl class="space-y-3">
                  <div v-if="member.occupation">
                    <dt class="text-sm font-medium text-gray-500">Occupation</dt>
                    <dd class="text-sm text-gray-900">{{ member.occupation }}</dd>
                  </div>
                  <div v-if="member.employer">
                    <dt class="text-sm font-medium text-gray-500">Employer</dt>
                    <dd class="text-sm text-gray-900">{{ member.employer }}</dd>
                  </div>
                  <div v-if="member.monthly_income">
                    <dt class="text-sm font-medium text-gray-500">Monthly Income</dt>
                    <dd class="text-sm text-gray-900">{{ formatCurrency(member.monthly_income) }}</dd>
                  </div>
                  <div v-if="member.employee_number">
                    <dt class="text-sm font-medium text-gray-500">Employee Number</dt>
                    <dd class="text-sm text-gray-900">{{ member.employee_number }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>

          <!-- Accounts Tab -->
          <div v-if="activeTab === 'accounts'">
            <div class="space-y-4">
              <div v-for="account in member.accounts" :key="account.id" class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="text-base font-medium text-gray-900">
                      {{ capitalize(account.account_type) }} Account
                    </h4>
                    <p class="text-sm text-gray-500">{{ account.account_number }}</p>
                    <div class="mt-2">
                      <span class="text-lg sm:text-xl font-medium text-gray-900">
                        {{ formatCurrency(account.balance) }}
                      </span>
                      <span class="text-sm text-gray-500 ml-2">Available Balance</span>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <Link :href="route('accounts.show', account.id)"
                      class="text-indigo-600 hover:text-indigo-500 text-sm">
                    View Details
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Loans Tab -->
          <div v-if="activeTab === 'loans'">
            <div v-if="member.loans.length > 0" class="space-y-4">
              <div v-for="loan in member.loans" :key="loan.id" class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="text-lg font-medium text-gray-900">
                      {{ loan.loan_product?.name || 'Loan' }}
                    </h4>
                    <p class="text-sm text-gray-500">Loan #{{ loan.id }}</p>
                    <div class="mt-2 grid grid-cols-3 gap-4">
                      <div>
                        <span class="text-sm text-gray-500">Principal</span>
                        <div class="font-semibold">{{ formatCurrency(loan.principal_amount) }}</div>
                      </div>
                      <div>
                        <span class="text-sm text-gray-500">Outstanding</span>
                        <div class="font-semibold text-red-600">{{ formatCurrency(loan.outstanding_balance) }}</div>
                      </div>
                      <div>
                        <span class="text-sm text-gray-500">Status</span>
                        <div>
                          <span :class="{
    'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
    'bg-green-100 text-green-800': loan.status === 'active',
    'bg-yellow-100 text-yellow-800': loan.status === 'pending',
    'bg-blue-100 text-blue-800': loan.status === 'approved',
    'bg-red-100 text-red-800': loan.status === 'rejected'
  }">
                            {{ loan.status }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <Link :href="route('loans.show', loan.id)" class="text-indigo-600 hover:text-indigo-500 text-sm">
                  View Details
                  </Link>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No loans found</p>
            </div>
          </div>

          <!-- Transactions Tab -->
          <div v-if="activeTab === 'transactions'">
            <div v-if="member.transactions.length > 0">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="transaction in member.transactions" :key="transaction.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ capitalize(transaction.transaction_type) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ transaction.account?.account_number }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium"
                        :class="transaction.transaction_type === 'credit' ? 'text-green-600' : 'text-red-600'">
                        {{ transaction.transaction_type === 'credit' ? '+' : '-' }}{{
    formatCurrency(transaction.amount) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span :class="{
    'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
    'bg-green-100 text-green-800': transaction.status === 'completed',
    'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
    'bg-red-100 text-red-800': transaction.status === 'failed'
  }">
                          {{ transaction.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="mt-4">
                <Link :href="route('members.transactions', member.id)"
                  class="text-indigo-600 hover:text-indigo-500 text-sm">
                View All Transactions
                </Link>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No transactions found</p>
            </div>
          </div>

          <!-- Next of Kin Tab -->
          <div v-if="activeTab === 'next-of-kin'">
            <div v-if="member.next_of_kin && member.next_of_kin.length > 0" class="space-y-4">
              <div v-for="kin in member.next_of_kin" :key="kin.id" class="border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <h4 class="text-lg font-medium text-gray-900">{{ kin.name }}</h4>
                    <p class="text-sm text-gray-500">{{ kin.relationship }}</p>
                    <div class="mt-2 space-y-1">
                      <p class="text-sm text-gray-900">{{ kin.phone }}</p>
                      <p class="text-sm text-gray-500">{{ kin.email }}</p>
                    </div>
                  </div>
                  <div v-if="kin.address">
                    <h5 class="text-sm font-medium text-gray-700">Address</h5>
                    <p class="text-sm text-gray-900">{{ kin.address }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No next of kin information available</p>
            </div>
          </div>

          <!-- Documents Tab -->
          <div v-if="activeTab === 'documents'">
            <div v-if="memberDocuments.length > 0" class="space-y-4">
              <div v-for="(doc, index) in memberDocuments" :key="index" class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <File class="h-8 w-8 text-gray-400 mr-3" />
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">{{ doc.name }}</h4>
                      <p class="text-sm text-gray-500">
                        {{ formatFileSize(doc.size) }} • {{ doc.type }} •
                        Uploaded {{ formatDate(doc.uploaded_at) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <a :href="`/storage/${doc.path}`" target="_blank"
                      class="text-indigo-600 hover:text-indigo-500 text-sm">
                      View
                    </a>
                    <a :href="`/storage/${doc.path}`" :download="doc.name"
                      class="text-green-600 hover:text-green-500 text-sm">
                      Download
                    </a>
                    <button v-if="canEdit" @click="deleteDocument(index)"
                      class="text-red-600 hover:text-red-500 text-sm">
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No documents uploaded</p>
            </div>

            <!-- Upload New Documents -->
            <div v-if="canEdit" class="mt-6 border-t pt-6">
              <input type="file" ref="documentInput" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="hidden"
                @change="handleDocumentUpload" />
              <button @click="$refs.documentInput.click()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <File class="w-4 h-4 mr-2" />
                Upload Documents
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, ChevronDown, File, Pencil, User } from 'lucide-vue-next'

// Flash handling
const page = usePage();
const flash = computed(() => page.props.flash || {});
const flashMessage = ref(null);
const flashType = ref("success");
const flashBox = ref(null);

watch(
  flash,
  (val) => {
    if (val.success) {
      flashMessage.value = val.success;
      flashType.value = "success";
    } else if (val.error) {
      flashMessage.value = val.error;
      flashType.value = "error";
    }

    if (flashMessage.value) {
      // Scroll to top of page to ensure flash is visible
      window.scrollTo({ top: 0, behavior: "smooth" });

      // Optional: also ensure the flash container itself is in view
      flashBox.value?.scrollIntoView({ behavior: "smooth", block: "start" });

      // Auto-hide after 3s
      setTimeout(() => (flashMessage.value = null), 3000);
    }
  },
  { immediate: true, deep: true }
);

const props = defineProps({
  member: Object,
  stats: Object
})

const activeTab = ref('personal')
const showDropdown = ref(false)
const dropdown = ref(null)



const tabs = [
  { id: 'personal', name: 'Personal Info' },
  { id: 'accounts', name: 'Accounts' },
  { id: 'loans', name: 'Loans' },
  { id: 'transactions', name: 'Recent Transactions' },
  { id: 'next-of-kin', name: 'Next of Kin' },
  { id: 'documents', name: 'Documents' }
]

const canEdit = computed(() => {
  const userRole = page.props.auth.user?.role
  return ['admin', 'management', 'loan_officer'].includes(userRole)
})


const canManageStatus = computed(() => {
  const userRole = page.props.auth.user?.role
  return ['admin', 'management'].includes(userRole)
})


const memberDocuments = computed(() => {
  if (!props.member.documents) return []
  try {
    return JSON.parse(props.member.documents)
  } catch {
    return []
  }
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}

const formatFileSize = (bytes) => {
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  if (bytes === 0) return '0 Byte'
  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}

const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const showConfirmModal = ref(false)
const actionType = ref(null)
const memberId = props.member.id

const openConfirm = (action) => {
  actionType.value = action
  showConfirmModal.value = true
}

const updateStatus = () => {
  router.post(route(`members.${actionType.value}`, memberId), {}, {
    preserveScroll: true,
    onSuccess: () => {
      showDropdown.value = false
      showConfirmModal.value = false
    }
  })
}

const handleDocumentUpload = (event) => {
  const files = Array.from(event.target.files)
  if (files.length > 0) {
    const formData = new FormData()
    files.forEach(file => {
      formData.append('documents[]', file)
    })

    router.post(route('members.upload-documents', props.member.id), formData, {
      preserveScroll: true,
      onSuccess: () => {
        event.target.value = ''
      }
    })
  }
}

const deleteDocument = (index) => {
  if (confirm('Are you sure you want to delete this document?')) {
    router.delete(route('members.delete-document', [props.member.id, index]), {
      preserveScroll: true
    })
  }
}

const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
