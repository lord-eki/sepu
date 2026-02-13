<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'
import {
  Database,
  FileText,
  Save,
  Play,
  Trash2,
  Filter
} from 'lucide-vue-next'

const props = defineProps<{
  data_sources: Record<string, any>
  templates: Record<string, any>
  saved_reports: Array<any>
}>()

/* ----------------------------------------
   STATE
---------------------------------------- */

const reportName = ref('')
const selectedSources = ref<string[]>([])
const selectedFields = ref<string[]>([])
const groupBy = ref('')
const sortBy = ref('')
const sortDirection = ref('asc')
const saveReport = ref(false)
const results = ref<any[]>([])
const loading = ref(false)

/* ----------------------------------------
   COMPUTED
---------------------------------------- */

const availableFields = computed(() => {
  const fields: string[] = []
  selectedSources.value.forEach(source => {
    props.data_sources[source]?.fields?.forEach((f: string) => {
      fields.push(`${source}.${f}`)
    })
  })
  return fields
})

/* ----------------------------------------
   METHODS
---------------------------------------- */

const toggleSource = (key: string) => {
  if (selectedSources.value.includes(key)) {
    selectedSources.value = selectedSources.value.filter(s => s !== key)
  } else {
    selectedSources.value.push(key)
  }
}

const toggleField = (field: string) => {
  if (selectedFields.value.includes(field)) {
    selectedFields.value = selectedFields.value.filter(f => f !== field)
  } else {
    selectedFields.value.push(field)
  }
}

const generateReport = async () => {
  loading.value = true
  results.value = []

  try {
    const response = await axios.post(
      route('reports.custom.generate'),
      {
        report_name: reportName.value,
        data_sources: selectedSources.value,
        selected_fields: selectedFields.value,
        group_by: groupBy.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
        save_report: saveReport.value,
        format: 'table',
      }
    )

    if (response.data.success) {
      results.value = response.data.data
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head title="Custom Reports Builder" />

  <AppLayout>
    <div class="space-y-8">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Custom Report Builder
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Build flexible reports from multiple data sources
        </p>
      </div>

      <!-- Templates -->
      <div>
        <h2 class="text-lg font-semibold mb-3">Report Templates</h2>
        <div class="grid md:grid-cols-2 gap-4">
          <div
            v-for="(template, key) in templates"
            :key="key"
            class="p-5 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700"
          >
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
              {{ template.name }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ template.description }}
            </p>

            <button
              class="mt-3 text-sm text-blue-600 font-medium"
              @click="selectedSources = template.data_sources"
            >
              Use Template
            </button>
          </div>
        </div>
      </div>

      <!-- Builder Section -->
      <div class="grid lg:grid-cols-3 gap-6">

        <!-- Data Sources -->
        <div class="p-5 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700">
          <h3 class="font-semibold flex items-center gap-2 mb-4">
            <Database class="w-4 h-4" />
            Data Sources
          </h3>

          <div class="space-y-2">
            <div
              v-for="(source, key) in data_sources"
              :key="key"
              class="flex items-center gap-2"
            >
              <input
                type="checkbox"
                :value="key"
                v-model="selectedSources"
                class="rounded border-gray-300"
              />
              <span class="text-sm">{{ source.name }}</span>
            </div>
          </div>
        </div>

        <!-- Fields -->
        <div class="p-5 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700">
          <h3 class="font-semibold mb-4">Fields</h3>

          <div class="max-h-64 overflow-y-auto space-y-2">
            <div
              v-for="field in availableFields"
              :key="field"
              class="flex items-center gap-2"
            >
              <input
                type="checkbox"
                :value="field"
                v-model="selectedFields"
                class="rounded border-gray-300"
              />
              <span class="text-sm">{{ field }}</span>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div class="p-5 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700">
          <h3 class="font-semibold mb-4 flex items-center gap-2">
            <Filter class="w-4 h-4" />
            Report Settings
          </h3>

          <div class="space-y-4">

            <input
              v-model="reportName"
              type="text"
              placeholder="Report Name"
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
            />

            <select
              v-model="groupBy"
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
            >
              <option value="">No Grouping</option>
              <option
                v-for="field in selectedFields"
                :key="field"
                :value="field"
              >
                {{ field }}
              </option>
            </select>

            <select
              v-model="sortBy"
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
            >
              <option value="">No Sorting</option>
              <option
                v-for="field in selectedFields"
                :key="field"
                :value="field"
              >
                {{ field }}
              </option>
            </select>

            <select
              v-model="sortDirection"
              class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
            >
              <option value="asc">Ascending</option>
              <option value="desc">Descending</option>
            </select>

            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" v-model="saveReport" />
              Save this report
            </label>

            <button
              @click="generateReport"
              :disabled="loading"
              class="w-full flex items-center justify-center gap-2 py-2 rounded-lg bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900"
            >
              <Play class="w-4 h-4" />
              {{ loading ? 'Generating...' : 'Generate Report' }}
            </button>

          </div>
        </div>

      </div>

      <!-- Results Table -->
      <div v-if="results.length" class="overflow-x-auto border rounded-xl">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
              <th
                v-for="(value, key) in results[0]"
                :key="key"
                class="px-4 py-2 text-left"
              >
                {{ key }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in results"
              :key="index"
              class="border-t dark:border-gray-700"
            >
              <td
                v-for="(value, key) in row"
                :key="key"
                class="px-4 py-2"
              >
                {{ value }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Saved Reports -->
      <div v-if="saved_reports.length">
        <h2 class="text-lg font-semibold mb-3">Saved Reports</h2>
        <div class="space-y-3">
          <div
            v-for="report in saved_reports"
            :key="report.id"
            class="p-4 border rounded-lg flex justify-between items-center"
          >
            <div>
              <p class="font-medium">{{ report.name }}</p>
              <p class="text-xs text-gray-500">
                Created {{ report.created_at }}
              </p>
            </div>
            <button class="text-red-500">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
