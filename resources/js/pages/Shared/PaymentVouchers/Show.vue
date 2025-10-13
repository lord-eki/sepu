<template>
  <Head :title="`Voucher ${voucher.voucher_number}`" />
  
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Payment Voucher: {{ voucher.voucher_number }}
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            Created on {{ formatDate(voucher.created_at) }} by {{ voucher.creator?.name }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Link
            :href="route('vouchers.index')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center"
          >
            <ArrowLeft class="h-5 w-5 mr-2" />
            Back to Vouchers
          </Link>
          
          <div v-if="canEdit" class="relative">
            <button
              @click="showActionsMenu = !showActionsMenu"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center"
            >
              <Cog class="h-5 w-5 mr-2" />
              Actions
              <ChevronDown class="h-4 w-4 ml-1" />
            </button>
            
            <div
              v-if="showActionsMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border"
            >
              <div class="py-1">
                <Link
                  v-if="canEdit"
                  :href="route('vouchers.edit', voucher.id)"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <Pencil class="h-4 w-4 inline mr-2" />
                  Edit Voucher
                </Link>
                
                <button
                  v-if="voucher.status === 'pending'"
                  @click="submitVoucher"
                  class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <Paperclip class="h-4 w-4 inline mr-2" />
                  Submit for Approval
                </button>
                
                <button
                  v-if="canDelete"
                  @click="deleteVoucher"
                  class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  <Trash class="h-4 w-4 inline mr-2" />
                  Delete Voucher
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Status Alert -->
        <div v-if="voucher.status === 'rejected'" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <OctagonAlert class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Voucher Rejected</h3>
              <p class="text-sm text-red-700 mt-1">{{ voucher.rejection_reason }}</p>
            </div>
          </div>
        </div>

        <div v-if="voucher.status === 'cancelled'" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <div class="flex">
            <CircleX class="h-5 w-5 text-gray-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-800">Voucher Cancelled</h3>
              <p class="text-sm text-gray-700 mt-1">{{ voucher.rejection_reason }}</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            
            <!-- Voucher Details -->
            <div class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900">Voucher Details</h3>
                  <span :class="getStatusBadgeClass(voucher.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                    {{ formatStatus(voucher.status) }}
                  </span>
                </div>
              </div>
              <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Voucher Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ voucher.voucher_number }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ formatVoucherType(voucher.voucher_type) }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ formatCurrency(voucher.amount) }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ voucher.purpose }}</dd>
                  </div>
                  <div v-if="voucher.description" class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ voucher.description }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Payee Information -->
            <div class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payee Information</h3>
              </div>
              <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ voucher.payee_name }}</dd>
                  </div>
                  <div v-if="voucher.payee_phone">
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ voucher.payee_phone }}</dd>
                  </div>
                  <div v-if="voucher.payee_account" class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Account Details</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ voucher.payee_account }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Budget & Loan Information -->
            <div v-if="voucher.budget_item || voucher.loan" class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Budget & Loan Information</h3>
              </div>
              <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-if="voucher.budget_item">
                    <dt class="text-sm font-medium text-gray-500">Budget Item</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ voucher.budget_item.item_name }}
                      <div class="text-xs text-gray-500">
                        Budget: {{ voucher.budget_item.budget?.title }}
                      </div>
                    </dd>
                  </div>
                  <div v-if="voucher.loan">
                    <dt class="text-sm font-medium text-gray-500">Loan</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ voucher.loan.loan_number }}
                      <div class="text-xs text-gray-500">
                        Member: {{ voucher.loan.member?.first_name }} {{ voucher.loan.member?.last_name }}
                      </div>
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Supporting Documents -->
            <div v-if="voucher.supporting_documents && voucher.supporting_documents.length > 0" class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supporting Documents</h3>
              </div>
              <div class="p-6">
                <div class="space-y-3">
                  <div
                    v-for="(document, index) in voucher.supporting_documents"
                    :key="index"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                  >
                    <div class="flex items-center">
                      <File class="h-6 w-6 text-gray-400 mr-3" />
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ document.name }}</p>
                        <p class="text-xs text-gray-500">
                          {{ formatFileSize(document.size) }} â€¢ 
                          Uploaded {{ formatDate(document.uploaded_at) }}
                        </p>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="downloadDocument(document)"
                        class="text-blue-600 hover:text-blue-800 text-sm"
                      >
                        <WindArrowDown class="h-4 w-4" />
                      </button>
                      <button
                        v-if="canEdit && voucher.status === 'pending'"
                        @click="deleteDocument(document)"
                        class="text-red-600 hover:text-red-800 text-sm"
                      >
                        <Trash class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>
                
                <!-- Upload Additional Documents -->
                <div v-if="canEdit && voucher.status === 'pending'" class="mt-4 pt-4 border-t border-gray-200">
                  <div class="flex items-center space-x-4">
                    <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                      Upload More Documents
                      <input
                        ref="fileInput"
                        type="file"
                        multiple
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="uploadAdditionalDocuments"
                      />
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            
            <!-- Actions -->
            <div class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Actions</h3>
              </div>
              <div class="p-6 space-y-3">
                
                <!-- Approval Actions -->
                <div v-if="canApprove && voucher.status === 'pending'">
                  <button
                    @click="showApprovalModal = true"
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                  >
                    <CheckCircle class="h-4 w-4 inline mr-2" />
                    Approve Voucher
                  </button>
                  <button
                    @click="showRejectionModal = true"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium mt-2"
                  >
                    <EyeClosed class="h-4 w-4 inline mr-2" />
                    Reject Voucher
                  </button>
                </div>

                <!-- Payment Actions -->
                <div v-if="canPay && voucher.status === 'approved'">
                  <button
                    @click="showPaymentModal = true"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                  >
                    <BanknoteIcon class="h-4 w-4 inline mr-2" />
                    Process Payment
                  </button>
                </div>

                <!-- General Actions -->
                <button
                  @click="duplicateVoucher"
                  class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                >
                  <Files class="h-4 w-4 inline mr-2" />
                  Duplicate Voucher
                </button>

                <button
                  @click="downloadPDF"
                  class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                >
                  <WindArrowDown class="h-4 w-4 inline mr-2" />
                  Download PDF
                </button>
              </div>
            </div>

            <!-- Workflow Status -->
            <div class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Workflow Status</h3>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <!-- Created -->
                  <div class="flex items-start">
                    <div class="flex-shrink-0">
                      <CheckCircle2 class="h-5 w-5 text-green-500" />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">Created</p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(voucher.created_at) }} by {{ voucher.creator?.name }}
                      </p>
                    </div>
                  </div>

                  <!-- Approved/Rejected -->
                  <div v-if="voucher.approved_by" class="flex items-start">
                    <div class="flex-shrink-0">
                      <CheckCircle2 
                        v-if="voucher.status === 'approved' || voucher.status === 'paid'"
                        class="h-5 w-5 text-green-500" 
                      />
                      <CircleX 
                        v-else
                        class="h-5 w-5 text-red-500" 
                      />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">
                        {{ voucher.status === 'rejected' ? 'Rejected' : 'Approved' }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(voucher.approval_date) }} by {{ voucher.approver?.name }}
                      </p>
                      <p v-if="voucher.approval_notes" class="text-xs text-gray-600 mt-1">
                        {{ voucher.approval_notes }}
                      </p>
                    </div>
                  </div>

                  <!-- Paid -->
                  <div v-if="voucher.status === 'paid'" class="flex items-start">
                    <div class="flex-shrink-0">
                      <CheckCircle2 class="h-5 w-5 text-blue-500" />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">Paid</p>
                      <p class="text-sm font-medium text-gray-900">Paid</p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(voucher.payment_date) }} by {{ voucher.payer?.name }}
                      </p>
                    </div>
                  </div>

                  <!-- Pending Steps -->
                  <div v-if="voucher.status === 'pending'" class="flex items-start">
                    <div class="flex-shrink-0">
                      <Clock class="h-5 w-5 text-yellow-500" />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">Awaiting Approval</p>
                      <p class="text-xs text-gray-500">Pending review by authorized personnel</p>
                    </div>
                  </div>

                  <div v-if="voucher.status === 'approved'" class="flex items-start">
                    <div class="flex-shrink-0">
                      <Clock class="h-5 w-5 text-yellow-500" />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">Awaiting Payment</p>
                      <p class="text-xs text-gray-500">Ready for payment processing</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white shadow-sm rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Info</h3>
              </div>
              <div class="p-6">
                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Created</dt>
                    <dd class="text-sm text-gray-900">{{ formatDate(voucher.created_at) }}</dd>
                  </div>
                  <div v-if="voucher.approval_date" class="flex justify-between">
                    <dt class="text-sm text-gray-500">{{ voucher.status === 'rejected' ? 'Rejected' : 'Approved' }}</dt>
                    <dd class="text-sm text-gray-900">{{ formatDate(voucher.approval_date) }}</dd>
                  </div>
                  <div v-if="voucher.payment_date" class="flex justify-between">
                    <dt class="text-sm text-gray-500">Paid</dt>
                    <dd class="text-sm text-gray-900">{{ formatDate(voucher.payment_date) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <Modal :show="showApprovalModal" @close="showApprovalModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <CheckCircle2 class="h-6 w-6 text-green-600 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Approve Voucher</h3>
        </div>
        <p class="text-sm text-gray-600 mt-2">
          Are you sure you want to approve this payment voucher for {{ formatCurrency(voucher.amount) }}?
        </p>
        
        <form @submit.prevent="approveVoucher" class="mt-4">
          <div>
            <label for="approvalNotes" class="block text-sm font-medium text-gray-700">
              Approval Notes (Optional)
            </label>
            <textarea
              v-model="approvalForm.approval_notes"
              id="approvalNotes"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
              placeholder="Add any notes about the approval..."
            />
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="showApprovalModal = false"
              class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="approvalForm.processing"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg disabled:opacity-50"
            >
              <span v-if="approvalForm.processing">Approving...</span>
              <span v-else>Approve Voucher</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Rejection Modal -->
    <Modal :show="showRejectionModal" @close="showRejectionModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <CircleX class="h-6 w-6 text-red-600 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Reject Voucher</h3>
        </div>
        <p class="text-sm text-gray-600 mt-2">
          Please provide a reason for rejecting this payment voucher.
        </p>
        
        <form @submit.prevent="rejectVoucher" class="mt-4">
          <div>
            <label for="rejectionReason" class="block text-sm font-medium text-gray-700">
              Rejection Reason <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="rejectionForm.rejection_reason"
              id="rejectionReason"
              rows="3"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
              placeholder="Please explain why this voucher is being rejected..."
            />
            <p v-if="rejectionForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">
              {{ rejectionForm.errors.rejection_reason }}
            </p>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="showRejectionModal = false"
              class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="rejectionForm.processing"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg disabled:opacity-50"
            >
              <span v-if="rejectionForm.processing">Rejecting...</span>
              <span v-else>Reject Voucher</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Payment Modal -->
    <Modal :show="showPaymentModal" @close="showPaymentModal = false" max-width="2xl">
      <div class="p-6">
        <div class="flex items-center">
          <BanknoteIcon class="h-6 w-6 text-blue-600 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Process Payment</h3>
        </div>
        <p class="text-sm text-gray-600 mt-2">
          Process payment of {{ formatCurrency(voucher.amount) }} to {{ voucher.payee_name }}.
        </p>
        
        <form @submit.prevent="processPayment" class="mt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="paymentMethod" class="block text-sm font-medium text-gray-700">
                Payment Method <span class="text-red-500">*</span>
              </label>
              <select
                v-model="paymentForm.payment_method"
                id="paymentMethod"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
              >
                <option value="">Select payment method</option>
                <option value="cash">Cash</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
              </select>
              <p v-if="paymentForm.errors.payment_method" class="mt-1 text-sm text-red-600">
                {{ paymentForm.errors.payment_method }}
              </p>
            </div>

            <div>
              <label for="paymentReference" class="block text-sm font-medium text-gray-700">
                Payment Reference
              </label>
              <input
                v-model="paymentForm.payment_reference"
                type="text"
                id="paymentReference"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
                placeholder="Transaction ID, cheque number, etc."
              />
              <p v-if="paymentForm.errors.payment_reference" class="mt-1 text-sm text-red-600">
                {{ paymentForm.errors.payment_reference }}
              </p>
            </div>
          </div>

          <div class="mt-4">
            <label for="paymentNotes" class="block text-sm font-medium text-gray-700">
              Payment Notes (Optional)
            </label>
            <textarea
              v-model="paymentForm.payment_notes"
              id="paymentNotes"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
              placeholder="Add any notes about the payment..."
            />
            <p v-if="paymentForm.errors.payment_notes" class="mt-1 text-sm text-red-600">
              {{ paymentForm.errors.payment_notes }}
            </p>
          </div>

          <!-- Payment Summary -->
          <div class="mt-6 bg-gray-50 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Payment Summary</h4>
            <dl class="grid grid-cols-2 gap-2 text-sm">
              <dt class="text-gray-500">Payee:</dt>
              <dd class="text-gray-900">{{ voucher.payee_name }}</dd>
              <dt class="text-gray-500">Amount:</dt>
              <dd class="text-gray-900 font-semibold">{{ formatCurrency(voucher.amount) }}</dd>
              <dt class="text-gray-500">Purpose:</dt>
              <dd class="text-gray-900">{{ voucher.purpose }}</dd>
            </dl>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="showPaymentModal = false"
              class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="paymentForm.processing"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg disabled:opacity-50"
            >
              <span v-if="paymentForm.processing">Processing...</span>
              <span v-else>Process Payment</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Cancellation Modal -->
    <Modal :show="showCancellationModal" @close="showCancellationModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <CircleX class="h-6 w-6 text-gray-600 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Cancel Voucher</h3>
        </div>
        <p class="text-sm text-gray-600 mt-2">
          Please provide a reason for cancelling this payment voucher.
        </p>
        
        <form @submit.prevent="cancelVoucher" class="mt-4">
          <div>
            <label for="cancellationReason" class="block text-sm font-medium text-gray-700">
              Cancellation Reason <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="cancellationForm.cancellation_reason"
              id="cancellationReason"
              rows="3"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
              placeholder="Please explain why this voucher is being cancelled..."
            />
            <p v-if="cancellationForm.errors.cancellation_reason" class="mt-1 text-sm text-red-600">
              {{ cancellationForm.errors.cancellation_reason }}
            </p>
          </div>
          
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="showCancellationModal = false"
              class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="cancellationForm.processing"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg disabled:opacity-50"
            >
              <span v-if="cancellationForm.processing">Cancelling...</span>
              <span v-else>Cancel Voucher</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ArrowLeft, BanknoteIcon, CheckCircle, CheckCircle2, ChevronDown, CircleX, Clock, Cog, EyeClosed, File, Files, OctagonAlert, Paperclip, Pencil, Trash, WindArrowDown } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  voucher: Object,
  canApprove: Boolean,
  canPay: Boolean,
  canEdit: Boolean,
  canDelete: Boolean,
})

const showActionsMenu = ref(false)
const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const showPaymentModal = ref(false)
const showCancellationModal = ref(false)

// Forms
const approvalForm = useForm({
  approval_notes: '',
})

const rejectionForm = useForm({
  rejection_reason: '',
})

const paymentForm = useForm({
  payment_method: '',
  payment_reference: '',
  payment_notes: '',
})

const cancellationForm = useForm({
  cancellation_reason: '',
})

// Methods
const submitVoucher = () => {
  router.post(route('vouchers.submit', props.voucher.id))
}

const approveVoucher = () => {
  approvalForm.post(route('vouchers.approve', props.voucher.id), {
    onSuccess: () => {
      showApprovalModal.value = false
      approvalForm.reset()
    },
  })
}

const rejectVoucher = () => {
  rejectionForm.post(route('vouchers.reject', props.voucher.id), {
    onSuccess: () => {
      showRejectionModal.value = false
      rejectionForm.reset()
    },
  })
}

const processPayment = () => {
  paymentForm.post(route('vouchers.pay', props.voucher.id), {
    onSuccess: () => {
      showPaymentModal.value = false
      paymentForm.reset()
    },
  })
}

const cancelVoucher = () => {
  cancellationForm.post(route('vouchers.cancel', props.voucher.id), {
    onSuccess: () => {
      showCancellationModal.value = false
      cancellationForm.reset()
    },
  })
}

const deleteVoucher = () => {
  if (confirm('Are you sure you want to delete this voucher? This action cannot be undone.')) {
    router.delete(route('vouchers.destroy', props.voucher.id))
  }
}

const duplicateVoucher = () => {
  router.post(route('vouchers.duplicate', props.voucher.id))
}

const downloadPDF = () => {
  window.open(route('vouchers.download-pdf', props.voucher.id), '_blank')
}

const uploadAdditionalDocuments = (event) => {
  const files = Array.from(event.target.files)
  if (files.length === 0) return

  const formData = new FormData()
  files.forEach((file, index) => {
    formData.append(`documents[${index}]`, file)
  })

  router.post(route('vouchers.upload-documents', props.voucher.id), formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
    onSuccess: () => {
      event.target.value = '' // Clear file input
    },
  })
}

const downloadDocument = (document) => {
  window.open(`/storage/${document.path}`, '_blank')
}

const deleteDocument = (document) => {
  if (confirm('Are you sure you want to delete this document?')) {
    router.delete(route('vouchers.delete-document', [props.voucher.id, document.path]))
  }
}

// Utility functions
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(amount)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatStatus = (status) => {
  const statusMap = {
    pending: 'Pending',
    approved: 'Approved',
    paid: 'Paid',
    rejected: 'Rejected',
    cancelled: 'Cancelled',
  }
  return statusMap[status] || status
}

const formatVoucherType = (type) => {
  const typeMap = {
    loan_disbursement: 'Loan Disbursement',
    operational_expense: 'Operational Expense',
    dividend_payment: 'Dividend Payment',
    refund: 'Refund',
    other: 'Other',
  }
  return typeMap[type] || type
}

const getStatusBadgeClass = (status) => {
  const classMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

// Close actions menu when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      showActionsMenu.value = false
    }
  })
})
</script>