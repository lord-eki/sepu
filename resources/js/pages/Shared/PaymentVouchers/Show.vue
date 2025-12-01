<template>

  <Head :title="`Voucher ${voucher.voucher_number}`" />

  <AppLayout :breadcrumbs="[{ title: 'Vouchers', href: '/vouchers' }, { title: `${voucher.voucher_number}` }]">

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
    <!-- Header -->
    <div
      class="bg-[#0A2342] text-white rounded-xl px-5 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 shadow">
      <div>
        <h1 class="text-2xl font-semibold">Payment Voucher • {{ voucher.voucher_number }}</h1>
        <p class="text-sm opacity-80 mt-1">
          Created on {{ formatDate(voucher.created_at) }} by <span class="font-medium">{{ voucher.creator?.name || '—'
            }}</span>
        </p>
      </div>

      <div class="flex items-center gap-3">
        <Link :href="route('vouchers.index')"
          class="bg-white text-[#0A2342] px-3 py-2 rounded-lg inline-flex items-center gap-2 shadow-sm hover:opacity-95">
        <ArrowLeft class="h-4 w-4" /> Back
        </Link>

        <div class="relative" ref="actionsRoot">
          <button @click="toggleActionsMenu"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-[#FB8500] text-white hover:brightness-95 shadow">
            <Cog class="h-4 w-4" />
            Actions
            <ChevronDown class="h-4 w-4" />
          </button>

          <transition name="fade">
            <div v-if="showActionsMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border z-40">
              <div class="py-2">
                <Link v-if="canEdit" :href="route('vouchers.edit', voucher.id)"
                  class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                <Pencil class="h-4 w-4" /> Edit Voucher
                </Link>

                <button v-if="voucher.status === 'pending'" @click="submitVoucher"
                  class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <Paperclip class="h-4 w-4" /> Submit for Approval
                </button>

                <button @click="duplicateVoucher"
                  class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <Files class="h-4 w-4" /> Duplicate Voucher
                </button>

                <button @click="downloadPDF"
                  class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                  <WindArrowDown class="h-4 w-4" /> Download PDF
                </button>

                <button v-if="canDelete" @click="confirmDeleteVoucher"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                  <Trash class="h-4 w-4" /> Delete Voucher
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <!-- Page Body -->
    <div class="max-w-6xl mx-auto space-y-6 pb-12">

      <!-- Alerts -->
      <div v-if="voucher.status === 'rejected'" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <OctagonAlert class="h-5 w-5 text-red-600" />
          <div>
            <p class="font-medium text-red-800">Voucher Rejected</p>
            <p class="text-sm text-red-700 mt-1">{{ voucher.rejection_reason || 'No reason provided' }}</p>
          </div>
        </div>
      </div>

      <div v-if="voucher.status === 'cancelled'" class="bg-gray-50 border-l-4 border-gray-300 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <CircleX class="h-5 w-5 text-gray-600" />
          <div>
            <p class="font-medium text-slate-800">Voucher Cancelled</p>
            <p class="text-sm text-slate-700 mt-1">{{ voucher.rejection_reason || 'No reason provided' }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Column -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Details Card -->
          <section class="bg-white rounded-xl shadow p-5 border">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold text-[#0A2342]">Voucher Details</h2>
                <p class="text-sm text-slate-600 mt-1">Core information and context</p>
              </div>

              <div>
                <span :class="statusBadgeClass"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                  {{ formatStatus(voucher.status) }}
                </span>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <dt class="text-xs text-slate-500">Voucher Number</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ voucher.voucher_number }}</dd>
              </div>

              <div>
                <dt class="text-xs text-slate-500">Type</dt>
                <dd class="mt-1 inline-flex items-center gap-2">
                  <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-800">
                    {{ formatVoucherType(voucher.voucher_type) }}
                  </span>
                </dd>
              </div>

              <div>
                <dt class="text-xs text-slate-500">Amount</dt>
                <dd class="mt-1 text-lg font-semibold text-[#0A2342]">{{ formatCurrency(voucher.amount) }}</dd>
              </div>

              <div>
                <dt class="text-xs text-slate-500">Purpose</dt>
                <dd class="mt-1 text-sm text-slate-800">{{ voucher.purpose || '—' }}</dd>
              </div>

              <div v-if="voucher.description" class="md:col-span-2">
                <dt class="text-xs text-slate-500">Description</dt>
                <dd class="mt-1 text-sm text-slate-700">{{ voucher.description }}</dd>
              </div>
            </div>
          </section>

          <!-- Payee Card -->
          <section class="bg-white rounded-xl shadow p-5 border">
            <h3 class="text-md font-semibold text-[#0A2342]">Payee Information</h3>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <dt class="text-xs text-slate-500">Name</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ voucher.payee_name || '—' }}</dd>
              </div>

              <div v-if="voucher.payee_phone">
                <dt class="text-xs text-slate-500">Phone</dt>
                <dd class="mt-1 text-slate-800">{{ voucher.payee_phone }}</dd>
              </div>

              <div v-if="voucher.payee_account" class="md:col-span-2">
                <dt class="text-xs text-slate-500">Account Details</dt>
                <dd class="mt-1 text-slate-800">{{ voucher.payee_account }}</dd>
              </div>
            </div>
          </section>

          <!-- Budget & Loan Card -->
          <section v-if="voucher.budget_item || voucher.loan" class="bg-white rounded-xl shadow p-5 border">
            <h3 class="text-md font-semibold text-[#0A2342]">Budget & Loan</h3>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-if="voucher.budget_item">
                <dt class="text-xs text-slate-500">Budget Item</dt>
                <dd class="mt-1 text-slate-800">
                  {{ voucher.budget_item.item_name }}
                  <div class="text-xs text-slate-500">Budget: {{ voucher.budget_item.budget?.title || '—' }}</div>
                </dd>
              </div>

              <div v-if="voucher.loan">
                <dt class="text-xs text-slate-500">Loan</dt>
                <dd class="mt-1 text-slate-800">
                  {{ voucher.loan.loan_number }}
                  <div class="text-xs text-slate-500">Member: {{ voucher.loan.member?.first_name }} {{
    voucher.loan.member?.last_name }}</div>
                </dd>
              </div>
            </div>
          </section>

          <!-- Supporting Documents -->
          <section v-if="voucher.supporting_documents && voucher.supporting_documents.length"
            class="bg-white rounded-xl shadow p-5 border">
            <div class="flex items-center justify-between">
              <h3 class="text-md font-semibold text-[#0A2342]">Supporting Documents</h3>

              <div v-if="canEdit && voucher.status === 'pending'">
                <label
                  class="inline-flex items-center gap-2 px-3 py-2 rounded bg-slate-100 text-slate-800 cursor-pointer hover:bg-slate-200">
                  Upload More
                  <input type="file" ref="fileInput" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden"
                    @change="uploadAdditionalDocuments" />
                </label>
              </div>
            </div>

            <div class="mt-4 space-y-3">
              <div v-for="(doc, idx) in voucher.supporting_documents" :key="idx"
                class="flex items-center justify-between bg-slate-50 p-3 rounded">
                <div class="flex items-center gap-3">
                  <File class="h-6 w-6 text-slate-400" />
                  <div>
                    <p class="font-medium text-slate-800">{{ doc.name }}</p>
                    <p class="text-xs text-slate-500">{{ formatFileSize(doc.size) }} • Uploaded {{
    formatDate(doc.uploaded_at) }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <button @click="downloadDocument(doc)" class="text-[#0A2342] hover:underline" title="Download">
                    <WindArrowDown class="h-4 w-4" />
                  </button>

                  <button v-if="canEdit && voucher.status === 'pending'" @click="confirmDeleteDocument(doc)"
                    class="text-red-600 hover:underline" title="Delete">
                    <Trash class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
          <!-- Actions -->
          <div class="bg-white rounded-xl shadow p-4 border">
            <h4 class="font-semibold text-[#0A2342]">Quick Actions</h4>

            <div class="mt-3 space-y-2">
              <button v-if="canApprove && voucher.status === 'pending'" @click="showApproveModal = true"
                class="w-full inline-flex items-center gap-2 justify-center px-3 py-2 rounded bg-green-600 text-white hover:brightness-95">
                <CheckCircle class="h-4 w-4" /> Approve
              </button>

              <button v-if="canApprove && voucher.status === 'pending'" @click="showRejectModal = true"
                class="w-full inline-flex items-center gap-2 justify-center px-3 py-2 rounded bg-red-600 text-white hover:brightness-95">
                <EyeClosed class="h-4 w-4" /> Reject
              </button>

              <button v-if="canPay && voucher.status === 'approved'" @click="showPaymentModal = true"
                class="w-full inline-flex items-center gap-2 justify-center px-3 py-2 rounded bg-[#0A2342] text-white hover:brightness-95">
                <BanknoteIcon class="h-4 w-4" /> Process Payment
              </button>

              <button @click="duplicateVoucher"
                class="w-full inline-flex items-center gap-2 justify-center px-3 py-2 rounded bg-slate-600 text-white hover:brightness-95">
                <Files class="h-4 w-4" /> Duplicate
              </button>

              <button @click="downloadPDF"
                class="w-full inline-flex items-center gap-2 justify-center px-3 py-2 rounded bg-slate-600 text-white hover:brightness-95">
                <WindArrowDown class="h-4 w-4" /> PDF
              </button>

              <button v-if="canDelete" @click="confirmDeleteVoucher"
                class="w-full inline-flex items-center gap-2 justify-center px-3 py-2 rounded bg-red-600 text-white hover:brightness-95">
                <Trash class="h-4 w-4" /> Delete
              </button>
            </div>
          </div>

          <!-- Workflow -->
          <div class="bg-white rounded-xl shadow p-4 border">
            <h4 class="font-semibold text-[#0A2342]">Workflow Status</h4>
            <div class="mt-3 space-y-4 text-sm text-slate-700">
              <div class="flex items-start gap-3">
                <CheckCircle2 class="h-5 w-5 text-green-500 flex-shrink-0" />
                <div>
                  <div class="font-medium">Created</div>
                  <div class="text-xs text-slate-500">{{ formatDate(voucher.created_at) }} • {{ voucher.creator?.name ||
    '—' }}</div>
                </div>
              </div>

              <div v-if="voucher.approved_by" class="flex items-start gap-3">
                <div class="flex-shrink-0">
                  <CheckCircle2 v-if="voucher.status === 'approved' || voucher.status === 'paid'"
                    class="h-5 w-5 text-green-500" />
                  <CircleX v-else class="h-5 w-5 text-red-500" />
                </div>
                <div>
                  <div class="font-medium">{{ voucher.status === 'rejected' ? 'Rejected' : 'Approved' }}</div>
                  <div class="text-xs text-slate-500">{{ formatDate(voucher.approval_date) }} • {{
    voucher.approver?.name || '—' }}</div>
                  <div v-if="voucher.approval_notes" class="text-xs text-slate-600 mt-1">{{ voucher.approval_notes }}
                  </div>
                </div>
              </div>

              <div v-if="voucher.status === 'paid'" class="flex items-start gap-3">
                <CheckCircle2 class="h-5 w-5 text-blue-500" />
                <div>
                  <div class="font-medium">Paid</div>
                  <div class="text-xs text-slate-500">{{ formatDate(voucher.payment_date) }} • {{ voucher.payer?.name ||
    '—' }}</div>
                </div>
              </div>

              <div v-if="voucher.status === 'pending'" class="flex items-start gap-3">
                <Clock class="h-5 w-5 text-yellow-500" />
                <div>
                  <div class="font-medium">Awaiting Approval</div>
                  <div class="text-xs text-slate-500">Pending review by authorized personnel</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Info -->
          <div class="bg-white rounded-xl shadow p-4 border">
            <h4 class="font-semibold text-[#0A2342]">Quick Info</h4>
            <div class="mt-3 text-sm text-slate-700 space-y-2">
              <div class="flex justify-between"><span class="text-slate-500">Created</span><span class="font-medium">{{
    formatDate(voucher.created_at) }}</span></div>
              <div v-if="voucher.approval_date" class="flex justify-between"><span class="text-slate-500">{{
    voucher.status === 'rejected' ? 'Rejected' : 'Approved' }}</span><span class="font-medium">{{
    formatDate(voucher.approval_date) }}</span></div>
              <div v-if="voucher.payment_date" class="flex justify-between"><span
                  class="text-slate-500">Paid</span><span class="font-medium">{{ formatDate(voucher.payment_date)
                  }}</span></div>
            </div>
          </div>
        </aside>
      </div>
    </div>

    <!-- APPROVE MODAL -->
    <Modal :show="showApproveModal" @close="showApproveModal = false">
      <div class="p-4">
        <div class="flex items-center gap-3">
          <CheckCircle2 class="h-6 w-6 text-green-600" />
          <h3 class="font-semibold text-lg">Approve Voucher</h3>
        </div>
        <p class="text-sm text-slate-600 mt-2">Approve payment of <strong>{{ formatCurrency(voucher.amount) }}</strong>
          to <strong>{{ voucher.payee_name }}</strong>?</p>

        <form @submit.prevent="approveVoucher" class="mt-4 space-y-3">
          <textarea v-model="approvalForm.approval_notes" rows="3" placeholder="Optional approval notes"
            class="w-full border rounded p-2"></textarea>

          <div class="flex justify-end gap-3">
            <button type="button" @click="showApproveModal = false"
              class="px-3 py-2 rounded bg-slate-200">Cancel</button>
            <button type="submit" :disabled="approvalForm.processing" class="px-3 py-2 rounded bg-green-600 text-white">
              <span v-if="approvalForm.processing">Approving...</span><span v-else>Approve</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- REJECT MODAL -->
    <Modal :show="showRejectModal" @close="showRejectModal = false">
      <div class="p-4">
        <div class="flex items-center gap-3">
          <CircleX class="h-6 w-6 text-red-600" />
          <h3 class="font-semibold text-lg">Reject Voucher</h3>
        </div>
        <p class="text-sm text-slate-600 mt-2">Please provide a reason for rejecting this voucher.</p>

        <form @submit.prevent="rejectVoucher" class="mt-4 space-y-3">
          <textarea v-model="rejectionForm.rejection_reason" rows="3" placeholder="Rejection reason (required)"
            class="w-full border rounded p-2" required></textarea>
          <p v-if="rejectionForm.errors.rejection_reason" class="text-xs text-red-600">{{
    rejectionForm.errors.rejection_reason }}</p>

          <div class="flex justify-end gap-3">
            <button type="button" @click="showRejectModal = false"
              class="px-3 py-2 rounded bg-slate-200">Cancel</button>
            <button type="submit" :disabled="rejectionForm.processing" class="px-3 py-2 rounded bg-red-600 text-white">
              <span v-if="rejectionForm.processing">Rejecting...</span><span v-else>Reject</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- PAYMENT MODAL -->
    <Modal :show="showPaymentModal" @close="showPaymentModal = false" max-width="2xl">
      <div class="p-4">
        <div class="flex items-center gap-3">
          <BanknoteIcon class="h-6 w-6 text-[#0A2342]" />
          <h3 class="font-semibold text-lg">Process Payment</h3>
        </div>
        <p class="text-sm text-slate-600 mt-2">Process payment to <strong>{{ voucher.payee_name }}</strong> for
          <strong>{{ formatCurrency(voucher.amount) }}</strong>.
        </p>

        <form @submit.prevent="processPayment" class="mt-4 space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <select v-model="paymentForm.payment_method" required class="border rounded p-2">
              <option value="">Select payment method</option>
              <option value="cash">Cash</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
            </select>

            <!-- Payment Account -->
            <label class="text-sm text-slate-600">Payment Account</label>
            <select v-model="paymentForm.acc_id" class="w-full border rounded p-2 mt-1" required>
              <option value="">-- Select Account --</option>
              <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                {{ formatAccount(acc) }}
              </option>

            </select>

            <input v-model="paymentForm.payment_reference" placeholder="Payment reference (optional)"
              class="border rounded p-2" />
          </div>

          <textarea v-model="paymentForm.payment_notes" rows="3" placeholder="Payment notes (optional)"
            class="w-full border rounded p-2"></textarea>

          <div class="mt-3 bg-slate-50 p-3 rounded">
            <div class="flex justify-between text-sm text-slate-700">
              <div>Payee</div>
              <div class="font-medium">{{ voucher.payee_name }}</div>
            </div>
            <div class="flex justify-between text-sm text-slate-700 mt-1">
              <div>Amount</div>
              <div class="font-semibold text-[#0A2342]">{{ formatCurrency(voucher.amount) }}</div>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-3">
            <button type="button" @click="showPaymentModal = false"
              class="px-3 py-2 rounded bg-slate-200">Cancel</button>
            <button type="submit" :disabled="paymentForm.processing" class="px-3 py-2 rounded bg-[#0A2342] text-white">
              <span v-if="paymentForm.processing">Processing...</span><span v-else>Process Payment</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- CANCEL CONFIRMATION MODAL -->
    <Modal :show="showCancelConfirm" @close="showCancelConfirm = false">
      <div class="p-4">
        <div class="flex items-center gap-3">
          <CircleX class="h-6 w-6 text-gray-600" />
          <h3 class="font-semibold text-lg">Cancel Voucher</h3>
        </div>

        <form @submit.prevent="cancelVoucher" class="mt-4 space-y-3">
          <textarea v-model="cancellationForm.cancellation_reason" rows="3" placeholder="Reason for cancellation"
            class="w-full border rounded p-2" required></textarea>
          <div class="flex justify-end gap-3">
            <button type="button" @click="showCancelConfirm = false"
              class="px-3 py-2 rounded bg-slate-200">Close</button>
            <button type="submit" :disabled="cancellationForm.processing"
              class="px-3 py-2 rounded bg-gray-700 text-white">
              <span v-if="cancellationForm.processing">Cancelling...</span><span v-else>Cancel Voucher</span>
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- SMALL CONFIRM DELETE DOCUMENT MODAL -->
    <Modal :show="showDeleteDocConfirm" @close="showDeleteDocConfirm = false">
      <div class="p-4">
        <h3 class="font-semibold">Delete Document</h3>
        <p class="text-sm text-slate-600 mt-2">Are you sure you want to delete <strong>{{ docToDelete?.name }}</strong>?
        </p>

        <div class="flex justify-end gap-3 mt-4">
          <button @click="showDeleteDocConfirm = false" class="px-3 py-2 rounded bg-slate-200">Cancel</button>
          <button @click="deleteDocumentConfirmed" :disabled="docDeleteProcessing"
            class="px-3 py-2 rounded bg-red-600 text-white">
            <span v-if="docDeleteProcessing">Deleting...</span><span v-else>Delete</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- CONFIRM DELETE VOUCHER -->
    <Modal :show="showDeleteVoucherConfirm" @close="showDeleteVoucherConfirm = false">
      <div class="p-4">
        <h3 class="font-semibold">Delete Voucher</h3>
        <p class="text-sm text-slate-600 mt-2">This action cannot be undone. Delete voucher <strong>{{
    voucher.voucher_number }}</strong>?</p>

        <div class="flex justify-end gap-3 mt-4">
          <button @click="showDeleteVoucherConfirm = false" class="px-3 py-2 rounded bg-slate-200">Cancel</button>
          <button @click="deleteVoucherConfirmed" :disabled="voucherDeleteProcessing"
            class="px-3 py-2 rounded bg-red-600 text-white">
            <span v-if="voucherDeleteProcessing">Deleting...</span><span v-else>Delete Voucher</span>
          </button>
        </div>
      </div>
    </Modal>

  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import {
  ArrowLeft, Cog, ChevronDown, Pencil, Paperclip, Files, WindArrowDown, Trash,
  OctagonAlert, CircleX, File, CheckCircle2, CheckCircle, AlertCircle, Clock, EyeClosed, BanknoteIcon
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'

const props = defineProps({
  voucher: Object,
  canApprove: Boolean,
  canPay: Boolean,
  canEdit: Boolean,
  canDelete: Boolean,
  accounts: {
    type: Array,
    default: () => [],
  }
})

// UI state
const showActionsMenu = ref(false)
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const showPaymentModal = ref(false)
const showCancelConfirm = ref(false)
const showDeleteDocConfirm = ref(false)
const showDeleteVoucherConfirm = ref(false)

const docToDelete = ref(null)
const docDeleteProcessing = ref(false)
const voucherDeleteProcessing = ref(false)

const fileInput = ref(null)
const actionsRoot = ref(null)



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

// Forms
const approvalForm = useForm({ approval_notes: '' })
const rejectionForm = useForm({ rejection_reason: '' })
const paymentForm = useForm({ payment_method: '', acc_id: "", payment_reference: '', payment_notes: '' })
const cancellationForm = useForm({ cancellation_reason: '' })

// Derived & helpers
const voucher = props.voucher || {}
const statusBadgeClass = computed(() => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  }
  return map[voucher.status] || 'bg-gray-100 text-gray-800'
})

function formatDate(date) {
  if (!date) return '—'
  const d = new Date(date)
  return d.toLocaleString('en-KE', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function formatCurrency(amount) {
  if (amount == null) return '—'
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount)
}

function formatFileSize(bytes) {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

function formatStatus(s) {
  const map = { pending: 'Pending', approved: 'Approved', paid: 'Paid', rejected: 'Rejected', cancelled: 'Cancelled' }
  return map[s] || (s || '—')
}


function formatAccount(acc) {
  if (!acc) return '—'
  const name = acc.account_name || acc.name || '—'
  const number = acc.account_number || '—'
  return `${name} (${number})`
}




function formatVoucherType(t) {
  const map = {
    loan_disbursement: 'Loan Disbursement',
    operational_expense: 'Operational Expense',
    dividend_payment: 'Dividend Payment',
    refund: 'Refund',
    other: 'Other',
  }
  return map[t] || (t || '—')
}

// Actions menu toggle & click-away
function toggleActionsMenu() {
  showActionsMenu.value = !showActionsMenu.value
}

function onDocumentClick(e) {
  if (!actionsRoot.value) return
  if (!actionsRoot.value.contains(e.target)) {
    showActionsMenu.value = false
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick))

// Router actions
function submitVoucher() {
  router.post(route('vouchers.submit', voucher.id))
}

function approveVoucher() {
  approvalForm.post(route('vouchers.approve', voucher.id), {
    onSuccess: () => {
      showApproveModal.value = false
      approvalForm.reset()
    },
    onError: (errors) => {
      showApproveModal.value = false
      approvalForm.reset()
      flashMessage.value = errors.error || 'Failed to approve voucher.'
      flashType.value = 'error'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  })
}

function rejectVoucher() {
  rejectionForm.post(route('vouchers.reject', voucher.id), {
    onSuccess: () => {
      showRejectModal.value = false
      rejectionForm.reset()
    },
    onError: (errors) => {
      showRejectModal.value = false
      rejectionForm.reset()
      flashMessage.value = errors.error || 'Failed to reject voucher.'
      flashType.value = 'error'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  })
}

function processPayment() {
  paymentForm.post(route('vouchers.pay', voucher.id), {
    onSuccess: () => {
      showPaymentModal.value = false
      paymentForm.reset()
    },
    onError: (errors) => {
      console.log("err", errors)
      showPaymentModal.value = false
      paymentForm.reset()
      flashMessage.value = 'Payment failed. Try again later'
      flashType.value = 'error'
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  })
}

function cancelVoucher() {
  cancellationForm.post(route('vouchers.cancel', voucher.id), {
    onSuccess: () => {
      showCancelConfirm.value = false
      cancellationForm.reset()
    }
  })
}

function duplicateVoucher() {
  router.post(route('vouchers.duplicate', voucher.id))
}

function downloadPDF() {
  // open in new tab
  window.open(route('vouchers.download-pdf', voucher.id), '_blank')
}

function confirmDeleteDocument(document) {
  docToDelete.value = document
  showDeleteDocConfirm.value = true
}

function deleteDocumentConfirmed() {
  if (!docToDelete.value) return
  docDeleteProcessing.value = true
  // Assuming route expects [voucherId, documentPath] like original
  router.delete(route('vouchers.delete-document', [voucher.id, docToDelete.value.path]), {
    onFinish: () => {
      docDeleteProcessing.value = false
      showDeleteDocConfirm.value = false
      docToDelete.value = null
    }
  })
}

function confirmDeleteVoucher() {
  showDeleteVoucherConfirm.value = true
}

function deleteVoucherConfirmed() {
  voucherDeleteProcessing.value = true
  router.delete(route('vouchers.destroy', voucher.id), {
    onFinish: () => {
      voucherDeleteProcessing.value = false
      showDeleteVoucherConfirm.value = false
    }
  })
}

// Document download & upload
function downloadDocument(document) {
  if (!document || !document.path) return
  window.open(`/storage/${document.path}`, '_blank')
}

function uploadAdditionalDocuments(event) {
  const files = Array.from(event.target.files || [])
  if (!files.length) return

  const fd = new FormData()
  files.forEach((f, i) => fd.append(`documents[${i}]`, f))

  router.post(route('vouchers.upload-documents', voucher.id), fd, {
    headers: { 'Content-Type': 'multipart/form-data' },
    onSuccess: () => {
      event.target.value = ''
    }
  })
}
</script>

<style scoped>
/* Small transitions used for menus/modals */
.fade-enter-active,
.fade-leave-active {
  transition: opacity .15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

button:hover {
  cursor: pointer;
}
</style>
