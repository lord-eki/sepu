<template>
  <AppLayout :breadcrumbs="[
    { title: 'Dividends', href: '/dividends' },
    { title: `Dividend ${dividend.dividend_year}` }
  ]">
   <Head title="View Dividend" />
    <!-- Flash -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 z-50 px-4">
      <transition name="fade-slide">
        <div v-if="flashMessage" :class="[
          'mb-4 rounded-md p-3 shadow flex items-center gap-3',
          flashType === 'success'
            ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-400'
            : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-400'
        ]">
          <svg v-if="flashType === 'success'" class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
          </svg>
          <svg v-else class="h-5 w-5 text-red-600 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
          </svg>
          <p class="text-sm">{{ flashMessage }}</p>
          <button type="button" class="ml-auto text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white" @click="flashMessage = null">✕</button>
        </div>
      </transition>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 max-w-7xl px-4 sm:px-6 lg:px-8 mt-2">
      <div class="flex flex-col gap-2">
        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">
          Dividend {{ dividend.dividend_year }}
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
          Status:
          <span :class="[
            'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
            getStatusClass(dividend.status)
          ]">
            {{ formatStatus(dividend.status) }}
          </span>
        </p>
      </div>

      <div class="flex flex-wrap items-start gap-3 mt-3 md:mt-0">
        <!-- Back -->
        <Link :href="route('dividends.index')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900 transition">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" />
          </svg>
          Back
        </Link>

        <!-- Edit -->
        <Link v-if="dividend.status === 'calculated'" :href="route('dividends.edit', dividend.id)" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-800 text-white text-sm font-semibold hover:bg-blue-900 transition">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
          </svg>
          Edit
        </Link>

        <!-- Approve -->
        <button v-if="canApprove" @click="showApproveModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
          </svg>
          Approve
        </button>

        <!-- Distribute -->
        <button v-if="canDistribute" @click="showDistributeModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 transition">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM3 4a1 1 0 011-1h1a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM8 4a1 1 0 011-1h6a1 1 0 011 1v3a1 1 0 01-1 1H9a1 1 0 01-1-1V4z" />
          </svg>
          Distribute
        </button>

        <!-- Reverse -->
        <button v-if="dividend.status === 'distributed'" @click="showReverseModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
          </svg>
          Reverse
        </button>
      </div>
    </div>

    <!-- Main content -->
    <main class="max-w-7xl px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

      <!-- Overview cards -->
      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="rounded-xl shadow-sm bg-white dark:bg-gray-800 p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-200">Total Profit</p>
          <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">KSh {{ formatCurrency(dividend.total_profit) }}</p>
        </div>

        <div class="rounded-xl shadow-sm bg-white dark:bg-gray-800 p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-200">Dividend Rate</p>
          <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ dividend.dividend_rate }}%</p>
        </div>

        <div class="rounded-xl shadow-sm bg-white dark:bg-gray-800 p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-200">Total Dividends</p>
          <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">KSh {{ formatCurrency(dividend.total_dividends) }}</p>
        </div>

        <div class="rounded-xl shadow-sm bg-white dark:bg-gray-800 p-5 border border-gray-100 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-200">Profit Utilization</p>
          <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ profitUtilization }}%</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">of total profit</p>
        </div>
      </section>

      <!-- Stats rows -->
      <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Member Statistics -->
        <div class="rounded-xl shadow-md bg-white dark:bg-gray-900 p-6 border border-gray-100 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Member Statistics</h3>
          <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <div class="flex justify-between"><span>Total Members</span><strong>{{ stats.total_members }}</strong></div>
            <div class="flex justify-between"><span>Members Paid</span><strong class="text-green-600 dark:text-green-400">{{ stats.members_paid }}</strong></div>
            <div class="flex justify-between"><span>Members Pending</span><strong class="text-orange-500 dark:text-orange-400">{{ stats.members_pending }}</strong></div>
            <div class="border-t pt-3 flex justify-between"><span class="font-medium">Average Dividend</span><strong>KSh {{ formatCurrency(averageDividend) }}</strong></div>
          </div>
        </div>

        <!-- Payment Statistics -->
        <div class="rounded-xl shadow-md bg-white dark:bg-gray-900 p-6 border border-gray-100 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment Statistics</h3>
          <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <div class="flex justify-between"><span>Total Distributed</span><strong class="text-green-600 dark:text-green-400">KSh {{ formatCurrency(stats.total_paid) }}</strong></div>
            <div class="flex justify-between"><span>Pending Distribution</span><strong class="text-orange-500 dark:text-orange-400">KSh {{ formatCurrency(stats.total_pending) }}</strong></div>

            <div class="border-t pt-3">
              <div class="flex justify-between items-center"><span class="font-medium">Distribution Progress</span><strong>{{ distributionProgress }}%</strong></div>
              <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-gray-900 dark:bg-gray-100 rounded-full transition-all duration-500" :style="{ width: distributionProgress + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Timeline -->
      <section class="rounded-xl shadow-md bg-white dark:bg-gray-900 p-6 border border-gray-100 dark:border-gray-700 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Dividend Timeline</h3>

        <div class="ml-4 pl-6 border-l-2 border-gray-200 dark:border-gray-700 flex flex-col gap-6">
          
          <!-- Calculated -->
          <div class="relative flex items-center gap-4">
            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-900 dark:bg-gray-700 flex items-center justify-center text-white shadow-sm">C</div>
            <div class="flex-1">
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Calculated on <strong class="text-gray-900 dark:text-gray-100">{{ formatDate(dividend.calculation_date) }}</strong>
                <span v-if="dividend.calculated_by" class="ml-2 text-gray-700 dark:text-gray-400">by {{ dividend.calculated_by.name }}</span>
              </p>
            </div>
          </div>

          <!-- Approved -->
          <div v-if="dividend.approval_date" class="relative flex items-center gap-4">
            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-600 flex items-center justify-center text-white shadow-sm">A</div>
            <div class="flex-1">
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Approved on <strong class="text-gray-900 dark:text-gray-100">{{ formatDate(dividend.approval_date) }}</strong>
                <span v-if="dividend.approved_by" class="ml-2 text-gray-700 dark:text-gray-400">by {{ dividend.approved_by.name }}</span>
              </p>
              <p v-if="dividend.approval_notes" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ dividend.approval_notes }}</p>
            </div>
          </div>

          <!-- Distributed -->
          <div v-if="dividend.distribution_date" class="relative flex items-center gap-4">
            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center text-white shadow-sm">D</div>
            <div class="flex-1">
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Distributed on <strong class="text-gray-900 dark:text-gray-100">{{ formatDate(dividend.distribution_date) }}</strong>
              </p>
            </div>
          </div>

          <!-- Reversed -->
          <div v-if="dividend.reversed_at" class="relative flex items-center gap-4">
            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white shadow-sm">R</div>
            <div class="flex-1">
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Reversed on <strong class="text-gray-900 dark:text-gray-100">{{ formatDate(dividend.reversed_at) }}</strong>
              </p>
              <p v-if="dividend.reversal_reason" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Reason: {{ dividend.reversal_reason }}
              </p>
            </div>
          </div>

        </div>
      </section>


      <!-- Notes -->
      <section v-if="dividend.notes" class="rounded-xl shadow-md bg-white dark:bg-gray-900 p-6 border border-gray-100 dark:border-gray-700 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Notes</h3>
        <p class="text-gray-700 dark:text-gray-300">{{ dividend.notes }}</p>
      </section>

      <!-- Member Dividends Table -->
      <section class="rounded-xl shadow-md bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 overflow-x-auto mt-6">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Member Dividends</h3>
          <div class="flex items-center gap-3 text-sm">
            <Link :href="route('dividends.members', dividend.id)" class="text-gray-900 dark:text-gray-100 hover:underline">View All Members</Link>
            <span class="text-gray-200 dark:text-gray-500">|</span>
            <Link :href="route('dividends.report', dividend.id)" class="text-gray-900 dark:text-gray-100 hover:underline">Generate Report</Link>
          </div>
        </div>

        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300 uppercase">
            <tr>
              <th class="px-6 py-3 text-left">Member</th>
              <th class="px-6 py-3 text-left">Shares Balance</th>
              <th class="px-6 py-3 text-left">Dividend Amount</th>
              <th class="px-6 py-3 text-left">Status</th>
              <th class="px-6 py-3 text-left">Payment Date</th>
              <th class="px-6 py-3 text-right">Actions</th>
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="memberDividend in memberDividends.data" :key="memberDividend.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ memberDividend.member.first_name }} {{ memberDividend.member.last_name }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ memberDividend.member.membership_id }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">KSh {{ formatCurrency(memberDividend.shares_balance) }}</td>
              <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">KSh {{ formatCurrency(memberDividend.dividend_amount) }}</td>
              <td class="px-6 py-4">
                <span :class="['inline-flex px-2 py-1 rounded-full text-xs font-semibold', getPaymentStatusClass(memberDividend.status)]">
                  {{ formatStatus(memberDividend.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ memberDividend.payment_date ? formatDate(memberDividend.payment_date) : 'Not paid' }}
              </td>
              <td class="px-6 py-4 text-right text-sm">
                <div class="flex justify-end items-center gap-3">
                  <Link :href="route('dividends.member-details', [dividend.id, memberDividend.member.id])" class="text-gray-900 dark:text-gray-100 hover:underline">View</Link>
                  <button v-if="memberDividend.status === 'pending' && dividend.status === 'approved'"
                    @click="payMember(memberDividend)" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">Pay</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
      <div v-if="memberDividends.links" class="px-6 py-3 border-t border-gray-100 dark:border-gray-700">
          <Pagination :data="memberDividends" />
        </div>
    </main>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-900 mx-3 dark:border w-full max-w-md rounded-xl shadow-lg p-5 animate-fadeIn">

        <!-- Header -->
        <div class="flex items-center gap-3 mb-4">
          <div class="h-8 w-8 rounded-full bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 flex items-center justify-center font-bold">A</div>
          <div>
            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Approve Dividend</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Dividend {{ dividend.dividend_year }}</p>
          </div>
        </div>

        <!-- Content -->
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
          Are you sure you want to approve this dividend?
        </p>

        <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="approval_notes">Approval Notes (optional)</label>
        <TextArea id="approval_notes" v-model="approvalForm.approval_notes" rows="3"
          class="w-full rounded-md p-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          placeholder="Add any notes..." />

        <!-- Footer -->
        <div class="mt-5 flex justify-end gap-3">
          <button @click="showApproveModal = false" 
            class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
            Cancel
          </button>

          <button @click="submitApproval" :disabled="processing"
            class="px-4 py-2 rounded-lg bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-sm font-semibold disabled:opacity-50 hover:bg-gray-800 dark:hover:bg-gray-200">
            <span v-if="processing">Approving...</span>
            <span v-else>Approve</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Distribute Modal -->
    <div v-if="showDistributeModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white mx-3 dark:border dark:bg-gray-900 w-full max-w-md rounded-xl shadow-lg p-5 animate-fadeIn">

        <!-- Header -->
        <div class="flex items-center gap-3 mb-4">
          <div class="h-8 w-8 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">D</div>
          <div>
            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Distribute Dividends</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Dividend {{ dividend.dividend_year }}</p>
          </div>
        </div>

        <!-- Content -->
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
          This will transfer <strong>KSh {{ formatCurrency(dividend.total_dividends) }}</strong>
          to <strong>{{ stats.total_members }}</strong> members. This action cannot be easily undone.
        </p>

        <div class="rounded-md bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 p-3 text-sm text-yellow-700 dark:text-yellow-300">
          Please confirm you want to proceed.
        </div>

        <!-- Footer -->
        <div class="mt-5 flex justify-end gap-3">
          <button @click="showDistributeModal = false" 
            class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
            Cancel
          </button>

          <button @click="submitDistribution" :disabled="processing"
            class="px-4 py-2 rounded-lg bg-orange-500 text-white dark:text-gray-900 text-sm font-semibold disabled:opacity-50 hover:bg-orange-600">
            <span v-if="processing">Distributing...</span>
            <span v-else>Distribute</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Reverse Modal -->
    <div v-if="showReverseModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white mx-3 dark:border dark:bg-gray-900 w-full max-w-md rounded-xl shadow-lg p-5 animate-fadeIn">

        <!-- Header -->
        <div class="flex items-center gap-3 mb-4">
          <div class="h-8 w-8 rounded-full bg-red-600 text-white flex items-center justify-center font-bold">R</div>
          <div>
            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100">Reverse Distribution</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Dividend {{ dividend.dividend_year }}</p>
          </div>
        </div>

        <!-- Content -->
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
          Are you sure you want to reverse the distribution? This will roll back payments and return funds to the system.
        </p>

        <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1" for="reversal_reason">Reason for reversal <span class="text-red-500">*</span></label>
        <TextArea id="reversal_reason" v-model="reversalForm.reason" required rows="3"
          class="w-full border rounded-md p-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          placeholder="Provide reason..." />
        <InputError :message="reversalForm.errors.reason" class="mt-2" />

        <!-- Footer -->
        <div class="mt-5 flex justify-end gap-3">
          <button @click="showReverseModal = false" 
            class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
            Cancel
          </button>

          <button @click="submitReversal" :disabled="processing"
            class="px-4 py-2 rounded-lg bg-red-600 text-white dark:text-gray-900 text-sm font-semibold disabled:opacity-50 hover:bg-red-700">
            <span v-if="processing">Reversing...</span>
            <span v-else>Reverse</span>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, useForm, Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import TextArea from '@/components/TextArea.vue'
import InputError from '@/components/InputError.vue'

const props = defineProps({
  dividend: Object,
  memberDividends: Object,
  stats: Object,
  canApprove: Boolean,
  canDistribute: Boolean
})

/* flash handling (auto-show from Inertia page props) */
const page = usePage()
const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

watch(
  () => page.props,
  (p) => {
    if (p.flash?.success) { flashMessage.value = p.flash.success; flashType.value = 'success' }
    else if (p.flash?.error) { flashMessage.value = p.flash.error; flashType.value = 'error' }
    else if (p.errors?.error) { flashMessage.value = p.errors.error; flashType.value = 'error' }

    if (flashMessage.value) {
      flashBox.value?.scrollIntoView?.({ behavior: 'smooth', block: 'start' })
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

/* local state */
const showApproveModal = ref(false)
const showDistributeModal = ref(false)
const showReverseModal = ref(false)
const processing = ref(false)

const approvalForm = useForm({ approval_notes: '' })
const reversalForm = useForm({ reason: '' })

/* computed helpers */
const profitUtilization = computed(() => {
  if (!props.dividend?.total_profit) return 0
  return Math.round((props.dividend.total_dividends / props.dividend.total_profit) * 100)
})

const averageDividend = computed(() => {
  if (!props.stats?.total_members) return 0
  return (props.dividend.total_dividends || 0) / props.stats.total_members
})

const distributionProgress = computed(() => {
  if (!props.dividend?.total_dividends) return 0
  return Math.round((props.stats.total_paid / props.dividend.total_dividends) * 100) || 0
})

/* utility functions */
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
}

const formatDate = (d) => {
  if (!d) return 'N/A'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatStatus = (s = '') => s ? s.charAt(0).toUpperCase() + s.slice(1) : ''

const getStatusClass = (status) => {
  switch (status) {
    case 'calculated': return 'bg-yellow-100 text-yellow-800'
    case 'approved': return 'bg-blue-100 text-[#0A1A2F]'
    case 'distributed': return 'bg-green-100 text-green-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getPaymentStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'paid': return 'bg-green-100 text-green-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

/* actions */
const submitApproval = () => {
  processing.value = true
  approvalForm.post(route('dividends.approve', props.dividend.id), {
    onFinish: () => {
      processing.value = false
      showApproveModal.value = false
    }
  })
}

const submitDistribution = () => {
  processing.value = true
  router.post(route('dividends.distribute', props.dividend.id), {}, {
    onFinish: () => {
      processing.value = false
      showDistributeModal.value = false
    }
  })
}

const submitReversal = () => {
  processing.value = true

  reversalForm.post(route('dividends.reverse', props.dividend.id), {

    onSuccess: () => {
      // Close only when request succeeds
      showReverseModal.value = false
      reversalForm.reset()
    },

    onError: () => {
      //  Do NOT close the modal here
      // Modal stays open and errors show
    },

    onFinish: () => {
      // Stop spinner always
      processing.value = false
    }
  })
}


const payMember = (memberDividend) => {
  processing.value = true
  router.post(route('dividends.pay-member', [props.dividend.id, memberDividend.member.id]), {}, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all .25s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-6px);
}

.fade-slide-enter-to {
  opacity: 1;
  transform: translateY(0);
}

.fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

button:hover {
  cursor: pointer;
}
</style>
