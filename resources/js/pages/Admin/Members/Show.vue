<template>
  <AppLayout :title="`${member.first_name} ${member.last_name}`">

   <!--  Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage" class="flex gap-3"
          :class="[
            flashType === 'success'
              ? 'bg-green-50 border border-green-200 text-green-700'
              : 'bg-red-50 border border-red-200 text-red-700',
            'mb-4 rounded-md p-4 shadow flex items-center'
          ]"
        >
          <component
            :is="flashType === 'success' ? CheckCircle : AlertCircle"
            class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'"
          />
          <p class="ml-3 text-sm">{{ flashMessage }}</p>
          <button
            type="button"
            class="ml-auto text-gray-500 hover:text-gray-700"
            @click="flashMessage = null"
          >
            ✕
          </button>
        </div>
      </transition>
    </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link :href="route('members.index')" class="mr-4">
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ member.first_name }} {{ member.last_name }}
            </h2>
            <p class="text-sm text-gray-600">Member ID: {{ member.membership_id }}</p>
          </div>
        </div>
        <div class="flex space-x-2" v-if="canEdit">
          <Link
            :href="route('members.edit', member.id)"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
          >
            <Pencil class="w-4 h-4 mr-2" />
            Edit
          </Link>
          <div class="relative" ref="dropdown">
            <button
              @click="showDropdown = !showDropdown"
              class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
              Actions
              <ChevronDown class="w-4 h-4 ml-2" />
            </button>
            <div
              v-if="showDropdown"
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border"
            >
              <button
                v-if="member.membership_status !== 'active' && canManageStatus"
                @click="updateStatus('activate')"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                Activate Member
              </button>
              <button
                v-if="member.membership_status === 'active' && canManageStatus"
                @click="updateStatus('deactivate')"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                Deactivate Member
              </button>
              <button
                v-if="member.membership_status !== 'suspended' && canManageStatus"
                @click="updateStatus('suspend')"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                Suspend Member
              </button>
            </div>
          </div>
        </div>
      </div>
   

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Member Status and Overview -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-20 w-20">
                  <img
                    v-if="member.profile_photo"
                    :src="`/storage/${member.profile_photo}`"
                    :alt="member.first_name"
                    class="h-20 w-20 rounded-full object-cover"
                  />
                  <div
                    v-else
                    class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center"
                  >
                    <User class="h-12 w-12 text-gray-600" />
                  </div>
                </div>
                <div class="ml-6">
                  <h3 class="text-lg font-medium text-gray-900">
                    {{ member.first_name }} {{ member.last_name }}
                    <span v-if="member.other_names" class="text-gray-600">{{ member.other_names }}</span>
                  </h3>
                  <p class="text-sm text-gray-500">{{ member.membership_id }}</p>
                  <div class="flex items-center mt-2">
                    <span
                      :class="{
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                        'bg-green-100 text-green-800': member.membership_status === 'active',
                        'bg-red-100 text-red-800': member.membership_status === 'inactive',
                        'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended'
                      }"
                    >
                      {{ member.membership_status }}
                    </span>
                    <span class="ml-4 text-sm text-gray-500">
                      Joined {{ formatDate(member.membership_date) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="text-center">
              <div class="text-2xl font-semibold text-green-600">
                {{ formatCurrency(stats.total_savings) }}
              </div>
              <div class="text-sm text-gray-500">Total Savings</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-semibold text-blue-600">
                {{ formatCurrency(stats.total_shares) }}
              </div>
              <div class="text-sm text-gray-500">Total Shares</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-semibold text-red-600">
                {{ formatCurrency(stats.total_loans) }}
              </div>
              <div class="text-sm text-gray-500">Active Loans</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-semibold text-purple-600">
                {{ formatCurrency(stats.total_dividends) }}
              </div>
              <div class="text-sm text-gray-500">Total Dividends</div>
            </div>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white shadow rounded-lg">
          <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="{
                  'border-indigo-500 text-indigo-600': activeTab === tab.id,
                  'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== tab.id,
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm': true
                }"
              >
                {{ tab.name }}
              </button>
            </nav>
          </div>

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
                    <div v-if="member.address">
                      <dt class="text-sm font-medium text-gray-500">Address</dt>
                      <dd class="text-sm text-gray-900">
                        {{ member.address }}
                        <span v-if="member.city">, {{ member.city }}</span>
                        <span v-if="member.state">, {{ member.state }}</span>
                        <span v-if="member.postal_code"> {{ member.postal_code }}</span>
                      </dd>
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
                <div
                  v-for="account in member.accounts"
                  :key="account.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="text-lg font-medium text-gray-900">
                        {{ capitalize(account.account_type) }} Account
                      </h4>
                      <p class="text-sm text-gray-500">{{ account.account_number }}</p>
                      <div class="mt-2">
                        <span class="text-2xl font-semibold text-gray-900">
                          {{ formatCurrency(account.balance) }}
                        </span>
                        <span class="text-sm text-gray-500 ml-2">Available Balance</span>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <Link
                        :href="route('accounts.show', account.id)"
                        class="text-indigo-600 hover:text-indigo-500 text-sm"
                      >
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
                <div
                  v-for="loan in member.loans"
                  :key="loan.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
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
                            <span
                              :class="{
                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                                'bg-green-100 text-green-800': loan.status === 'active',
                                'bg-yellow-100 text-yellow-800': loan.status === 'pending',
                                'bg-blue-100 text-blue-800': loan.status === 'approved',
                                'bg-red-100 text-red-800': loan.status === 'rejected'
                              }"
                            >
                              {{ loan.status }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <Link
                      :href="route('loans.show', loan.id)"
                      class="text-indigo-600 hover:text-indigo-500 text-sm"
                    >
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
                          {{ transaction.transaction_type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                          <span
                            :class="{
                              'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                              'bg-green-100 text-green-800': transaction.status === 'completed',
                              'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                              'bg-red-100 text-red-800': transaction.status === 'failed'
                            }"
                          >
                            {{ transaction.status }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="mt-4">
                  <Link
                    :href="route('members.transactions', member.id)"
                    class="text-indigo-600 hover:text-indigo-500 text-sm"
                  >
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
                <div
                  v-for="kin in member.next_of_kin"
                  :key="kin.id"
                  class="border border-gray-200 rounded-lg p-4"
                >
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
                <div
                  v-for="(doc, index) in memberDocuments"
                  :key="index"
                  class="border border-gray-200 rounded-lg p-4"
                >
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
                      <a
                        :href="`/storage/${doc.path}`"
                        target="_blank"
                        class="text-indigo-600 hover:text-indigo-500 text-sm"
                      >
                        View
                      </a>
                      <a
                        :href="`/storage/${doc.path}`"
                        :download="doc.name"
                        class="text-green-600 hover:text-green-500 text-sm"
                      >
                        Download
                      </a>
                      <button
                        v-if="canEdit"
                        @click="deleteDocument(index)"
                        class="text-red-600 hover:text-red-500 text-sm"
                      >
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
                <input
                  type="file"
                  ref="documentInput"
                  multiple
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                  class="hidden"
                  @change="handleDocumentUpload"
                />
                <button
                  @click="$refs.documentInput.click()"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                  <File class="w-4 h-4 mr-2" />
                  Upload Documents
                </button>
              </div>
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

const updateStatus = (action) => {
  if (confirm(`Are you sure you want to ${action} this member?`)) {
    router.post(route(`members.${action}`, props.member.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        showDropdown.value = false
      }
    })
  }
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
