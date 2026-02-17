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

const breadcrumbs = [
    { title: 'Reports', href: route('reports.index') },
    { title: 'Custom Report' },
]
</script>

<template>
  <Head title="Custom Reports Builder" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-7xl mx-5 mt-5 space-y-10">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            Custom Report Builder
          </h1>
          <p class="mt-1 text-gray-500 dark:text-gray-400">
            Build dynamic reports from multiple data sources
          </p>
        </div>

        <button
          @click="generateReport"
          :disabled="loading"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r 
                 from-blue-900 to-blue-600 text-white shadow-lg 
                 hover:shadow-xl transition disabled:opacity-50"
        >
          <Play class="w-4 h-4" />
          {{ loading ? 'Generating...' : 'Generate Report' }}
        </button>
      </div>

      <!-- Templates -->
      <div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-4">
          Templates
        </h2>

        <div class="grid md:grid-cols-4 gap-5">
          <div
            v-for="(template, key) in templates"
            :key="key"
            class="group p-6 rounded-2xl border bg-white dark:bg-gray-900
                   hover:shadow-xl transition-all duration-300 cursor-pointer"
            @click="selectedSources = template.data_sources"
          >
            <h3 class="font-semibold text-gray-900 dark:text-white">
              {{ template.name }}
            </h3>
            <p class="text-sm text-gray-500 mt-2">
              {{ template.description }}
            </p>

            <div class="mt-4 text-xs text-blue-900 font-medium 
                        opacity-0 group-hover:opacity-100 transition">
              Use Template →
            </div>
          </div>
        </div>
      </div>

      <!-- Builder Grid -->
      <div class="grid lg:grid-cols-12 gap-6">

        <!-- Data Sources -->
        <div class="lg:col-span-3 bg-white dark:bg-gray-900 
                    rounded-2xl shadow-sm border p-6 space-y-4">

          <h3 class="flex items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
            <Database class="w-4 h-4" />
            Data Sources
          </h3>

          <div class="space-y-3">
            <label
              v-for="(source, key) in data_sources"
              :key="key"
              class="flex items-center justify-between p-3 rounded-xl 
                     hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition"
            >
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :value="key"
                  v-model="selectedSources"
                  class="rounded text-blue-900"
                />
                <span class="text-sm font-medium">
                  {{ source.name }}
                </span>
              </div>
            </label>
          </div>
        </div>

        <!-- Fields -->
        <div class="lg:col-span-4 bg-white dark:bg-gray-900 
                    rounded-2xl shadow-sm border p-6">

          <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">
            Fields
          </h3>

          <div class="flex flex-wrap gap-2 max-h-80 overflow-y-auto">
            <button
              v-for="field in availableFields"
              :key="field"
              @click="toggleField(field)"
              :class="[
                'px-3 py-1.5 text-xs rounded-full border transition',
                selectedFields.includes(field)
                  ? 'bg-blue-900 text-white border-blue-900'
                  : 'bg-gray-50 dark:bg-gray-800 hover:bg-gray-100'
              ]"
            >
              {{ field }}
            </button>
          </div>
        </div>

        <!-- Settings -->
        <div class="lg:col-span-5 bg-white dark:bg-gray-900 
                    rounded-2xl shadow-sm border p-6 space-y-5">

          <h3 class="flex items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
            <Filter class="w-4 h-4" />
            Report Settings
          </h3>

          <input
            v-model="reportName"
            type="text"
            placeholder="Report Name"
            class="w-full px-4 py-2 rounded-xl border dark:bg-gray-800 
                   focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />

          <div class="grid grid-cols-2 gap-4">
            <select
              v-model="groupBy"
              class="px-4 py-2 rounded-xl border dark:bg-gray-800"
            >
              <option value="">No Grouping</option>
              <option v-for="field in selectedFields" :key="field" :value="field">
                {{ field }}
              </option>
            </select>

            <select
              v-model="sortBy"
              class="px-4 py-2 rounded-xl border dark:bg-gray-800"
            >
              <option value="">No Sorting</option>
              <option v-for="field in selectedFields" :key="field" :value="field">
                {{ field }}
              </option>
            </select>
          </div>

          <select
            v-model="sortDirection"
            class="w-full px-4 py-2 rounded-xl border dark:bg-gray-800"
          >
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>

          <label class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
            <input type="checkbox" v-model="saveReport" class="rounded text-blue-900" />
            Save this report
          </label>
        </div>

      </div>

      <!-- Results -->
      <div v-if="results.length"
           class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border overflow-hidden">

        <div class="px-6 py-4 border-b font-semibold">
          Results ({{ results.length }} rows)
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th
                  v-for="(value, key) in results[0]"
                  :key="key"
                  class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300"
                >
                  {{ key }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, index) in results"
                :key="index"
                class="border-t dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
              >
                <td
                  v-for="(value, key) in row"
                  :key="key"
                  class="px-6 py-3"
                >
                  {{ value }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
