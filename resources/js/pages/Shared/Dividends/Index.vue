<template>
  <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/dividends' }]">

    <Head title="Dividends" />

    <!-- Flash -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition name="fade-slide">
        <div v-if="flashMessage" :class="[
    'mb-4 rounded-md p-3 shadow flex items-center gap-3',
    flashType === 'success'
      ? 'bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300'
      : 'bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300'
  ]">
          <svg v-if="flashType === 'success'" class="h-5 w-5 text-green-600 dark:text-green-300" viewBox="0 0 20 20"
            fill="currentColor">
            <path
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
          </svg>
          <svg v-else class="h-5 w-5 text-red-600 dark:text-red-300" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
          </svg>
          <p class="text-sm">{{ flashMessage }}</p>
          <button type="button"
            class="ml-auto text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
            @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>

    <!-- Header -->
    <div class="bg-header text-white py-8 mx-4 px-4 sm:px-8 rounded-3xl shadow-lg dark:bg-[#0a122d]">
      <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Dividends</h2>
      <p class="mt-2 text-sm sm:text-base opacity-90">
        Track, approve, and distribute member dividends with ease.
      </p>
    </div>

    <!-- Content -->
    <div class="py-10">
      <div class="max-w-7xl mx-auto space-y-10">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="card in cards" :key="card.label"
            class="bg-white dark:bg-gray-900/50 dark:border-gray-700/60 backdrop-blur-sm rounded-2xl shadow-md border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-4">
            <div class="flex items-center space-x-4">
              <div :class="[card.color, 'w-10 h-10 rounded-xl flex items-center justify-center shadow-md']">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path :d="card.icon" />
                </svg>
              </div>
              <div>
                <p class="text-sm sm:text-base text-gray-500 dark:text-gray-300">
                  {{ card.label }}
                </p>
                <p class="text-lg sm:text-xl font-semibold text-[#0a2342] dark:text-white">
                  {{ card.value }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters + Actions -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-md p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
              <select v-model="filters.status" @change="applyFilters"
                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm px-3 py-2 shadow-sm focus:border-[#0a2342] focus:ring focus:ring-blue-100 dark:focus:ring-blue-800">
                <option value="all">All Status</option>
                <option value="calculated">Calculated</option>
                <option value="approved">Approved</option>
                <option value="distributed">Distributed</option>
              </select>

              <select v-model="filters.year" @change="applyFilters"
                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm px-3 py-2 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-100 dark:focus:ring-orange-800">
                <option value="">All Years</option>
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
              <Link :href="route('dividends.analytics.history')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 text-sm font-medium hover:bg-orange-200 dark:hover:bg-orange-800 transition">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 13l4-4 3 3 5-6a1 1 0 111.6 1.2l-6 7a1 1 0 01-1.4.1L8 11l-3.3 3.3a1 1 0 11-1.4-1.4z" />
              </svg>
              Analytics
              </Link>

              <Link :href="route('dividends.create')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0a2342] dark:bg-blue-900 text-white text-sm font-medium hover:bg-blue-900 transition">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
              </svg>
              Calculate New Dividend
              </Link>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div
          class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md overflow-hidden hover:shadow-lg transition-all duration-300">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead class="bg-blue-50 dark:bg-gray-800">
                <tr>
                  <th v-for="head in tableHeaders" :key="head"
                    class="px-6 py-3 text-left font-semibold text-[#0a2342] dark:text-gray-200 uppercase tracking-wide text-xs">
                    {{ head }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="dividend in dividends.data" :key="dividend.id"
                  class="hover:bg-orange-50 dark:hover:bg-gray-800 transition duration-200">
                  <td class="px-6 py-4 font-medium text-blue-900 dark:text-blue-300">
                    {{ dividend.dividend_year }}
                  </td>
                  <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                    KSh {{ formatCurrency(dividend.total_profit) }}
                  </td>
                  <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                    {{ dividend.dividend_rate }}%
                  </td>
                  <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                    KSh {{ formatCurrency(dividend.total_dividends) }}
                  </td>
                  <td class="px-6 py-4">
                    <span :class="[
    getStatusClass(dividend.status),
    'px-2 py-1 text-xs font-semibold rounded-full'
  ]">
                      {{ formatStatus(dividend.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                    <div>
                      Calculated: {{ formatDate(dividend.calculation_date) }}
                    </div>
                    <div v-if="dividend.approval_date">
                      Approved: {{ formatDate(dividend.approval_date) }}
                    </div>
                    <div v-if="dividend.distribution_date">
                      Distributed: {{ formatDate(dividend.distribution_date) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-3">
                      <Link :href="route('dividends.show', dividend.id)"
                        class="text-blue-700 dark:text-blue-400 hover:underline">View</Link>

                      <Link v-if="dividend.status === 'calculated'" :href="route('dividends.edit', dividend.id)"
                        class="text-orange-600 dark:text-orange-300 hover:underline">Edit</Link>

                      <button v-if="dividend.status === 'calculated'" @click="approveDividend(dividend)"
                        class="text-green-600 dark:text-green-300 hover:underline">
                        Approve
                      </button>

                      <button v-if="dividend.status === 'approved'" @click="distributeDividend(dividend)"
                        class="text-purple-600 dark:text-purple-300 hover:underline">
                        Distribute
                      </button>

                      <button v-if="dividend.status === 'calculated'" @click="confirmDelete(dividend)"
                        class="text-red-600 dark:text-red-300 hover:underline">
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="dividends.links"
            class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <Pagination :data="dividends" />
          </div>
        </div>
      </div>
    </div>

    <!-- Full-page loader -->
    <div v-if="processing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70">
      <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white dark:bg-gray-900 max-sm:mx-3 rounded-2xl shadow-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Delete Dividend</h3>
        <p class="mb-6 dark:text-gray-300">
          Are you sure you want to delete the dividend for
          {{ selectedDividend?.dividend_year }}? This action cannot be undone.
        </p>

        <div class="flex justify-end gap-3">
          <button @click="showDeleteModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
            Cancel
          </button>

          <button @click="deleteDividend" :disabled="processing"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center">
            Delete
            <span v-if="processing"
              class="ml-2 animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
          </button>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white dark:bg-gray-900 max-sm:mx-3 rounded-2xl shadow-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Approve Dividend</h3>
        <div class="mb-4 dark:text-gray-300">
          Are you sure you want to approve the dividend for
          {{ selectedDividend?.dividend_year }}?
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="approval_notes">Approval Notes
            (Optional)</label>
          <textarea id="approval_notes" v-model="approvalForm.approval_notes" rows="3"
            class="mt-1 block w-full border border-gray-300 dark:border-gray-700 rounded-lg p-2 dark:bg-gray-800 dark:text-gray-200"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="showApproveModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
            Cancel
          </button>

          <button @click="submitApproval" :disabled="processing"
            class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 flex items-center">
            Approve
            <span v-if="processing"
              class="ml-2 animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
          </button>
        </div>
      </div>
    </div>

    <!-- Distribute Modal -->
    <div v-if="showDistributeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white dark:bg-gray-900 max-sm:mx-3 rounded-2xl shadow-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Distribute Dividend</h3>
        <p class="mb-6 dark:text-gray-300">
          Are you sure you want to distribute the dividend for
          {{ selectedDividend?.dividend_year }}? This will transfer dividend amounts to
          all eligible members' accounts.
        </p>

        <div class="flex justify-end gap-3">
          <button @click="showDistributeModal = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
            Cancel
          </button>

          <button @click="submitDistribution" :disabled="processing"
            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center">
            Distribute
            <span v-if="processing"
              class="ml-2 animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import { Link, router, useForm, Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import Pagination from "@/components/Pagination.vue";

const props = defineProps({
  dividends: Object,
  availableYears: Array,
  filters: Object,
  stats: Object,
});

/* flash */
const page = usePage();
const flashMessage = ref(null);
const flashType = ref("success");
const flashBox = ref(null);

watch(
  () => page.props,
  (p) => {
    if (p.flash?.success) {
      flashMessage.value = p.flash.success;
      flashType.value = "success";
    } else if (p.flash?.error) {
      flashMessage.value = p.flash.error;
      flashType.value = "error";
    } else if (p.errors?.error) {
      flashMessage.value = p.errors.error;
      flashType.value = "error";
    }

    if (flashMessage.value) {
      flashBox.value?.scrollIntoView?.({ behavior: "smooth", block: "start" });
      setTimeout(() => (flashMessage.value = null), 5000);
    }
  },
  { immediate: true, deep: true }
);

const showDeleteModal = ref(false);
const showApproveModal = ref(false);
const showDistributeModal = ref(false);
const selectedDividend = ref(null);
const processing = ref(false);

const filters = reactive({
  status: props.filters.status || "all",
  year: props.filters.year || "",
});

const approvalForm = useForm({
  approval_notes: "",
});

const formatCurrency = (amount) =>
  new Intl.NumberFormat("en-KE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0);

const formatDate = (date) =>
  date
    ? new Date(date).toLocaleDateString("en-GB", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    })
    : "N/A";

const formatStatus = (status) => status.charAt(0).toUpperCase() + status.slice(1);

const getStatusClass = (status) =>
({
  calculated:
    "bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300",
  approved: "bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300",
  distributed:
    "bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300",
}[status] || "bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300");

const applyFilters = () =>
  router.get(route("dividends.index"), filters, {
    preserveState: true,
    replace: true,
  });

const confirmDelete = (dividend) => {
  selectedDividend.value = dividend;
  showDeleteModal.value = true;
};

const deleteDividend = () => {
  processing.value = true;
  router.delete(route("dividends.destroy", selectedDividend.value.id), {
    onFinish: () => {
      processing.value = false;
      showDeleteModal.value = false;
      selectedDividend.value = null;
    },
  });
};

const approveDividend = (dividend) => {
  selectedDividend.value = dividend;
  approvalForm.reset();
  showApproveModal.value = true;
};

const distributeDividend = (dividend) => {
  selectedDividend.value = dividend;
  showDistributeModal.value = true;
};

const submitApproval = () => {
  if (!selectedDividend.value) return;
  processing.value = true;

  router.post(route("dividends.approve", selectedDividend.value.id), approvalForm, {
    preserveState: true,
    onFinish: () => {
      processing.value = false;
      showApproveModal.value = false;
    },
  });
};

const submitDistribution = () => {
  if (!selectedDividend.value) return;
  processing.value = true;

  router.post(
    route("dividends.distribute", selectedDividend.value.id),
    {},
    {
      onFinish: () => {
        processing.value = false;
        showDistributeModal.value = false;
      },
    }
  );
};

const tableHeaders = [
  "Year",
  "Total Profit",
  "Dividend Rate",
  "Total Dividends",
  "Status",
  "Dates",
  "Actions",
];

const cards = [
  {
    label: "Total Dividends",
    value: props.stats.total_dividends,
    color: "bg-[#0a2342]",
    icon: "M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z",
  },
  {
    label: "Total Distributed",
    value: "KSh " + formatCurrency(props.stats.total_distributed),
    color: "bg-orange-500",
    icon: "M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 9.586l3.293-3.293a1 1 0 111.414 1.414z",
  },
  {
    label: "Pending Approval",
    value: props.stats.pending_approval,
    color: "bg-yellow-500",
    icon: "M10 2a8 8 0 100 16 8 8 0 000-16zm1 9H9V5a1 1 0 112 0v6z",
  },
  {
    label: "Ready to Distribute",
    value: props.stats.approved_pending_distribution,
    color: "bg-blue-500",
    icon: "M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 9.586l3.293-3.293a1 1 0 111.414 1.414z",
  },
];
</script>


<style scoped>
.bg-header {
  background: linear-gradient(135deg, #043066 0%, #215bad 50%, #f97316 100%);
}

button:hover {
  cursor: pointer;
}
</style>