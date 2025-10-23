<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') },
    { title: 'Loan Details' }
  ]">
    <!-- HEAD -->
    <Head title="Loan Details" />

    <!-- Flash Toast -->
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
      :class="[
        'fixed top-6 left-1/2 -translate-x-1/2 z-50 max-w-lg w-full shadow-xl rounded-xl px-5 py-3 flex gap-4 items-start text-white',
        type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-slate-600'
      ]"
      role="status"
      aria-live="polite"
    >
      <div class="flex-shrink-0 mt-0.5">
        <div
          v-if="type === 'success'"
          class="h-7 w-7 rounded-full bg-white/20 text-white flex items-center justify-center font-medium"
        >
          ✓
        </div>
        <div
          v-else-if="type === 'error'"
          class="h-7 w-7 rounded-full bg-white/20 text-white flex items-center justify-center font-medium"
        >
          ✕
        </div>
      </div>

      <div class="flex-1">
        <p class="text-sm font-medium">{{ message }}</p>
      </div>

      <button
        @click="close"
        class="text-white/80 hover:text-white"
        aria-label="Close notification"
      >
        ✕
      </button>
    </div>

    </transition>



    <!-- Header -->
    <div class="mt-8 mx-4 sm:mx-12 flex flex-col sm:flex-row sm:justify-between gap-6 items-start sm:items-center">
      <div class="flex items-center gap-4">
        <div class="rounded-2xl p-3 shadow-md" :style="{ background: 'linear-gradient(135deg,#0b2b40,#164a6b)' }">
          <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
          </svg>
        </div>
        <div>
          <h2 class="font-semibold text-xl text-[#0B2B40]">
            Loan Details — <span class="text-orange-600">{{ loan.loan_number }}</span>
          </h2>
          <p class="text-xs text-slate-500 mt-1">Loan ID: <span class="font-semibold text-slate-700">{{ loan.loan_number }}</span></p>
        </div>
      </div>

      <div class="flex items-center flex-wrap gap-3">
        <Link v-if="canEdit" :href="route('loans.edit', loan.id)"
          class="inline-flex items-center gap-2 bg-[#0B2B40] hover:bg-blue-950 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M4 21v-7a4 4 0 014-4h7" />
          </svg>
          Edit
        </Link>

        <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
          class="inline-flex items-center gap-2 border border-[#0B2B40]/30 text-[#0B2B40] bg-white hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </Link>
      </div>
    </div>

    <!-- Body -->
    <div class="py-8 px-4 sm:px-8 lg:px-12">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- Status Banner -->
        <div :class="getStatusBannerClass(loan.status)" class="rounded-xl border shadow-sm p-4 flex items-start gap-3">
          <div class="flex-shrink-0">
            <component :is="getStatusIcon(loan.status)" class="h-6 w-6" />
          </div>
          <div>
            <h3 class="text-sm font-semibold text-[#0B2B40]">Loan Status: {{ formatStatus(loan.status) }}</h3>
            <p class="text-sm text-slate-600 mt-1">{{ getStatusDescription(loan.status) }}</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div v-if="hasQuickActions" class="bg-white border border-slate-200 rounded-xl shadow-sm">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-[#0B2B40] mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
              <button v-if="canApprove" @click="openApprovalModal"
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 shadow">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg> Approve Loan
              </button>

              <button v-if="canReject" @click="openRejectModal"
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 shadow">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg> Reject Loan
              </button>

              <button v-if="canDisburse" @click="openDisbursementModal"
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#0B2B40] hover:bg-blue-950 shadow">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                </svg> Disburse Loan
              </button>

              <Link v-if="canViewSchedule" :href="route('loans.schedule', loan.id)"
                class="inline-flex items-center px-4 py-2 bg-[#0B2B40] rounded-lg border border-slate-200 text-sm font-medium text-white hover:bg-[#2a3a44]">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                </svg> View Schedule
              </Link>

              <Link v-if="canViewRepayments" :href="route('loans.repayments', loan.id)"
                class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium bg-orange-500 text-white hover:bg-orange-600">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2" />
                </svg> Repayments
              </Link>
            </div>
          </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left: Main -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Loan Information -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-xl">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-[#0B2B40]">Loan Information</h3>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-800">
                  <div>
                    <label class="block text-sm font-medium text-slate-500">Loan Number</label>
                    <p class="mt-1 text-sm">{{ loan.loan_number }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-500">Loan Product</label>
                    <p class="mt-1 text-sm">{{ loan.loan_product?.name || '-' }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-500">Applied Amount</label>
                    <p class="mt-1 text-sm font-semibold text-slate-900">KES {{ formatCurrency(loan.applied_amount) }}</p>
                  </div>

                  <div v-if="loan.approved_amount">
                    <label class="block text-sm font-medium text-slate-500">Approved Amount</label>
                    <p class="mt-1 text-sm font-semibold text-blue-800">KES {{ formatCurrency(loan.approved_amount) }}</p>
                  </div>

                  <div v-if="loan.disbursed_amount">
                    <label class="block text-sm font-medium text-slate-500">Disbursed Amount</label>
                    <p class="mt-1 text-sm font-semibold text-blue-800">KES {{ formatCurrency(loan.disbursed_amount) }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-500">Interest Rate</label>
                    <p class="mt-1 text-sm">{{ loan.interest_rate }}% per annum</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-500">Term</label>
                    <p class="mt-1 text-sm">{{ loan.term_months }} months</p>
                  </div>

                  <div v-if="loan.monthly_repayment">
                    <label class="block text-sm font-medium text-slate-500">Monthly Repayment</label>
                    <p class="mt-1 text-sm font-semibold">KES {{ formatCurrency(loan.monthly_repayment) }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-500">Application Date</label>
                    <p class="mt-1 text-sm">{{ formatDate(loan.application_date) }}</p>
                  </div>

                  <div v-if="loan.approval_date">
                    <label class="block text-sm font-medium text-slate-500">Approval Date</label>
                    <p class="mt-1 text-sm">{{ formatDate(loan.approval_date) }}</p>
                  </div>

                  <div v-if="loan.disbursement_date">
                    <label class="block text-sm font-medium text-slate-500">Disbursement Date</label>
                    <p class="mt-1 text-sm">{{ formatDate(loan.disbursement_date) }}</p>
                  </div>
                </div>

                <div class="mt-6">
                  <label class="block text-sm font-medium text-slate-500">Purpose</label>
                  <p class="mt-1 text-sm text-slate-800">{{ loan.purpose || '-' }}</p>
                </div>

                <div v-if="loan.approval_notes" class="mt-4">
                  <label class="block text-sm font-medium text-slate-500">Approval Notes</label>
                  <p class="mt-1 text-sm text-slate-800">{{ loan.approval_notes }}</p>
                </div>

                <div v-if="loan.rejection_reason" class="mt-4">
                  <label class="block text-sm font-medium text-slate-500">Rejection Reason</label>
                  <p class="mt-1 text-sm text-red-600">{{ loan.rejection_reason }}</p>
                </div>
              </div>
            </div>

            <!-- Member Info (Admin only) -->
            <div v-if="!isMemberRole" class="bg-white border border-slate-200 shadow-sm rounded-xl">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-[#0B2B40]">Member Information</h3>
              </div>
              <div class="p-6">
                <div class="flex items-center gap-4">
                  <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>

                  <div>
                    <h4 class="text-lg font-medium text-slate-900">{{ loan.member.first_name }} {{ loan.member.last_name }}</h4>
                    <p class="text-sm text-slate-500">{{ loan.member.membership_id }}</p>
                    <p class="text-sm text-slate-500">{{ loan.member.user?.phone || '-' }}</p>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-slate-500">Membership Status</label>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                      {{ (loan.member.membership_status || '').toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-500">Monthly Income</label>
                    <p class="text-sm text-slate-900">KES {{ formatCurrency(loan.member.monthly_income) }}</p>
                  </div>
                </div>

                <div class="mt-4">
                  <Link :href="route('members.show', loan.member.id)" class="inline-flex items-center px-3 py-2 border border-slate-200 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50">
                    View Member Profile
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Guarantors + Sidebar -->
          <div class="space-y-6">
            <div v-if="loan.guarantors && loan.guarantors.length > 0" class="bg-white border border-slate-200 shadow-sm rounded-xl">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-[#0B2B40]">Guarantors</h3>
              </div>
              <div class="p-6 space-y-4">
                <div v-for="guarantor in loan.guarantors" :key="guarantor.id" class="border border-slate-100 rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="h-4 w-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="font-medium text-slate-900">{{ guarantor.guarantor_member.first_name }} {{ guarantor.guarantor_member.last_name }}</h4>
                        <p class="text-sm text-slate-500">{{ guarantor.guarantor_member.membership_id }}</p>
                      </div>
                    </div>

                    <div class="text-right">
                      <p class="font-medium text-slate-900">KES {{ formatCurrency(guarantor.guaranteed_amount) }}</p>
                      <span :class="getGuarantorStatusClass(guarantor.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1">
                        {{ (guarantor.status || '').toUpperCase() }}
                      </span>
                    </div>
                  </div>

                  <div v-if="guarantor.comments" class="mt-2">
                    <p class="text-sm text-slate-600">{{ guarantor.comments }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Balance Information -->
            <div v-if="loan.status === 'disbursed' || loan.status === 'active'" class="bg-white shadow-sm sm:rounded-lg border">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-medium text-slate-900">Balance Information</h3>
              </div>
              <div class="p-6 space-y-4 text-sm">
                <div>
                  <label class="block text-sm font-medium text-slate-500">Outstanding Balance</label>
                  <p class="mt-1 text-lg font-bold text-red-800">KES {{ formatCurrency(loan.outstanding_balance) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-500">Principal Balance</label>
                  <p class="mt-1 text-sm font-semibold text-slate-900">KES {{ formatCurrency(loan.principal_balance) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-500">Interest Balance</label>
                  <p class="mt-1 text-sm font-semibold text-slate-900">KES {{ formatCurrency(loan.interest_balance) }}</p>
                </div>
                <div v-if="loan.penalty_balance > 0">
                  <label class="block text-sm font-medium text-slate-500">Penalty Balance</label>
                  <p class="mt-1 text-sm font-semibold text-red-600">KES {{ formatCurrency(loan.penalty_balance) }}</p>
                </div>
                <div v-if="loan.days_in_arrears > 0">
                  <label class="block text-sm font-medium text-slate-500">Days in Arrears</label>
                  <p class="mt-1 text-sm font-bold text-red-600">{{ loan.days_in_arrears }} days</p>
                </div>
              </div>
            </div>

            <!-- Fees Information -->
            <div class="bg-white shadow-sm rounded-lg border">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-medium text-slate-900">Fees & Charges</h3>
              </div>
              <div class="p-6 space-y-3 text-sm">
                <div class="flex justify-between">
                  <span class="text-sm text-slate-500">Processing Fee</span>
                  <span class="text-sm font-medium text-slate-900">KES {{ formatCurrency(loan.processing_fee) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-slate-500">Insurance Fee</span>
                  <span class="text-sm font-medium text-slate-900">KES {{ formatCurrency(loan.insurance_fee) }}</span>
                </div>
                <div class="border-t pt-3">
                  <div class="flex justify-between">
                    <span class="text-sm font-medium text-slate-900">Total Fees</span>
                    <span class="text-sm font-bold text-slate-900">
                      KES {{ formatCurrency(Number(loan.processing_fee) + Number(loan.insurance_fee)) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white shadow-sm rounded-lg border">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-medium text-slate-900">Loan Timeline</h3>
              </div>
              <div class="p-6">
                <div class="flow-root">
                  <ul class="mb-8">
                    <li>
                      <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full bg-blue-900 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-slate-500">Application submitted on {{ formatDate(loan.application_date) }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>

                    <li v-if="loan.approval_date">
                      <div class="relative pb-8">
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full bg-orange-600 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-slate-500">
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
                            <span class="h-8 w-8 rounded-full bg-blue-800 flex items-center justify-center ring-8 ring-white">
                              <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                              </svg>
                            </span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div>
                              <p class="text-sm text-slate-500">
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

    <!-- APPROVAL MODAL -->
    <TransitionRoot as="template" :show="showApprovalModal">
      <Dialog as="div" class="relative z-40" @close="closeApprovalModal">
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
              <DialogPanel
                class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-2xl transition-all border border-slate-200"
              >
                <DialogTitle class="text-2xl font-semibold text-[#0B2B40] mb-4">
                  Approve Loan
                </DialogTitle>

                <div class="space-y-5 text-base">
                  <!-- Approved Amount -->
                  <div>
                    <label class="block text-base font-medium text-slate-600 mb-1">
                      Approved Amount
                    </label>
                    <input
                      v-model.number="approvalForm.approved_amount"
                      type="number"
                      min="0"
                      class="block w-full rounded-lg border border-slate-300 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 p-2 text-base"
                      :class="{ 'border-red-300': validationErrors.approved_amount }"
                      aria-describedby="approval-amount-error"
                    />
                    <p
                      v-if="validationErrors.approved_amount"
                      id="approval-amount-error"
                      class="text-sm text-red-600 mt-1"
                    >
                      {{ firstError(validationErrors.approved_amount) }}
                    </p>
                  </div>

                  <!-- Approval Notes -->
                  <div>
                    <label class="block text-base font-medium text-slate-600 mb-1">
                      Approval Notes
                    </label>
                    <textarea
                      v-model="approvalForm.approval_notes"
                      rows="4"
                      class="block w-full rounded-lg border border-slate-300 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 p-2 text-base"
                      :class="{ 'border-red-300': validationErrors.approval_notes }"
                      aria-describedby="approval-notes-error"
                    ></textarea>
                    <p
                      v-if="validationErrors.approval_notes"
                      id="approval-notes-error"
                      class="text-sm text-red-600 mt-1"
                    >
                      {{ firstError(validationErrors.approval_notes) }}
                    </p>
                  </div>
                </div>

                <!-- Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                  <button
                    type="button"
                    @click="closeApprovalModal"
                    class="px-5 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-base font-medium"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="approveLoan"
                    :disabled="loading.approve"
                    class="px-5 py-2.5 rounded-lg bg-orange-600 text-white hover:bg-orange-700 text-base font-medium disabled:opacity-50"
                  >
                    <span v-if="!loading.approve">Approve</span>
                    <span v-else>Approving…</span>
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>


   <!-- REJECTION MODAL -->
  <TransitionRoot as="template" :show="showRejectionModal">
    <Dialog as="div" class="relative z-40" @close="closeRejectModal">
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
            <DialogPanel
              class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-2xl transition-all border border-slate-200"
            >
              <DialogTitle class="text-2xl font-semibold text-[#0B2B40] mb-4">
                Reject Loan
              </DialogTitle>

              <div class="space-y-5 text-base">
                <!-- Rejection Reason -->
                <div>
                  <label class="block text-base font-medium text-slate-600 mb-1">
                    Rejection Reason
                  </label>
                  <textarea
                    v-model="rejectionForm.rejection_reason"
                    rows="4"
                    class="block w-full rounded-lg border border-slate-300 shadow-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 p-2 text-base"
                    :class="{ 'border-red-300': validationErrors.rejection_reason }"
                    aria-describedby="reject-error"
                  ></textarea>
                  <p
                    v-if="validationErrors.rejection_reason"
                    id="reject-error"
                    class="text-sm text-red-600 mt-1"
                  >
                    {{ firstError(validationErrors.rejection_reason) }}
                  </p>
                </div>
              </div>

              <!-- Buttons -->
              <div class="mt-8 flex justify-end space-x-4">
                <button
                  type="button"
                  @click="closeRejectModal"
                  class="px-5 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-base font-medium"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  @click="rejectLoan"
                  :disabled="loading.reject"
                  class="px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-base font-medium disabled:opacity-50"
                >
                  <span v-if="!loading.reject">Reject</span>
                  <span v-else>Rejecting…</span>
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>


   <!-- DISBURSEMENT MODAL -->
  <TransitionRoot as="template" :show="showDisbursementModal">
    <Dialog as="div" class="relative z-40" @close="closeDisbursementModal">
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
            <DialogPanel
              class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-2xl transition-all border border-slate-200"
            >
              <DialogTitle class="text-2xl font-semibold text-[#0B2B40] mb-4">
                Disburse Loan
              </DialogTitle>

              <div class="space-y-5 text-base">
                <!-- Disbursed Amount -->
                <div>
                  <label class="block text-base font-medium text-slate-600 mb-1">
                    Disbursed Amount
                  </label>
                  <input
                    v-model.number="disbursementForm.disbursed_amount"
                    type="number"
                    min="0"
                    class="block w-full rounded-lg border border-slate-300 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 p-2 text-base"
                    :class="{ 'border-red-300': validationErrors.disbursed_amount }"
                    aria-describedby="disbursed-error"
                  />
                  <p
                    v-if="validationErrors.disbursed_amount"
                    id="disbursed-error"
                    class="text-sm text-red-600 mt-1"
                  >
                    {{ firstError(validationErrors.disbursed_amount) }}
                  </p>
                </div>

                <!-- Disbursement Method -->
                <div>
                  <label class="block text-base font-medium text-slate-600 mb-1">
                    Disbursement Method
                  </label>
                  <select
                    v-model="disbursementForm.disbursement_method"
                    class="block w-full rounded-lg border border-slate-300 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 p-2 text-base"
                    :class="{ 'border-red-300': validationErrors.disbursement_method }"
                  >
                    <option disabled value="">Select Method</option>
                    <option value="cash">Cash</option>
                    <option value="mpesa">Mpesa</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                  </select>
                  <p
                    v-if="validationErrors.disbursement_method"
                    class="text-sm text-red-600 mt-1"
                  >
                    {{ firstError(validationErrors.disbursement_method) }}
                  </p>
                </div>

                <!-- Reference -->
                <div>
                  <label class="block text-base font-medium text-slate-600 mb-1">
                    Reference
                  </label>
                  <input
                    v-model="disbursementForm.disbursement_reference"
                    type="text"
                    class="block w-full rounded-lg border border-slate-300 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 p-2 text-base"
                    :class="{ 'border-red-300': validationErrors.disbursement_reference }"
                  />
                  <p
                    v-if="validationErrors.disbursement_reference"
                    class="text-sm text-red-600 mt-1"
                  >
                    {{ firstError(validationErrors.disbursement_reference) }}
                  </p>
                </div>
              </div>

              <!-- Buttons -->
              <div class="mt-8 flex justify-end space-x-4">
                <button
                  type="button"
                  @click="closeDisbursementModal"
                  class="px-5 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-base font-medium"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  @click="disburseLoan"
                  :disabled="loading.disburse"
                  class="px-5 py-2.5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white font-medium text-base disabled:opacity-50"
                >
                  <span v-if="!loading.disburse">Disburse</span>
                  <span v-else>Disbursing…</span>
                </button>
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
  type: { type: String, default: 'success' },
  duration: { type: Number, default: 5000 }
})

const isMemberRole = computed(() => props.auth.user.role === 'member')

// modal controls
const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const showDisbursementModal = ref(false)

function openApprovalModal() { resetValidation(); approvalForm.approved_amount = props.loan.applied_amount || 0; showApprovalModal.value = true }
function openRejectModal() { resetValidation(); showRejectionModal.value = true }
function openDisbursementModal() { resetValidation(); disbursementForm.disbursed_amount = props.loan.approved_amount || 0; showDisbursementModal.value = true }

function closeApprovalModal() { showApprovalModal.value = false }
function closeRejectModal() { showRejectionModal.value = false }
function closeDisbursementModal() { showDisbursementModal.value = false }

// forms & validation
const approvalForm = reactive({
  approved_amount: props.loan.applied_amount || 0,
  approval_notes: ''
})
const rejectionForm = reactive({
  rejection_reason: ''
})
const disbursementForm = reactive({
  disbursed_amount: props.loan.approved_amount || 0,
  disbursement_method: '',
  disbursement_reference: ''
})

const validationErrors = reactive({})

// loading states
const loading = reactive({ approve: false, reject: false, disburse: false })

function resetValidation() {
  for (const k in validationErrors) delete validationErrors[k]
}

// permissions computed
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

// helpers
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  try { return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' }) }
  catch { return date }
}

const formatStatus = (status = '') => status.replace('_', ' ').toUpperCase()

const getStatusBannerClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-50 text-yellow-800 border border-yellow-200',
    'approved': 'bg-green-50 text-green-800 border border-green-200',
    'disbursed': 'bg-blue-50 text-blue-800 border border-blue-200',
    'active': 'bg-blue-50 text-blue-800 border border-blue-200',
    'completed': 'bg-green-50 text-slate-800 border border-slate-200',
    'rejected': 'bg-red-50 text-red-800 border border-red-200',
    'cancelled': 'bg-red-50 text-red-800 border border-red-200'
  }
  return classes[status] || 'bg-slate-50 text-slate-800 border border-slate-200'
}

const getStatusIcon = (status) => 'svg'

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
    'accepted': 'bg-emerald-100 text-emerald-800',
    'rejected': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-slate-100 text-slate-800'
}

// Toast state
const visible = ref(Boolean(props.message))
const message = ref(props.message || '')
const type = ref(props.type || 'success')
let toastTimer = null

const toastAppearance = computed(() => {
  return type.value === 'success'
    ? 'bg-white/95 border border-slate-200'
    : 'bg-white/95 border border-red-200'
})

const close = () => {
  visible.value = false
  if (toastTimer) { clearTimeout(toastTimer); toastTimer = null }
}

if (visible.value && props.duration > 0) {
  toastTimer = setTimeout(() => { visible.value = false; toastTimer = null }, props.duration)
}

function showToast(txt = '', t = 'success', duration = props.duration) {
  message.value = txt
  type.value = t
  visible.value = true
  if (toastTimer) clearTimeout(toastTimer)
  if (duration > 0) toastTimer = setTimeout(() => { visible.value = false; toastTimer = null }, duration)
}

// returns first message from array or string
function firstError(err) {
  if (!err) return ''
  if (Array.isArray(err)) return err[0]
  if (typeof err === 'object') return Object.values(err)[0]
  return String(err)
}

// Actions (with validation)
const approveLoan = async () => {
  resetValidation()
  // client-side checks
  if (!approvalForm.approved_amount || approvalForm.approved_amount <= 0) {
    validationErrors.approved_amount = ['Approved amount must be greater than zero.']
    return
  }
  loading.approve = true
  try {
    const response = await axios.post(`/loans/${props.loan.id}/approve`, approvalForm)
    showToast(response.data.message || 'Loan approved successfully', 'success')
    showApprovalModal.value = false
    router.reload()
  } catch (err) {
    const res = err.response
    if (res?.data?.errors) {
      Object.assign(validationErrors, res.data.errors)
    }
    showToast(res?.data?.message || 'Failed to approve loan', 'error')
  } finally {
    loading.approve = false
  }
}

const rejectLoan = async () => {
  resetValidation()
  if (!rejectionForm.rejection_reason || rejectionForm.rejection_reason.trim().length < 3) {
    validationErrors.rejection_reason = ['Please provide a reason for rejection (min 3 characters).']
    return
  }
  loading.reject = true
  try {
    const response = await axios.post(`/loans/${props.loan.id}/reject`, rejectionForm)
    showToast(response.data.message || 'Loan rejected', 'success')
    showRejectionModal.value = false
    router.reload()
  } catch (err) {
    const res = err.response
    if (res?.data?.errors) Object.assign(validationErrors, res.data.errors)
    showToast(res?.data?.message || 'Failed to reject loan', 'error')
  } finally {
    loading.reject = false
  }
}

const disburseLoan = async () => {
  resetValidation()
  if (!disbursementForm.disbursed_amount || disbursementForm.disbursed_amount <= 0) {
    validationErrors.disbursed_amount = ['Disbursed amount must be greater than zero.']
    return
  }
  if (!disbursementForm.disbursement_method) {
    validationErrors.disbursement_method = ['Select a disbursement method.']
    return
  }
  loading.disburse = true
  try {
    const response = await axios.post(`/loans/${props.loan.id}/disburse`, disbursementForm)
    showToast(response.data.message || 'Loan disbursed successfully', 'success')
    showDisbursementModal.value = false
    router.reload()
  } catch (err) {
    const res = err.response
    if (res?.data?.errors) Object.assign(validationErrors, res.data.errors)
    showToast(res?.data?.message || 'Failed to disburse loan', 'error')
  } finally {
    loading.disburse = false
  }
}
</script>
