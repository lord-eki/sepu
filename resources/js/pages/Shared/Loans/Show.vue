<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') },
      { title: 'Loan Details' }
    ]"
  >
    <!-- Flash Message -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-3"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-3"
    >
      <div
        v-if="visible"
        :class="messageClass"
        class="fixed top-6 left-1/2 -translate-x-1/2 bg-white/90 dark:bg-gray-800 backdrop-blur-sm border border-gray-200 dark:border-gray-700 shadow-lg px-6 py-3 rounded-xl flex items-start gap-4 z-50 max-w-xl w-full"
      >
        <div class="flex-shrink-0 mt-0.5">
          <template v-if="type === 'success'">
            <div class="h-6 w-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center">✓</div>
          </template>
          <template v-else-if="type === 'error'">
            <div class="h-6 w-6 rounded-full bg-red-100 text-red-700 flex items-center justify-center">✕</div>
          </template>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium" v-if="type === 'success'">{{ message }}</p>
          <p class="text-sm whitespace-pre-wrap" v-else-if="type === 'error'">{{ message }}</p>
        </div>
        <button type="button" class="ml-2 text-gray-400 hover:text-gray-600" @click="close">✕</button>
      </div>
    </transition>

    <!-- Header -->
    <div class="mt-6 mx-4 flex sm:flex-row items-center sm:justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl p-3 shadow">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
          </svg>
        </div>
        <div>
          <h2 class="font-semibold text-base sm:text-lg text-slate-900 dark:text-white">
            Loan Details - <span class="max-sm:hidden">{{ loan.loan_number }}</span>
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">Loan ID: <span class="font-medium">{{ loan.loan_number }}</span></p>
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <Link
          v-if="canEdit"
          :href="route('loans.edit', loan.id)"
          class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5h6M4 21v-7a4 4 0 014-4h7" />
          </svg>
          Edit
        </Link>

        <Link
          :href="isMemberRole ? route('my-loans') : route('loans.index')"
          class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span>Back <span class="max-sm:hidden">to Loans</span></span>
        </Link>
      </div>
    </div>

    <div class="py-5 max-sm:px-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Status Banner -->
        <div :class="getStatusBannerClass(loan.status)" class="rounded-xl p-4 flex items-start gap-3 border">
          <div class="flex-shrink-0">
            <component :is="getStatusIcon(loan.status)" class="h-6 w-6" />
          </div>
          <div>
            <h3 class="text-sm font-semibold">Loan Status: {{ formatStatus(loan.status) }}</h3>
            <div class="mt-2 text-sm">
              <p class="text-sm">{{ getStatusDescription(loan.status) }}</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div v-if="hasQuickActions" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
              <button
                v-if="canApprove"
                @click="showApprovalModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 shadow"
              >
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Approve Loan
              </button>

              <button
                v-if="canReject"
                @click="showRejectionModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 shadow"
              >
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject Loan
              </button>

              <button
                v-if="canDisburse"
                @click="showDisbursementModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 shadow"
              >
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                </svg>
                Disburse Loan
              </button>

              <Link
                v-if="canViewSchedule"
                :href="route('loans.schedule', loan.id)"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                </svg>
                View Schedule
              </Link>

              <Link
                v-if="canViewRepayments"
                :href="route('loans.repayments', loan.id)"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2" />
                </svg>
                Repayments
              </Link>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Loan Information</h3>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Loan Number</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ loan.loan_number }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Loan Product</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ loan.loan_product.name }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Applied Amount</label>
                    <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ formatCurrency(loan.applied_amount) }}</p>
                  </div>
                  <div v-if="loan.approved_amount">
                    <label class="block text-sm font-medium text-gray-500">Approved Amount</label>
                    <p class="mt-1 text-sm font-semibold text-green-600">KES {{ formatCurrency(loan.approved_amount) }}</p>
                  </div>
                  <div v-if="loan.disbursed_amount">
                    <label class="block text-sm font-medium text-gray-500">Disbursed Amount</label>
                    <p class="mt-1 text-sm font-semibold text-blue-600">KES {{ formatCurrency(loan.disbursed_amount) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Interest Rate</label>
                    <p class="mt-1 text-sm text-gray-900">{{ loan.interest_rate }}% per annum</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Term</label>
                    <p class="mt-1 text-sm text-gray-900">{{ loan.term_months }} months</p>
                  </div>
                  <div v-if="loan.monthly_repayment">
                    <label class="block text-sm font-medium text-gray-500">Monthly Repayment</label>
                    <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ formatCurrency(loan.monthly_repayment) }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Application Date</label>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(loan.application_date) }}</p>
                  </div>
                  <div v-if="loan.approval_date">
                    <label class="block text-sm font-medium text-gray-500">Approval Date</label>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(loan.approval_date) }}</p>
                  </div>
                  <div v-if="loan.disbursement_date">
                    <label class="block text-sm font-medium text-gray-500">Disbursement Date</label>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(loan.disbursement_date) }}</p>
                  </div>
                </div>

                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-500">Purpose</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ loan.purpose }}</p>
                </div>

                <div v-if="loan.approval_notes" class="mt-4">
                  <label class="block text-sm font-medium text-gray-500">Approval Notes</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ loan.approval_notes }}</p>
                </div>

                <div v-if="loan.rejection_reason" class="mt-4">
                  <label class="block text-sm font-medium text-gray-500">Rejection Reason</label>
                  <p class="mt-1 text-sm text-red-600">{{ loan.rejection_reason }}</p>
                </div>
              </div>
            </div>

            <!-- Member Information (Admin Only) -->
            <div v-if="!isMemberRole" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Member Information</h3>
              </div>
              <div class="p-6">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <svg class="h-6 w-6 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ loan.member.first_name }} {{ loan.member.last_name }}
                    </h4>
                    <p class="text-sm text-gray-500">{{ loan.member.membership_id }}</p>
                    <p class="text-sm text-gray-500">{{ loan.member.user.phone }}</p>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Membership Status</label>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                      {{ loan.member.membership_status.toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Monthly Income</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100">KES {{ formatCurrency(loan.member.monthly_income) }}</p>
                  </div>
                </div>

                <div class="mt-4">
                  <Link :href="route('members.show', loan.member.id)" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    View Member Profile
                  </Link>
                </div>
              </div>
            </div>

            <!-- Guarantors -->
            <div v-if="loan.guarantors && loan.guarantors.length > 0" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Guarantors</h3>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div v-for="guarantor in loan.guarantors" :key="guarantor.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                          <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="h-4 w-4 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ guarantor.guarantor_member.first_name }} {{ guarantor.guarantor_member.last_name }}
                          </h4>
                          <p class="text-sm text-gray-500">{{ guarantor.guarantor_member.membership_id }}</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="font-medium text-gray-900 dark:text-gray-100">KES {{ formatCurrency(guarantor.guaranteed_amount) }}</p>
                        <span :class="getGuarantorStatusClass(guarantor.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1">
                          {{ guarantor.status.toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div v-if="guarantor.comments" class="mt-2">
                      <p class="text-sm text-gray-600 dark:text-gray-300">{{ guarantor.comments }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Balance Information -->
            <div v-if="loan.status === 'disbursed' || loan.status === 'active'" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Balance Information</h3>
              </div>
              <div class="p-6 space-y-4 text-sm">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Outstanding Balance</label>
                  <p class="mt-1 text-xl font-bold text-red-600">KES {{ formatCurrency(loan.outstanding_balance) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Principal Balance</label>
                  <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">KES {{ formatCurrency(loan.principal_balance) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Interest Balance</label>
                  <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">KES {{ formatCurrency(loan.interest_balance) }}</p>
                </div>
                <div v-if="loan.penalty_balance > 0">
                  <label class="block text-sm font-medium text-gray-500">Penalty Balance</label>
                  <p class="mt-1 text-sm font-semibold text-red-600">KES {{ formatCurrency(loan.penalty_balance) }}</p>
                </div>
                <div v-if="loan.days_in_arrears > 0">
                  <label class="block text-sm font-medium text-gray-500">Days in Arrears</label>
                  <p class="mt-1 text-sm font-bold text-red-600">{{ loan.days_in_arrears }} days</p>
                </div>
              </div>
            </div>

            <!-- Fees Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Fees & Charges</h3>
              </div>
              <div class="p-6 space-y-3 text-sm">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Processing Fee</span>
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">KES {{ formatCurrency(loan.processing_fee) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Insurance Fee</span>
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">KES {{ formatCurrency(loan.insurance_fee) }}</span>
                </div>
                <div class="border-t pt-3">
                  <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Total Fees</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                      KES {{ formatCurrency(Number(loan.processing_fee) + Number(loan.insurance_fee)) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Loan Timeline</h3>
              </div>
              <div class="p-6">
                <div class="flow-root">
                  <ul class="-mb-8">
                    <li>
                      <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-gray-500">Application submitted on {{ formatDate(loan.application_date) }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>

                    <li v-if="loan.approval_date">
                      <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-gray-500">
                                Approved on {{ formatDate(loan.approval_date) }}
                                <span v-if="loan.approved_by">by {{ loan.approved_by.name }}</span>
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>

                    <li v-if="loan.disbursement_date">
                      <div class="relative">
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-gray-500">
                                Disbursed on {{ formatDate(loan.disbursement_date) }}
                                <span v-if="loan.disbursed_by">by {{ loan.disbursed_by.name }}</span>
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>

                  </ul>
                </div>
              </div>
            </div>

          </div> <!-- end main: lg:col-span-2 -->
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <TransitionRoot as="template" :show="showApprovalModal">
      <Dialog as="div" class="relative z-40" @close="showApprovalModal = false">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 z-40 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all border">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-gray-100">Approve Loan</DialogTitle>

                <div class="mt-4 text-sm">
                  <label class="block text-sm font-medium text-gray-500">Approved Amount</label>
                  <input v-model="approvalForm.approved_amount" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500" />

                  <label class="block text-sm font-medium text-gray-500 mt-4">Approval Notes</label>
                  <textarea v-model="approvalForm.approval_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button type="button" @click="showApprovalModal = false" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm">Cancel</button>
                  <button type="button" @click="approveLoan" class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 text-sm">Approve</button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Rejection Modal -->
    <TransitionRoot as="template" :show="showRejectionModal">
      <Dialog as="div" class="relative z-40" @close="showRejectionModal = false">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 z-40 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all border">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-gray-100">Reject Loan</DialogTitle>

                <div class="mt-4 text-sm">
                  <label class="block text-sm font-medium text-gray-500">Rejection Reason</label>
                  <textarea v-model="rejectionForm.rejection_reason" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button type="button" @click="showRejectionModal = false" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm">Cancel</button>
                  <button type="button" @click="rejectLoan" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm">Reject</button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Disbursement Modal -->
    <TransitionRoot as="template" :show="showDisbursementModal">
      <Dialog as="div" class="relative z-40" @close="showDisbursementModal = false">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 z-40 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all border">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-gray-100">Disburse Loan</DialogTitle>

                <div class="mt-4 text-sm space-y-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Disbursed Amount</label>
                    <input v-model="disbursementForm.disbursed_amount" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500">Disbursement Method</label>
                    <input v-model="disbursementForm.disbursement_method" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-500">Reference</label>
                    <input v-model="disbursementForm.disbursement_reference" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button type="button" @click="showDisbursementModal = false" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-sm">Cancel</button>
                  <button type="button" @click="disburseLoan" class="px-4 py-2 rounded-md bg-purple-600 text-white hover:bg-purple-700 text-sm">Disburse</button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

  </AppLayout>
</template>


<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  loan: Object,
  auth: Object,
  message: String,
  type: { type: String, default: 'success' }, // success or error
  duration: { type: Number, default: 5000 }
})

const isMemberRole = computed(() => props.auth.user.role === 'member')

// Reactive data
const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const showDisbursementModal = ref(false)

const approvalForm = reactive({
  approved_amount: props.loan.applied_amount,
  approval_notes: ''
})

const rejectionForm = reactive({
  rejection_reason: ''
})

const disbursementForm = reactive({
  disbursed_amount: props.loan.approved_amount,
  disbursement_method: '',
  disbursement_reference: ''
})

// Computed properties
const canEdit = computed(() => {
  return props.loan.status === 'pending' && ['admin', 'loan_officer', 'management'].includes(props.auth.user.role)
})

const canApprove = computed(() => {
  return props.loan.status === 'pending' && ['admin', 'management'].includes(props.auth.user.role)
})

const canReject = computed(() => {
  return ['pending', 'approved'].includes(props.loan.status) && ['admin', 'management'].includes(props.auth.user.role)
})

const canDisburse = computed(() => {
  return props.loan.status === 'approved' && ['admin', 'accountant'].includes(props.auth.user.role)
})

const canViewSchedule = computed(() => {
  return ['disbursed', 'active', 'completed'].includes(props.loan.status)
})

const canViewRepayments = computed(() => {
  return ['disbursed', 'active', 'completed'].includes(props.loan.status)
})

const hasQuickActions = computed(() => {
  return canApprove.value || canReject.value || canDisburse.value || canViewSchedule.value || canViewRepayments.value
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: '2-digit'
  })
}

const formatStatus = (status) => {
  return status.replace('_', ' ').toUpperCase()
}

const getStatusBannerClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-50 text-yellow-800 border border-yellow-200',
    'approved': 'bg-green-50 text-green-800 border border-green-200',
    'disbursed': 'bg-blue-50 text-blue-800 border border-blue-200',
    'active': 'bg-blue-50 text-blue-800 border border-blue-200',
    'completed': 'bg-gray-50 text-gray-800 border border-gray-200',
    'rejected': 'bg-red-50 text-red-800 border border-red-200',
    'cancelled': 'bg-red-50 text-red-800 border border-red-200'
  }
  return classes[status] || 'bg-gray-50 text-gray-800 border border-gray-200'
}

const getStatusIcon = (status) => {
  // Return appropriate icon component based on status
  const icons = {
    'pending': 'svg', // Clock icon
    'approved': 'svg', // Check icon
    'disbursed': 'svg', // Money icon
    'rejected': 'svg', // X icon
  }
  return 'svg' // Default to svg for now
}

const getStatusDescription = (status) => {
  const descriptions = {
    'pending': 'This loan application is awaiting review and approval.',
    'approved': 'This loan has been approved and is ready for disbursement.',
    'disbursed': 'This loan has been disbursed to the member.',
    'active': 'This loan is active with ongoing repayments.',
    'completed': 'This loan has been fully repaid.',
    'rejected': 'This loan application has been rejected.',
    'cancelled': 'This loan application has been cancelled.'
  }
  return descriptions[status] || 'Unknown status'
}

const getGuarantorStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'accepted': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const approveLoan = async () => {
  try {
    const response = await axios.post(`/loans/${props.loan.id}/approve`, approvalForm)
    alert(response.data.message)
    showApprovalModal.value = false
    // Optionally refresh data or emit an event
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to approve loan.')
  }
}

const rejectLoan = async () => {
  try {
    const response = await axios.post(`/loans/${props.loan.id}/reject`, rejectionForm)
    alert(response.data.message)
    showRejectionModal.value = false
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to reject loan.')
  }
}

const disburseLoan = async () => {
  try {
    const response = await axios.post(`/loans/${props.loan.id}/disburse`, disbursementForm)
    alert(response.data.message)
    showDisbursementModal.value = false
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to disburse loan.')
  }
}
</script>