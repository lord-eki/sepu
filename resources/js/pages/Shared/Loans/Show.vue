<template>
  <AppLayout
  :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') },
    { title: 'Loan Details' }
  ]" >

      <div class="flex justify-between mt-4 mx-4 items-center">
        <h2 class="font-semibold text-base sm:text-lg text-blue-900 leading-tight">
          Loan Details - {{ loan.loan_number }}
        </h2>
        <div class="flex space-x-3">
          <Link v-if="canEdit" :href="route('loans.edit', loan.id)"
            class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium">
          Edit
          </Link>
          <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
          Back <span class="max-sm:hidden">to Loans</span>
          </Link>
        </div>
      </div>

    <div class="py-5 max-sm:px-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Status Banner -->
        <div :class="getStatusBannerClass(loan.status)" class="rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <component :is="getStatusIcon(loan.status)" class="h-5 w-5" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium">
                Loan Status: {{ formatStatus(loan.status) }}
              </h3>
              <div class="mt-2 text-sm">
                <p>{{ getStatusDescription(loan.status) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div v-if="hasQuickActions" class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
              <button v-if="canApprove" @click="showApprovalModal = true"
                class="inline-flex items-center hover:cursor-pointer px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Approve Loan
              </button>

              <button v-if="canReject" @click="showRejectionModal = true"
                class="inline-flex items-center hover:cursor-pointer px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject Loan
              </button>

              <button v-if="canDisburse" @click="showDisbursementModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                Disburse Loan
              </button>

              <Link v-if="canViewSchedule" :href="route('loans.schedule', loan.id)"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              View Schedule
              </Link>

              <Link v-if="canViewRepayments" :href="route('loans.repayments', loan.id)"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
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
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Loan Information</h3>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Loan Number</label>
                    <p class="mt-1 text-sm text-gray-900">{{ loan.loan_number }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Loan Product</label>
                    <p class="mt-1 text-sm text-gray-900">{{ loan.loan_product.name }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Applied Amount</label>
                    <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ formatCurrency(loan.applied_amount) }}
                    </p>
                  </div>
                  <div v-if="loan.approved_amount">
                    <label class="block text-sm font-medium text-gray-500">Approved Amount</label>
                    <p class="mt-1 text-sm font-semibold text-green-600">KES {{ formatCurrency(loan.approved_amount) }}
                    </p>
                  </div>
                  <div v-if="loan.disbursed_amount">
                    <label class="block text-sm font-medium text-gray-500">Disbursed Amount</label>
                    <p class="mt-1 text-sm font-semibold text-blue-600">KES {{ formatCurrency(loan.disbursed_amount) }}
                    </p>
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
                    <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ formatCurrency(loan.monthly_repayment) }}
                    </p>
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
                  <p class="mt-1 text-sm text-gray-900">{{ loan.purpose }}</p>
                </div>

                <div v-if="loan.approval_notes" class="mt-4">
                  <label class="block text-sm font-medium text-gray-500">Approval Notes</label>
                  <p class="mt-1 text-sm text-gray-900">{{ loan.approval_notes }}</p>
                </div>

                <div v-if="loan.rejection_reason" class="mt-4">
                  <label class="block text-sm font-medium text-gray-500">Rejection Reason</label>
                  <p class="mt-1 text-sm text-red-600">{{ loan.rejection_reason }}</p>
                </div>
              </div>
            </div>

            <!-- Member Information (Admin Only) -->
            <div v-if="!isMemberRole" class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Member Information</h3>
              </div>
              <div class="p-6">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                      <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h4 class="text-lg font-medium text-gray-900">
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
                    <p class="text-sm text-gray-900">KES {{ formatCurrency(loan.member.monthly_income) }}</p>
                  </div>
                </div>

                <div class="mt-4">
                  <Link :href="route('members.show', loan.member.id)"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                  View Member Profile
                  </Link>
                </div>
              </div>
            </div>

            <!-- Guarantors -->
            <div v-if="loan.guarantors && loan.guarantors.length > 0" class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Guarantors</h3>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div v-for="guarantor in loan.guarantors" :key="guarantor.id"
                    class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                          <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-900">
                            {{ guarantor.guarantor_member.first_name }} {{ guarantor.guarantor_member.last_name }}
                          </h4>
                          <p class="text-sm text-gray-500">{{ guarantor.guarantor_member.membership_id }}</p>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="font-medium text-gray-900">KES {{ formatCurrency(guarantor.guaranteed_amount) }}</p>
                        <span :class="getGuarantorStatusClass(guarantor.status)"
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                          {{ guarantor.status.toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div v-if="guarantor.comments" class="mt-2">
                      <p class="text-sm text-gray-600">{{ guarantor.comments }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Balance Information -->
            <div v-if="loan.status === 'disbursed' || loan.status === 'active'"
              class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Balance Information</h3>
              </div>
              <div class="p-6 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500">Outstanding Balance</label>
                  <p class="mt-1 text-xl font-bold text-red-600">KES {{ formatCurrency(loan.outstanding_balance) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Principal Balance</label>
                  <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ formatCurrency(loan.principal_balance) }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500">Interest Balance</label>
                  <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ formatCurrency(loan.interest_balance) }}
                  </p>
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
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Fees & Charges</h3>
              </div>
              <div class="p-6 space-y-3">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Processing Fee</span>
                  <span class="text-sm font-medium text-gray-900">KES {{ formatCurrency(loan.processing_fee) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Insurance Fee</span>
                  <span class="text-sm font-medium text-gray-900">KES {{ formatCurrency(loan.insurance_fee) }}</span>
                </div>
                <div class="border-t pt-3">
                  <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-900">Total Fees</span>
                      <span class="text-sm font-bold text-gray-900">
                        KES {{ formatCurrency(Number(loan.processing_fee) + Number(loan.insurance_fee)) }}
                      </span>
                    </div>

                </div>
              </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Loan Timeline</h3>
              </div>
              <div class="p-6">
                <div class="flow-root">
                  <ul class="-mb-8">
                    <li>
                      <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                          <div>
                            <span
                              class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-gray-500">
                                Application submitted on {{ formatDate(loan.application_date) }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>

                    <li v-if="loan.approval_date">
                      <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                          <div>
                            <span
                              class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7" />
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
                            <span
                              class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
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
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <TransitionRoot as="template" :show="showApprovalModal">
      <Dialog as="div" class="relative z-10" @close="showApprovalModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form @submit.prevent="approveLoan">
                  <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                      <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                      <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                        Approve Loan Application
                      </DialogTitle>
                    </div>
                  </div>

                  <div class="mt-4">
                    <label for="approved_amount" class="block text-sm font-medium text-gray-700">Approved Amount</label>
                    <input v-model="approvalForm.approved_amount" type="number" step="0.01" id="approved_amount"
                      required :max="loan.applied_amount"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Applied Amount: KES {{ formatCurrency(loan.applied_amount) }}
                    </p>
                  </div>

                  <div class="mt-4">
                    <label for="approval_notes" class="block text-sm font-medium text-gray-700">Approval Notes</label>
                    <textarea v-model="approvalForm.approval_notes" id="approval_notes" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                  </div>

                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="submit"
                      class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:col-start-2">
                      Approve Loan
                    </button>
                    <button type="button"
                      class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                      @click="showApprovalModal = false">
                      Cancel
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Rejection Modal -->
    <TransitionRoot as="template" :show="showRejectionModal">
      <Dialog as="div" class="relative z-10" @close="showRejectionModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form @submit.prevent="rejectLoan">
                  <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                      <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                      <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                        Reject Loan Application
                      </DialogTitle>
                    </div>
                  </div>

                  <div class="mt-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection
                      Reason</label>
                    <textarea v-model="rejectionForm.rejection_reason" id="rejection_reason" rows="4" required
                      placeholder="Please provide a detailed reason for rejection..."
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                  </div>

                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="submit"
                      class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:col-start-2">
                      Reject Loan
                    </button>
                    <button type="button"
                      class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                      @click="showRejectionModal = false">
                      Cancel
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Disbursement Modal -->
    <TransitionRoot as="template" :show="showDisbursementModal">
      <Dialog as="div" class="relative z-10" @close="showDisbursementModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form @submit.prevent="disburseLoan">
                  <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                      <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                      <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                        Disburse Loan
                      </DialogTitle>
                    </div>
                  </div>

                  <div class="mt-4 space-y-4">
                    <div>
                      <label for="disbursed_amount" class="block text-sm font-medium text-gray-700">Disbursed
                        Amount</label>
                      <input v-model="disbursementForm.disbursed_amount" type="number" step="0.01" id="disbursed_amount"
                        required :max="loan.approved_amount"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                      <p class="text-xs text-gray-500 mt-1">Approved Amount: KES {{ formatCurrency(loan.approved_amount)
                        }}
                      </p>
                    </div>

                    <div>
                      <label for="disbursement_method" class="block text-sm font-medium text-gray-700">Disbursement
                        Method</label>
                      <select v-model="disbursementForm.disbursement_method" id="disbursement_method" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select method...</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                      </select>
                    </div>

                    <div>
                      <label for="disbursement_reference"
                        class="block text-sm font-medium text-gray-700">Reference</label>
                      <input v-model="disbursementForm.disbursement_reference" type="text" id="disbursement_reference"
                        placeholder="Transaction reference or receipt number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="bg-yellow-50 p-3 rounded-md">
                      <h4 class="font-medium text-yellow-800 mb-2">Fee Deductions</h4>
                      <div class="text-sm space-y-1">
                        <div class="flex justify-between">
                          <span>Processing Fee:</span>
                          <span>KES {{ formatCurrency(loan.processing_fee) }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span>Insurance Fee:</span>
                          <span>KES {{ formatCurrency(loan.insurance_fee) }}</span>
                        </div>
                        <div class="border-t pt-1 flex justify-between font-medium">
                          <span>Net Amount:</span>
                          <span>KES {{ formatCurrency((disbursementForm.disbursed_amount || loan.approved_amount) -
    loan.processing_fee - loan.insurance_fee) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="submit"
                      class="inline-flex w-full justify-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 sm:col-start-2">
                      Disburse Loan
                    </button>
                    <button type="button"
                      class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                      @click="showDisbursementModal = false">
                      Cancel
                    </button>
                  </div>
                </form>
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

const props = defineProps({
  loan: Object,
  auth: Object
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

const approveLoan = () => {
  router.post(route('loans.approve', props.loan.id), approvalForm, {
    onSuccess: () => {
      showApprovalModal.value = false
    }
  })
}

const rejectLoan = () => {
  router.post(route('loans.reject', props.loan.id), rejectionForm, {
    onSuccess: () => {
      showRejectionModal.value = false
    }
  })
}

const disburseLoan = () => {
  router.post(route('loans.disburse', props.loan.id), disbursementForm, {
    onSuccess: () => {
      showDisbursementModal.value = false
    }
  })
}
</script>