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
            ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-700 dark:text-green-300'
            : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-700 dark:text-red-300',
          'mb-4 rounded-xl p-4 shadow-sm flex items-center border'
        ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" />
          <div class="flex gap-2 items-center">
            <p class="ml-3 text-sm">{{ flashMessage }}</p>
            <button @click="flashMessage = null"
              class="ml-auto text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
              x
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between max-sm:mx-2 gap-4 px-4 sm:px-8 py-6
             bg-blue-950 text-white rounded-b-3xl shadow-md
             dark:bg-[#0e1628] dark:text-gray-100">
      <div class="flex items-center gap-3">
        <Link :href="route('members.index')" class="hover:text-orange-400 transition">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="font-bold text-lg sm:text-xl">{{ member.first_name }} {{ member.last_name }}</h2>
          <p class="text-sm opacity-75">Member ID: {{ member.membership_id }}</p>
        </div>
      </div>

      <!-- ACTION BUTTONS -->
      <div v-if="canEdit" class="flex flex-wrap gap-2">
        <Link :href="route('members.edit', member.id)" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600
                 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
          <Pencil class="w-4 h-4" /> Edit
        </Link>

        <button
        @click="openUsernameModal"
        class="inline-flex items-center gap-2 bg-blue-800 hover:bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm">
        Edit Username
        </button>

        <button
        @click="openResetPasswordModal"
        class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm">
        Reset Password
        </button>

        <!-- DROPDOWN -->
        <div class="relative" ref="dropdown">
          <button @click="showDropdown = !showDropdown" class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 text-[#0a2342] dark:text-gray-100
                   border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm font-medium
                   hover:bg-gray-50 dark:hover:bg-gray-700">
            Actions
            <ChevronDown class="w-4 h-4" />
          </button>

          <div v-if="showDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg py-2 z-10
                   border border-gray-100 dark:border-gray-700">
            <template v-if="member.membership_status === 'pending' && canManageStatus">
              <button @click="openConfirm('approve')" class="block w-full text-left px-4 py-2
                       hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-300 text-sm">
                Approve Member
              </button>
              <button @click="openConfirm('reject')" class="block w-full text-left px-4 py-2
                       hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-300 text-sm">
                Reject Member
              </button>
            </template>

            <template v-else-if="canManageStatus">
              <button v-if="member.membership_status !== 'active'" @click="openConfirm('activate')"
                class="block w-full text-left px-4 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-300 text-sm">
                Activate Member
              </button>
              <button v-if="member.membership_status === 'active' && member.user.id !== $page.props.auth.user.id"
                @click="openConfirm('deactivate')"
                class="block w-full text-left px-4 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-300 text-sm">
                Deactivate Member
              </button>
              <button v-if="member.membership_status !== 'suspended' && member.user.id !== $page.props.auth.user.id"
                @click="openConfirm('suspend')"
                class="block w-full text-left px-4 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-300 text-sm">
                Suspend Member
              </button>
              <button v-if="member.user.id === $page.props.auth.user.id"
                class="block w-full text-left px-4 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-300 text-sm">
                No action (current user)
              </button>
            </template>

            <!-- CONFIRMATION MODAL -->
            <div v-if="showConfirmModal" class="fixed inset-0 flex items-center justify-center bg-black/70 z-50">
              <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 w-96
                          border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Confirm Action</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                  <template v-if="actionType === 'delete'">
                    <strong class="text-red-600 dark:text-red-400">Warning:</strong>
                    Deleting this member will permanently remove the profile.
                    <br /><br />
                    <strong>This action cannot be undone.</strong>
                  </template>
                  <template v-else>
                    Are you sure you want to
                    <span class="font-semibold text-orange-600 dark:text-orange-400">{{ actionType }}</span>
                    this member?
                  </template>
                </p>
                <div class="flex justify-end space-x-3">
                  <button @click="showConfirmModal = false"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                  </button>
                  <button @click="updateStatus"
                    class="px-4 py-2 bg-[#0a2342] dark:bg-orange-600 text-white rounded-md hover:bg-orange-600 dark:hover:bg-orange-500">
                    Yes, {{ actionType }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Suspended/Rejected Notice -->
    <div v-if="['suspended', 'rejected'].includes(member.membership_status)"
      class="max-w-3xl mx-auto mt-6 px-4 py-3
             bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700
             text-red-700 dark:text-red-300 rounded-xl shadow-sm flex items-center gap-2">
      <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400" />
      <p class="text-sm">
        This member has been <strong>{{ member.membership_status }}</strong>.
        Certain actions are disabled until reinstated.
      </p>
    </div>

    <!-- Content -->
    <div class="py-10 px-4 sm:px-8 max-w-7xl mx-auto space-y-8">

      <!-- Profile Overview -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-6 flex flex-col md:flex-row items-center gap-6 border-b border-gray-100">
          <div>
            <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`" alt='profile'
              class="h-24 w-24 rounded-full object-cover border-2 border-orange-500" />
            <div v-else class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center border-2 border-orange-500">
              <User class="h-10 w-10 text-gray-600" />
            </div>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-[#0a2342]">{{ member.first_name }} {{ member.last_name }}</h3>
            <p class="text-sm text-gray-500">{{ member.membership_id }}</p>
            <div class="mt-2 flex items-center gap-3">
              <span :class="[
                'inline-flex px-3 py-1 rounded-full text-xs font-medium',
                member.membership_status === 'active' ? 'bg-green-100 text-green-700' :
                  member.membership_status === 'inactive' ? 'bg-red-100 text-red-700' :
                    member.membership_status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                      member.membership_status === 'suspended' ? 'bg-orange-100 text-orange-700' :
                        member.membership_status === 'rejected' ? 'bg-gray-100 text-gray-700' :
                          'bg-blue-100 text-blue-700'
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
            <p class="text-sm text-gray-600">Total Share Deposits</p>
          </div>
          <div class="text-center bg-blue-50 rounded-xl p-4">
            <p class="text-lg sm:text-xl font-semibold text-[#0a2342]">{{ formatCurrency(stats.total_shares) }}</p>
            <p class="text-sm text-gray-600">Total Shares Capital</p>
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
              ? 'bg-blue-950 text-white shadow-sm'
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
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Username</dt>
                    <dd class="text-sm text-gray-900">{{ capitalize(member.user.username) }}</dd>
                  </div>
                  <div>
                  <dt class="text-sm font-medium text-gray-500">Membership Date</dt>
                    <dd class="text-sm text-gray-900">
                        {{
                            new Date(member.membership_date).toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            })
                        }}
                    </dd>
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
                    <div>
                      <dt class="text-sm font-medium text-gray-500">City</dt>
                      <dd class="text-sm text-gray-900">{{ member.city }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">County</dt>
                      <dd class="text-sm text-gray-900">{{ member.county }}</dd>
                    </div>
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
                    <h4 class="text-base font-medium text-gray-900">{{ capitalize(account.account_type) }} Account</h4>
                    <p class="text-sm text-gray-500">{{ account.account_number }}</p>
                    <div class="mt-2">
                      <span class="text-lg sm:text-xl font-medium text-gray-900">{{ formatCurrency(account.balance) }}</span>
                      <span class="text-sm text-gray-500 ml-2">Available Balance</span>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <Link :href="route('accounts.show', account.id)" class="text-indigo-600 hover:text-indigo-500 text-sm">
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
                    <h4 class="text-lg font-medium text-gray-900">{{ loan.loan_product?.name || 'Loan' }}</h4>
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
                          }">{{ loan.status }}</span>
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
                        {{ transaction.transaction_type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span :class="{
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                          'bg-green-100 text-green-800': transaction.status === 'completed',
                          'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                          'bg-red-100 text-red-800': transaction.status === 'failed'
                        }">{{ transaction.status }}</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="mt-4">
                <Link :href="route('members.transactions', member.id)" class="text-indigo-600 hover:text-indigo-500 text-sm">
                  View All Transactions
                </Link>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No transactions found</p>
            </div>
          </div>

          <!-- ================================================================ -->
          <!-- Finance Setup Tab                                                 -->
          <!-- ================================================================ -->
          <div v-if="activeTab === 'deposit-commitments'" class="space-y-6">

            <div>
              <h3 class="text-lg font-semibold text-gray-900">Financial Configuration</h3>
              <p class="text-sm text-gray-500">
                Configure how this member participates in contributions, loans, and dividends.
                These settings are used during scheduled system runs.
              </p>
            </div>

            <!-- 3 info cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

              <!-- 1. MONTHLY CONTRIBUTIONS -->
              <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <h4 class="font-semibold text-[#0a2342]">Monthly Contributions</h4>
                  <span class="text-xs px-2 py-1 rounded-full"
                    :class="memberConfig?.contribution_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                    {{ memberConfig?.contribution_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                  <p>Amount:</p>
                  <p class="font-semibold text-gray-900 text-lg">
                    {{ formatCurrency(memberConfig?.monthly_contribution || 0) }}
                  </p>
                  <p class="text-xs text-gray-400">
                    Account: {{ memberConfig?.contribution_account?.account_number || '—' }}
                  </p>
                  <p class="text-xs text-gray-500 pt-1">Auto-credited monthly during schedule run</p>
                </div>
                <button @click="openModal('deposit')"
                  class="block w-full text-center bg-blue-50 text-[#0a2342] py-2 rounded-lg text-sm hover:bg-blue-100 transition">
                  Configure
                </button>
              </div>

              <!-- 2. LOAN REPAYMENTS -->
              <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <h4 class="font-semibold text-[#0a2342]">Loan Repayments</h4>
                  <span class="text-xs px-2 py-1 rounded-full"
                    :class="memberConfig?.loan_auto_deduct ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                    {{ memberConfig?.loan_auto_deduct ? 'Auto Deduct' : 'Manual' }}
                  </span>
                </div>
                <div class="text-sm text-gray-600 space-y-2">
                  <p>Active Loans:
                    <span class="font-semibold text-gray-900">{{ member.loans.filter(l => l.status === 'active').length }}</span>
                  </p>
                  <p>Monthly Deduction:
                    <span class="font-semibold" :class="memberConfig?.loan_deduction_amount ? 'text-blue-700' : 'text-gray-400'">
                      {{ memberConfig?.loan_deduction_amount ? formatCurrency(memberConfig.loan_deduction_amount) : 'Full instalment' }}
                    </span>
                  </p>
                  <p class="text-xs text-gray-500">
                    Repayments processed automatically during schedule run.
                  </p>
                </div>
                <button @click="openModal('loan')"
                  class="block w-full text-center bg-blue-50 text-[#0a2342] py-2 rounded-lg text-sm hover:bg-blue-100 transition">
                  Configure
                </button>
              </div>

              <!-- 3. DIVIDEND SETTINGS -->
              <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                  <h4 class="font-semibold text-[#0a2342]">Dividend Settings</h4>
                  <span class="text-xs px-2 py-1 rounded-full"
                    :class="memberConfig?.dividend_eligible !== false ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                    {{ memberConfig?.dividend_eligible !== false ? 'Eligible' : 'Not Eligible' }}
                  </span>
                </div>
                <div class="text-sm text-gray-600 space-y-2">
                  <p>Share Capital:
                    <span class="font-semibold text-gray-900">{{ formatCurrency(stats.total_shares) }}</span>
                  </p>
                  <p>Payout Account:
                    <span class="font-semibold text-gray-700">
                      {{ memberConfig?.dividend_account?.account_number || 'Default (FOSA)' }}
                    </span>
                  </p>
                  <p class="text-xs text-gray-500">
                    Dividends calculated from shares and deposits during annual distribution.
                  </p>
                </div>
                <button @click="openModal('dividend')"
                  class="block w-full text-center bg-blue-50 text-[#0a2342] py-2 rounded-lg text-sm hover:bg-blue-100 transition">
                  Configure
                </button>
              </div>

            </div>

            <!-- System Note -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
              <strong>System Note:</strong><br />
              During scheduled execution:
              <ul class="list-disc ml-5 mt-2 space-y-1">
                <li>Monthly contributions are credited automatically (only if contribution is active and amount &gt; 0)</li>
                <li>Loan repayments are processed for members with auto-deduct enabled (uses fixed amount if set, otherwise full instalment)</li>
                <li>Dividend eligibility is considered during the annual distribution run</li>
              </ul>
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
            <Link :href="route('members.next-of-kin', member.id)" class="text-indigo-600 hover:text-indigo-500 text-sm">
              Manage Next of Kin
            </Link>
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
                        {{ formatFileSize(doc.size) }} • {{ doc.type }} • Uploaded {{ formatDate(doc.uploaded_at) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <a :href="`/storage/${doc.path}`" target="_blank" class="text-indigo-600 hover:text-indigo-500 text-sm">View</a>
                    <a :href="`/storage/${doc.path}`" :download="doc.name" class="text-green-600 hover:text-green-500 text-sm">Download</a>
                    <button v-if="canEdit" @click="deleteDocument(index)" class="text-red-600 hover:text-red-500 text-sm">Delete</button>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-500">No documents uploaded</p>
            </div>
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

    <!-- ====================================================================== -->
    <!-- FINANCE SETUP MODAL                                                     -->
    <!-- ====================================================================== -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showFinanceModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200 dark:border-gray-700"
          @click.stop>

          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div>
              <h3 class="text-lg font-semibold text-[#0a2342] dark:text-white">{{ modalTitle }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ member.first_name }} {{ member.last_name }}</p>
            </div>
            <button @click="showFinanceModal = false"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <form @submit.prevent="saveConfig" class="p-6 space-y-5">

            <!-- ── 1. MONTHLY DEPOSIT ─────────────────────────────────── -->
            <template v-if="activeModalSection === 'deposit'">
              <div class="space-y-4">

                <!-- Active toggle -->
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                  <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Auto Contribution</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Include this member in the monthly deposit schedule</p>
                  </div>
                  <button type="button" @click="form.contribution_active = !form.contribution_active"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                    :class="form.contribution_active ? 'bg-[#0a2342]' : 'bg-gray-300'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                      :class="form.contribution_active ? 'translate-x-6' : 'translate-x-1'" />
                  </button>
                </div>

                <!-- Amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Monthly Contribution Amount (KES)
                  </label>
                  <input v-model.number="form.monthly_contribution" type="number" min="0" step="0.01"
                    placeholder="e.g. 5000" :disabled="!form.contribution_active"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-[#0a2342] focus:border-transparent outline-none
                           disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400
                           dark:bg-gray-800 dark:text-gray-100" />
                </div>

                <!-- Account selector -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Credit To Account
                  </label>
                  <select v-model="form.contribution_account_id" :disabled="!form.contribution_active"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-[#0a2342] focus:border-transparent outline-none
                           disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400
                           dark:bg-gray-800 dark:text-gray-100">
                    <option value="">— Select account —</option>
                    <option v-for="acc in member.accounts" :key="acc.id" :value="acc.id">
                      {{ acc.account_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                      – {{ acc.account_number }} ({{ formatCurrency(acc.balance) }})
                    </option>
                  </select>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                  The chosen amount will be posted to the selected account each time the Monthly Deposits schedule is run.
                </p>
              </div>
            </template>

            <!-- ── 2. LOAN REPAYMENT ──────────────────────────────────── -->
            <template v-if="activeModalSection === 'loan'">
              <div class="space-y-4">

                <!-- Auto deduct toggle -->
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                  <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Enable Auto Deduction</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Include this member's loans in the automated repayment schedule
                    </p>
                  </div>
                  <button type="button" @click="form.loan_auto_deduct = !form.loan_auto_deduct"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                    :class="form.loan_auto_deduct ? 'bg-[#0a2342]' : 'bg-gray-300'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                      :class="form.loan_auto_deduct ? 'translate-x-6' : 'translate-x-1'" />
                  </button>
                </div>

                <!-- Fixed deduction amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fixed Monthly Deduction Amount (KES)
                    <span class="text-gray-400 font-normal ml-1">— optional</span>
                  </label>
                  <input v-model.number="form.loan_deduction_amount" type="number" min="0" step="0.01"
                    placeholder="Leave blank to deduct the full instalment" :disabled="!form.loan_auto_deduct"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-[#0a2342] focus:border-transparent outline-none
                           disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400
                           dark:bg-gray-800 dark:text-gray-100" />
                  <p class="text-xs text-gray-400 mt-1">
                    If set, this fixed amount is deducted each month (capped at the outstanding instalment).
                    Leave blank to always deduct the full instalment.
                  </p>
                </div>

                <!-- Active loans info box -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 space-y-2">
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Active Loans</p>
                  <template v-if="member.loans.filter(l => l.status === 'active').length > 0">
                    <div v-for="loan in member.loans.filter(l => l.status === 'active')" :key="loan.id"
                      class="flex justify-between text-sm text-blue-700 dark:text-blue-300">
                      <span>{{ loan.loan_product?.name || 'Loan #' + loan.id }}</span>
                      <span class="font-semibold">{{ formatCurrency(loan.outstanding_balance) }} outstanding</span>
                    </div>
                  </template>
                  <p v-else class="text-sm text-blue-600 dark:text-blue-400">No active loans at this time.</p>
                </div>
              </div>
            </template>

            <!-- ── 3. DIVIDEND ────────────────────────────────────────── -->
            <template v-if="activeModalSection === 'dividend'">
              <div class="space-y-4">

                <!-- Eligibility toggle -->
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                  <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Dividend Eligible</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Include this member in the annual dividend payment run
                    </p>
                  </div>
                  <button type="button" @click="form.dividend_eligible = !form.dividend_eligible"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                    :class="form.dividend_eligible ? 'bg-[#0a2342]' : 'bg-gray-300'">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                      :class="form.dividend_eligible ? 'translate-x-6' : 'translate-x-1'" />
                  </button>
                </div>

                <!-- Dividend payout account -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Dividend Payout Account
                    <span class="text-gray-400 font-normal ml-1">— optional, defaults to FOSA</span>
                  </label>
                  <select v-model="form.dividend_account_id" :disabled="!form.dividend_eligible"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm
                           focus:ring-2 focus:ring-[#0a2342] focus:border-transparent outline-none
                           disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400
                           dark:bg-gray-800 dark:text-gray-100">
                    <option value="">— Default (FOSA) —</option>
                    <option v-for="acc in member.accounts" :key="acc.id" :value="acc.id">
                      {{ acc.account_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                      – {{ acc.account_number }} ({{ formatCurrency(acc.balance) }})
                    </option>
                  </select>
                </div>

                <!-- Share summary -->
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 space-y-2">
                  <p class="text-sm font-medium text-green-800 dark:text-green-300">Member Share Summary</p>
                  <div class="flex justify-between text-sm text-green-700 dark:text-green-300">
                    <span>Share Capital</span>
                    <span class="font-semibold">{{ formatCurrency(stats.total_shares) }}</span>
                  </div>
                  <div class="flex justify-between text-sm text-green-700 dark:text-green-300">
                    <span>Share Deposits</span>
                    <span class="font-semibold">{{ formatCurrency(stats.total_savings) }}</span>
                  </div>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                  Dividends are computed using share capital and monthly deposit balances.
                  Disabling eligibility excludes this member from all dividend schedule runs.
                </p>
              </div>
            </template>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
              <button type="button" @click="showFinanceModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800
                       hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition">
                Cancel
              </button>
              <button type="submit" :disabled="isSaving"
                class="px-5 py-2 text-sm font-medium text-white bg-[#0a2342] hover:bg-orange-600 rounded-xl transition
                       disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2">
                <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                {{ isSaving ? 'Saving…' : 'Save Configuration' }}
              </button>
            </div>

          </form>
        </div>
      </div>
    </transition>

   <!-- ================= RESET PASSWORD MODAL ================= -->
  <div
    v-if="showResetModal"
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
  >
    <div
      class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md border border-gray-200 dark:border-gray-700"
    >
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Reset Password
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          {{ member.first_name }} {{ member.last_name }}
        </p>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-4">

        <!-- Password Type -->
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
            Password Option
          </label>

          <select
            v-model="resetForm.password_type"
            class="w-full border rounded-xl px-3 py-2 dark:bg-gray-800 dark:border-gray-700"
          >
            <option value="default">Use ID Number</option>
            <option value="custom">Set Custom Password</option>
          </select>
        </div>

        <!-- Default Password -->
        <div
          v-if="resetForm.password_type === 'default'"
          class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4"
        >
          <p class="text-sm text-blue-800 font-medium">Default Password</p>

          <p class="font-bold text-xl text-[#0a2342] dark:text-white">
            {{ member.id_number }}
          </p>
        </div>

        <!-- Custom Password -->
        <div v-if="resetForm.password_type === 'custom'">
          <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
            New Password
          </label>

          <input
            type="password"
            v-model="resetForm.custom_password"
            class="w-full border rounded-xl px-3 py-2 dark:bg-gray-800 dark:border-gray-700"
            placeholder="Enter custom password"
          />
        </div>

        <!-- Force change -->
        <label class="flex items-start gap-3">
          <input
            type="checkbox"
            v-model="resetForm.must_change_password"
            class="mt-1 rounded border-gray-300"
          />

          <span class="text-sm text-gray-700 dark:text-gray-300">
            Force password change on next login
          </span>
        </label>

      </div>

      <!-- Footer -->
      <div
        class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3"
      >
        <button
          @click="showResetModal = false"
          :disabled="isLoading"
          class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm disabled:opacity-50"
        >
          Cancel
        </button>

        <button
          @click="submitResetPassword"
          :disabled="isLoading"
          class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm disabled:opacity-70 flex items-center gap-2"
        >
          <!-- Loader -->
          <svg
            v-if="isLoading"
            class="animate-spin h-4 w-4 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            />
          </svg>

          <span>
            {{ isLoading ? 'Resetting...' : 'Reset Password' }}
          </span>
        </button>
      </div>
    </div>
  </div>

<div v-if="showUsernameModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
 <div class="bg-white rounded-xl p-6 w-full max-w-md">
   <h3 class="font-bold text-lg mb-4">Edit Username</h3>

   <input v-model="usernameForm.username"
    class="w-full border rounded-lg px-3 py-2 mb-4">

   <div class="flex justify-end gap-2">
     <button @click="showUsernameModal=false">Cancel</button>

     <button
      @click="submitUsername"
      :disabled="isLoading"
      class="bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">

      <span
        v-if="isLoading"
        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin">
      </span>

      {{ isLoading ? 'Saving...' : 'Save' }}
      </button>
   </div>
 </div>
</div>

  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, ChevronDown, AlertCircle, CheckCircle, File, Pencil, User, X } from 'lucide-vue-next'

const props = defineProps({
  member: Object,
  stats:  Object,

})

const page         = usePage()
const flash        = computed(() => page.props.flash || {})
const flashMessage = ref(null)
const flashType    = ref('success')
const flashBox     = ref(null)
const isLoading    = ref(false)


watch(flash, (val) => {
  if (val.success)      { flashMessage.value = val.success; flashType.value = 'success' }
  else if (val.error)   { flashMessage.value = val.error;   flashType.value = 'error'   }

  if (flashMessage.value) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    setTimeout(() => (flashMessage.value = null), 3000)
  }
}, { immediate: true, deep: true })

const memberConfig = computed(() => props.member.finance_config ?? {})

const activeTab = ref('personal')
const tabs = [
  { id: 'personal',            name: 'Personal Info' },
  { id: 'accounts',            name: 'Accounts' },
  { id: 'loans',               name: 'Loans' },
  { id: 'transactions',        name: 'Recent Transactions' },
  { id: 'deposit-commitments', name: 'Finance Setup' },
  { id: 'next-of-kin',         name: 'Next of Kin' },
  { id: 'documents',           name: 'Documents' },
]

const showResetModal = ref(false)
const showUsernameModal = ref(false)

const resetForm = ref({
  password_type: 'default',
  custom_password: '',
  must_change_password: true
})

const usernameForm = ref({
  username: props.member.user?.username || ''
})

const openResetPasswordModal = () => {
  resetForm.value.must_change_password = true
  showResetModal.value = true
}

const openUsernameModal = () => {
  usernameForm.value.username = props.member.user?.username || ''
  showUsernameModal.value = true
}

const submitResetPassword = () => {
  isLoading.value = true

  router.post(
    route('members.reset-password', props.member.id),
    {
      password_type: resetForm.value.password_type,
      custom_password: resetForm.value.custom_password,
      must_change_password: resetForm.value.must_change_password
    },
    {
      preserveScroll: true,

      onSuccess: () => {
        showResetModal.value = false

        // reset form
        resetForm.value = {
          password_type: 'default',
          custom_password: '',
          must_change_password: true
        }
      },

      onError: (errors) => {
        flashMessage.value =
          errors?.custom_password ||
          errors?.password_type ||
          errors?.message ||
          'Failed to reset password'

        flashType.value = 'error'
      },

      onFinish: () => {
        isLoading.value = false
      }
    }
  )
}

const submitUsername = () => {
  isLoading.value = true

  router.post(
    route('members.update-username', props.member.id),
    {
      username: usernameForm.value.username
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        showUsernameModal.value = false
      },
      onError: (errors) => {
        flashMessage.value =
          errors?.username ||
          errors?.message ||
          'Failed to update username'

        flashType.value = 'error'
      },
      onFinish: () => {
        isLoading.value = false
      }
    }
  )
}
const canEdit        = computed(() => ['admin', 'management', 'loan_officer'].includes(page.props.auth.user?.role))
const canManageStatus = computed(() => ['admin', 'management'].includes(page.props.auth.user?.role))

const showDropdown     = ref(false)
const dropdown         = ref(null)
const showConfirmModal = ref(false)
const actionType       = ref(null)
const memberId         = props.member.id

const openConfirm = (action) => { actionType.value = action; showConfirmModal.value = true }

const updateStatus = () => {
  isLoading.value = true
  if (actionType.value === 'delete') {
    router.delete(route('members.destroy', memberId), {
      preserveScroll: true,
      onSuccess: () => { showConfirmModal.value = false; showDropdown.value = false; router.visit(route('members.index')) },
      onFinish:  () => { isLoading.value = false },
    })
    return
  }
  router.post(route(`members.${actionType.value}`, memberId), {}, {
    preserveScroll: true,
    onSuccess: () => { showConfirmModal.value = false; showDropdown.value = false },
    onError:   (errors) => { flashMessage.value = errors?.message || 'Something went wrong'; flashType.value = 'error' },
    onFinish:  () => { isLoading.value = false },
  })
}

const memberDocuments = computed(() => {
  if (!props.member.documents) return []
  try { return JSON.parse(props.member.documents) } catch { return [] }
})

const handleDocumentUpload = (event) => {
  const files = Array.from(event.target.files)
  if (!files.length) return
  const formData = new FormData()
  files.forEach(file => formData.append('documents[]', file))
  isLoading.value = true
  router.post(route('members.upload-documents', props.member.id), formData, {
    preserveScroll: true,
    onError:  (errors) => { flashMessage.value = errors?.message || 'Failed to upload documents'; flashType.value = 'error' },
    onFinish: () => { isLoading.value = false; event.target.value = '' },
  })
}

const deleteDocument = (index) => {
  if (!confirm('Are you sure you want to delete this document?')) return
  isLoading.value = true
  router.delete(route('members.delete-document', [props.member.id, index]), {
    preserveScroll: true,
    onError:  (errors) => { flashMessage.value = errors?.message || 'Failed to delete document'; flashType.value = 'error' },
    onFinish: () => { isLoading.value = false },
  })
}

const showFinanceModal   = ref(false)
const activeModalSection = ref('deposit')   
const isSaving           = ref(false)

const modalTitle = computed(() => ({
  deposit:  'Monthly Deposit Configuration',
  loan:     'Loan Repayment Configuration',
  dividend: 'Dividend Eligibility Configuration',
}[activeModalSection.value] ?? 'Finance Setup'))

const form = ref({
  // Monthly deposit
  monthly_contribution:    0,
  contribution_active:     false,
  contribution_account_id: '',

  // Loan repayment
  loan_auto_deduct:       false,
  loan_deduction_amount:  null,   

  // Dividend
  dividend_eligible:      true,
  dividend_account_id:    '',     
})


const openModal = (section) => {
  activeModalSection.value = section

  const cfg = memberConfig.value
  form.value = {
    monthly_contribution:    cfg.monthly_contribution    ?? 0,
    contribution_active:     cfg.contribution_active     ?? false,
    contribution_account_id: cfg.contribution_account_id ?? '',

    loan_auto_deduct:       cfg.loan_auto_deduct       ?? false,
    loan_deduction_amount:  cfg.loan_deduction_amount  ?? null,

    dividend_eligible:      cfg.dividend_eligible      ?? true,
    dividend_account_id:    cfg.dividend_account_id    ?? '',
  }

  showFinanceModal.value = true
}


const saveConfig = () => {
  isSaving.value = true

  router.post(
    route('members.finance-config.save', props.member.id),
    {
      // Monthly deposit
      monthly_contribution:    form.value.monthly_contribution,
      contribution_active:     form.value.contribution_active,
      contribution_account_id: form.value.contribution_account_id || null,

      // Loan repayment
      loan_auto_deduct:       form.value.loan_auto_deduct,
      loan_deduction_amount:  form.value.loan_deduction_amount || null,

      // Dividend
      dividend_eligible:      form.value.dividend_eligible,
      dividend_account_id:    form.value.dividend_account_id || null,
    },
    {
      preserveScroll: true,
      onSuccess: () => { showFinanceModal.value = false },
      onError: (errors) => {
        const first = Object.values(errors)[0]
        flashMessage.value = first || 'Failed to save configuration'
        flashType.value = 'error'
      },
      onFinish: () => { isSaving.value = false },
    }
  )
}

const formatDate = (date) => new Date(date).toLocaleDateString()

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)

const formatFileSize = (bytes) => {
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  if (bytes === 0) return '0 Byte'
  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}

const capitalize = (str) => str ? str.charAt(0).toUpperCase() + str.slice(1) : ''

const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target)) showDropdown.value = false
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>