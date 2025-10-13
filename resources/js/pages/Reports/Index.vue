<template>
  <AppLayout :breadcrumbs="[{ title: 'Reports' }]">
    <Head title="Reports Overview" />

    <!-- Page Header -->
    <div
      class="px-6 pt-4 pb-6 border-b border-gray-200 flex justify-between items-start sm:items-center bg-white rounded-t-2xl shadow"
    >
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#0a2342]">Reports</h1>
        <p class="text-sm text-gray-500">
          View and manage all financial, regulatory, and custom reports
        </p>
      </div>
     <!-- Export Dropdown -->
<div class="relative inline-block text-left">
  <button
    @click="showExportDropdown = !showExportDropdown"
    class="flex items-center gap-2 px-4 py-2 bg-[#0a2342] text-white rounded-lg shadow-sm hover:bg-blue-900 focus:outline-none"
  >
    Export
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
    </svg>
  </button>

  <!-- Dropdown -->
  <div
    v-if="showExportDropdown"
    class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
  >
    <button
      @click="exportReport('pdf')"
      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
    >
      Export as PDF
    </button>
    <button
      @click="exportReport('excel')"
      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
    >
      Export as Excel
    </button>
  </div>
</div>

    </div>

    <!-- Tabs -->
    <div class="bg-gray-50 border-b border-gray-200 px-6">
      <nav class="flex space-x-4">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            'px-3 py-2 text-sm font-semibold rounded-t-lg transition-all',
            activeTab === tab.value
              ? 'bg-white text-blue-900 border-b-2 border-orange-500'
              : 'text-gray-500 hover:text-blue-800',
          ]"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Content Area -->
    <div class="p-6 bg-white rounded-b-2xl shadow-md min-h-[70vh]">
      <!-- FINANCIAL REPORTS -->
      <div v-if="activeTab === 'financial'">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">
          Financial Reports
        </h2>

        <div class="grid md:grid-cols-2 gap-6">
          <ReportCard
            title="Balance Sheet"
            description="Assets, liabilities, and equity overview"
            color="blue"
            @generate="generateReport('balance-sheet')"
          />
          <ReportCard
            title="Income Statement"
            description="Revenue and expenses summary"
            color="orange"
            @generate="generateReport('income-statement')"
          />
          <ReportCard
            title="Cash Flow Statement"
            description="Cash inflows and outflows summary"
            color="teal"
            @generate="generateReport('cash-flow')"
          />
          <ReportCard
            title="Trial Balance"
            description="Debit and credit account summary"
            color="purple"
            @generate="generateReport('trial-balance')"
          />
        </div>

        <div v-if="generatedReport" class="mt-8">
          <h3 class="text-lg font-semibold text-blue-900 mb-2">
            {{ generatedReport.name }}
          </h3>
          <table class="w-full text-sm border rounded-lg overflow-hidden">
            <thead class="bg-blue-900 text-white">
              <tr>
                <th
                  v-for="header in generatedReport.headers"
                  :key="header"
                  class="px-4 py-2 text-left"
                >
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, idx) in generatedReport.data"
                :key="idx"
                class="border-b hover:bg-gray-50"
              >
                <td v-for="val in row" :key="val" class="px-4 py-2">
                  {{ val }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- REGULATORY REPORTS -->
      <div v-else-if="activeTab === 'regulatory'">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">
          Regulatory Reports
        </h2>
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl">
          <p class="text-sm text-gray-700 mb-2">
            Generate SACCO regulatory compliance and statutory reports for
            submission.
          </p>
          <div class="flex flex-wrap gap-4 mt-4">
            <button
              class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-md font-medium shadow"
              @click="generateReport('statutory')"
            >
              Statutory Report
            </button>
            <button
              class="px-4 py-2 bg-blue-900 hover:bg-blue-950 text-white rounded-md font-medium shadow"
              @click="generateReport('compliance')"
            >
              Compliance Report
            </button>
          </div>
        </div>

        <div v-if="generatedReport" class="mt-8">
          <h3 class="text-lg font-semibold text-blue-900 mb-2">
            {{ generatedReport.name }}
          </h3>
          <table class="w-full text-sm border rounded-lg overflow-hidden">
            <thead class="bg-blue-900 text-white">
              <tr>
                <th
                  v-for="header in generatedReport.headers"
                  :key="header"
                  class="px-4 py-2 text-left"
                >
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, idx) in generatedReport.data"
                :key="idx"
                class="border-b hover:bg-gray-50"
              >
                <td v-for="val in row" :key="val" class="px-4 py-2">
                  {{ val }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- CUSTOM REPORT BUILDER -->
      <div v-else-if="activeTab === 'custom'">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">
          Custom Report Builder
        </h2>
        <div class="space-y-4">
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-700">Report Name</label>
              <input
                v-model="customReport.name"
                type="text"
                placeholder="Enter custom report name"
                class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-orange-500"
              />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Data Source</label>
              <select
                v-model="customReport.source"
                class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-orange-500"
              >
                <option disabled value="">Select source</option>
                <option
                  v-for="src in dataSources"
                  :key="src"
                  :value="src"
                >
                  {{ src }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Date Range</label>
            <div class="flex gap-2 mt-1">
              <input
                v-model="customReport.start"
                type="date"
                class="border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
              />
              <input
                v-model="customReport.end"
                type="date"
                class="border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500"
              />
            </div>
          </div>

          <div class="flex justify-end mt-4">
            <button
              @click="generateCustom"
              class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-md font-semibold shadow"
            >
              Generate Custom Report
            </button>
          </div>

          <div v-if="generatedReport" class="mt-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">
              {{ generatedReport.name }}
            </h3>
            <table class="w-full text-sm border rounded-lg overflow-hidden">
              <thead class="bg-blue-900 text-white">
                <tr>
                  <th
                    v-for="header in generatedReport.headers"
                    :key="header"
                    class="px-4 py-2 text-left"
                  >
                    {{ header }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, idx) in generatedReport.data"
                  :key="idx"
                  class="border-b hover:bg-gray-50"
                >
                  <td v-for="val in row" :key="val" class="px-4 py-2">
                    {{ val }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ReportCard from '@/components/ReportCard.vue'

const tabs = [
  { label: 'Financial Reports', value: 'financial' },
  { label: 'Regulatory Reports', value: 'regulatory' },
  { label: 'Custom Reports', value: 'custom' },
]
const activeTab = ref('financial')

const exportFormats = ['pdf', 'excel', 'csv']
const dataSources = ['Members', 'Accounts', 'Transactions', 'Loans', 'Loan Repayments', 'Dividends']

const customReport = ref({
  name: '',
  source: '',
  start: '',
  end: '',
})

const generatedReport = ref<any | null>(null)

const generateReport = (type: string) => {
  generatedReport.value = {
    name: `${type.replace('-', ' ').toUpperCase()} REPORT`,
    headers: ['ID', 'Name', 'Amount', 'Date'],
    data: [
      [1, 'Example A', '12,000', '2025-09-01'],
      [2, 'Example B', '9,500', '2025-09-12'],
      [3, 'Example C', '15,400', '2025-09-23'],
    ],
  }
}

const generateCustom = () => {
  generatedReport.value = {
    name: customReport.value.name || 'Custom Report',
    headers: ['Field', 'Value', 'Date'],
    data: [
      ['Deposit', '20,000', '2025-10-01'],
      ['Withdrawal', '5,000', '2025-10-02'],
    ],
  }
}

const showExportDropdown = ref(false)

const exportReport = (type) => {
  showExportDropdown.value = false
  if (type === 'pdf') {
    console.log('Exporting as PDF...')
    // your export PDF logic here
  } else if (type === 'excel') {
    console.log('Exporting as Excel...')
    // your export Excel logic here
  }
}

// close dropdown when clicking outside
const handleClickOutside = (e) => {
  const dropdown = document.querySelector('.relative.inline-block')
  if (dropdown && !dropdown.contains(e.target)) {
    showExportDropdown.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
})

</script>


<style scoped>
button:hover{
    cursor: pointer;
}
</style>
