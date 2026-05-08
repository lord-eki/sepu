<template>
  <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/dividends' }]">
    <Head title="Dividends" />

    <div class="min-h-screen bg-slate-50 dark:bg-[#081122] p-4 sm:p-6 space-y-6">
      <!-- Flash -->
      <div ref="flashBox" class="max-w-3xl mx-auto">
        <transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div
            v-if="flashMessage"
            class="rounded-2xl border px-5 py-4 shadow-lg backdrop-blur-xl flex items-start gap-3"
            :class="flashType === 'success'
              ? 'bg-emerald-50/90 border-emerald-200 text-emerald-700 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300'
              : 'bg-rose-50/90 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300'"
          >
            <div class="mt-0.5 shrink-0">
              <svg
                v-if="flashType === 'success'"
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              <svg
                v-else
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M18 10A8 8 0 114.293 4.293 8 8 0 0118 10zM9 7a1 1 0 012 0v3a1 1 0 11-2 0V7zm1 8a1.25 1.25 0 100-2.5A1.25 1.25 0 0010 15z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>

            <div class="flex-1 text-sm font-medium">{{ flashMessage }}</div>

            <button
              type="button"
              class="text-current/70 hover:text-current transition"
              @click="flashMessage = null"
            >
              ✕
            </button>
          </div>
        </transition>
      </div>

      <!-- Hero -->
      <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 p-6 sm:p-8 shadow-2xl shadow-blue-950/20">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(249,115,22,0.18),_transparent_30%)]" />
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
              Dividends Management
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
              Track, approve, and distribute member dividends with clarity and control.
            </p>
          </div>

          <div class="flex flex-wrap gap-3">
            <Link
              :href="route('dividends.analytics.history')"
              class="inline-flex items-center gap-2 rounded-2xl border border-orange-400/20 bg-white/10 px-4 py-3 text-sm font-medium text-orange-300 backdrop-blur-xl transition hover:bg-white/15"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 13l4-4 3 3 5-6a1 1 0 111.6 1.2l-6 7a1 1 0 01-1.4.1L8 11l-3.3 3.3a1 1 0 11-1.4-1.4z" />
              </svg>
              Analytics
            </Link>

            <Link
              :href="route('dividends.create')"
              class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
              </svg>
              Calculate New Dividend
            </Link>
          </div>
        </div>
      </section>

      <!-- Stats -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div
          v-for="card in cards"
          :key="card.label"
          class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white/90 dark:bg-slate-900/80 p-5 shadow-sm hover:shadow-xl transition-all duration-300"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
              <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ card.value }}</h3>
            </div>

            <div :class="[card.color, 'flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-lg']">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path :d="card.icon" />
              </svg>
            </div>
          </div>
        </div>
      </section>

      <!-- Filters -->
      <section class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white/90 dark:bg-slate-900/80 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex flex-wrap gap-3">
            <select v-model="filters.status" @change="applyFilters" class="filter-input">
              <option value="all">All Status</option>
              <option value="calculated">Calculated</option>
              <option value="approved">Approved</option>
              <option value="distributed">Distributed</option>
            </select>

            <select v-model="filters.year" @change="applyFilters" class="filter-input">
              <option value="">All Years</option>
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>
        </div>
      </section>

      <!-- Table -->
      <section class="overflow-hidden rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white/90 dark:bg-slate-900/80 shadow-sm">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300">
              <tr>
                <th
                  v-for="head in tableHeaders"
                  :key="head"
                  class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider"
                >
                  {{ head }}
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="dividend in dividends.data"
                :key="dividend.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
              >
                <td class="px-6 py-4 font-semibold text-blue-700 dark:text-blue-400">
                  {{ dividend.dividend_year }}
                </td>

                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                  KSh {{ formatCurrency(dividend.total_profit) }}
                </td>

                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                  {{ dividend.dividend_rate }}%
                </td>

                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                  KSh {{ formatCurrency(dividend.total_dividends) }}
                </td>

                <td class="px-6 py-4">
                  <span :class="[getStatusClass(dividend.status), 'rounded-full px-3 py-1 text-xs font-semibold']">
                    {{ formatStatus(dividend.status) }}
                  </span>
                </td>

                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs leading-6">
                  <div>Calculated: {{ formatDate(dividend.calculation_date) }}</div>
                  <div v-if="dividend.approval_date">Approved: {{ formatDate(dividend.approval_date) }}</div>
                  <div v-if="dividend.distribution_date">Distributed: {{ formatDate(dividend.distribution_date) }}</div>
                </td>

                <td class="px-6 py-4">
                  <div class="flex flex-wrap items-center justify-end gap-3 text-sm font-medium">
                    <Link
                      :href="route('dividends.show', dividend.id)"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                    >
                      View
                    </Link>

                    <Link
                      v-if="dividend.status === 'calculated'"
                      :href="route('dividends.edit', dividend.id)"
                      class="text-orange-500 hover:text-orange-600"
                    >
                      Edit
                    </Link>

                    <button
                      v-if="dividend.status === 'calculated'"
                      @click="approveDividend(dividend)"
                      class="text-emerald-600 hover:text-emerald-700"
                    >
                      Approve
                    </button>

                    <button
                      v-if="dividend.status === 'approved'"
                      @click="distributeDividend(dividend)"
                      class="text-violet-600 hover:text-violet-700"
                    >
                      Distribute
                    </button>

                    <button
                      v-if="dividend.status === 'calculated'"
                      @click="confirmDelete(dividend)"
                      class="text-rose-600 hover:text-rose-700"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="dividends.links" class="border-t border-slate-200 dark:border-slate-800 px-6 py-4">
          <Pagination :data="dividends" />
        </div>
      </section>
    </div>

    <!-- Full-page loader -->
    <div v-if="processing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70">
      <div class="h-16 w-16 rounded-full border-4 border-white border-t-transparent animate-spin"></div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete Dividend</h3>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
          Are you sure you want to delete the dividend for {{ selectedDividend?.dividend_year }}? This action cannot be undone.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200"
          >
            Cancel
          </button>
          <button
            @click="deleteDividend"
            :disabled="processing"
            class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Approve Dividend</h3>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
          Approve dividend for {{ selectedDividend?.dividend_year }}.
        </p>

        <div class="mt-4">
          <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
            Approval Notes (Optional)
          </label>
          <textarea
            v-model="approvalForm.approval_notes"
            rows="3"
            class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-500"
          />
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showApproveModal = false"
            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200"
          >
            Cancel
          </button>
          <button
            @click="submitApproval"
            :disabled="processing"
            class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800"
          >
            Approve
          </button>
        </div>
      </div>
    </div>

    <!-- Distribute Modal -->
    <div v-if="showDistributeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Distribute Dividend</h3>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
          Distribute dividend for {{ selectedDividend?.dividend_year }} to all eligible member accounts.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showDistributeModal = false"
            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200"
          >
            Cancel
          </button>
          <button
            @click="submitDistribution"
            :disabled="processing"
            class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700"
          >
            Distribute
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Link, router, useForm, Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  dividends: Object,
  availableYears: Array,
  filters: Object,
  stats: Object,
})

const page = usePage()
const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

watch(
  () => page.props,
  (p) => {
    if (p.flash?.success) {
      flashMessage.value = p.flash.success
      flashType.value = 'success'
    } else if (p.flash?.error) {
      flashMessage.value = p.flash.error
      flashType.value = 'error'
    } else if (p.errors?.error) {
      flashMessage.value = p.errors.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      flashBox.value?.scrollIntoView?.({ behavior: 'smooth', block: 'start' })
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

const showDeleteModal = ref(false)
const showApproveModal = ref(false)
const showDistributeModal = ref(false)
const selectedDividend = ref(null)
const processing = ref(false)

const filters = reactive({
  status: props.filters?.status || 'all',
  year: props.filters?.year || '',
})

const approvalForm = useForm({
  approval_notes: '',
})

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)

const formatDate = (date) =>
  date
    ? new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
      })
    : 'N/A'

const formatStatus = (status) => status.charAt(0).toUpperCase() + status.slice(1)

const getStatusClass = (status) =>
  ({
    calculated: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    approved: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    distributed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
  }[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300')

const applyFilters = () =>
  router.get(route('dividends.index'), filters, {
    preserveState: true,
    replace: true,
  })

const confirmDelete = (dividend) => {
  selectedDividend.value = dividend
  showDeleteModal.value = true
}

const deleteDividend = () => {
  if (!selectedDividend.value) return
  processing.value = true

  router.delete(route('dividends.destroy', selectedDividend.value.id), {
    onFinish: () => {
      processing.value = false
      showDeleteModal.value = false
      selectedDividend.value = null
    },
  })
}

const approveDividend = (dividend) => {
  selectedDividend.value = dividend
  approvalForm.reset()
  showApproveModal.value = true
}

const distributeDividend = (dividend) => {
  selectedDividend.value = dividend
  showDistributeModal.value = true
}

const submitApproval = () => {
  if (!selectedDividend.value) return
  processing.value = true

  approvalForm.post(route('dividends.approve', selectedDividend.value.id), {
    preserveState: true,
    onFinish: () => {
      processing.value = false
      showApproveModal.value = false
    },
  })
}

const submitDistribution = () => {
  if (!selectedDividend.value) return
  processing.value = true

  router.post(route('dividends.distribute', selectedDividend.value.id), {}, {
    onFinish: () => {
      processing.value = false
      showDistributeModal.value = false
    },
  })
}

const tableHeaders = [
  'Year',
  'Total Profit',
  'Dividend Rate',
  'Total Dividends',
  'Status',
  'Dates',
  'Actions',
]

const cards = [
  {
    label: 'Total Dividends',
    value: props.stats?.total_dividends ?? 0,
    color: 'bg-slate-900',
    icon: 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z',
  },
  {
    label: 'Total Distributed',
    value: `KSh ${formatCurrency(props.stats?.total_distributed)}`,
    color: 'bg-orange-500',
    icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 9.586l3.293-3.293a1 1 0 111.414 1.414z',
  },
  {
    label: 'Pending Approval',
    value: props.stats?.pending_approval ?? 0,
    color: 'bg-yellow-500',
    icon: 'M10 2a8 8 0 100 16 8 8 0 000-16zm1 9H9V5a1 1 0 112 0v6z',
  },
  {
    label: 'Ready to Distribute',
    value: props.stats?.approved_pending_distribution ?? 0,
    color: 'bg-blue-600',
    icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 9.586l3.293-3.293a1 1 0 111.414 1.414z',
  },
]
</script>

<style scoped>

.filter-input {
  border-radius: 1rem; /* rounded-2xl */
  border: 1px solid #e2e8f0; /* slate-200 */
  background-color: #ffffff;
  padding: 0.75rem 1rem; /* py-3 px-4 */
  font-size: 0.875rem; /* text-sm */
  color: #0f172a; /* slate-900 */
  outline: none;
  transition: all 0.2s ease;
}

/* Dark mode */
.dark .filter-input {
  border-color: #334155; /* slate-700 */
  background-color: #1e293b; /* slate-800 */
  color: #ffffff;
}

/* Focus state */
.filter-input:focus {
  border-color: #3b82f6; /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

button:hover {
  cursor: pointer;
}
</style>