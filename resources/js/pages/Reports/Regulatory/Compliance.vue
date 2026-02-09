<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  ShieldCheck,
  AlertTriangle,
  CheckCircle,
  Calendar
} from 'lucide-vue-next'

const props = defineProps<{
  compliance: Record<string, any>
  compliance_score: number
  start_date: string
  end_date: string
}>()

const submitFilter = (e: Event) => {
  e.preventDefault()
  const form = e.target as HTMLFormElement
  const data = new FormData(form)

  router.get(route('reports.regulatory.compliance'), {
    start_date: data.get('start_date'),
    end_date: data.get('end_date'),
  })
}

const statusIcon = (value: boolean | number) => {
  if (value === true || value >= 80) return CheckCircle
  return AlertTriangle
}
</script>

<template>
  <Head title="Compliance Reports" />

  <AppLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Compliance Reports
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Regulatory compliance monitoring and risk assessment
        </p>
      </div>

      <!-- Filters -->
      <form
        @submit="submitFilter"
        class="flex flex-col sm:flex-row gap-4 items-end bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700"
      >
        <div class="flex-1">
          <label class="block text-sm font-medium mb-1">Start Date</label>
          <input
            type="date"
            name="start_date"
            :value="start_date"
            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
          />
        </div>

        <div class="flex-1">
          <label class="block text-sm font-medium mb-1">End Date</label>
          <input
            type="date"
            name="end_date"
            :value="end_date"
            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800"
          />
        </div>

        <button
          type="submit"
          class="px-5 py-2 rounded-lg bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-sm font-medium"
        >
          Apply Filter
        </button>
      </form>

      <!-- Compliance Score -->
      <div
        class="flex items-center gap-4 p-6 rounded-xl bg-gradient-to-r from-green-600 to-emerald-500 text-white"
      >
        <ShieldCheck class="w-10 h-10" />
        <div>
          <p class="text-sm opacity-90">Overall Compliance Score</p>
          <p class="text-3xl font-bold">{{ compliance_score }}%</p>
        </div>
      </div>

      <!-- Compliance Items -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div
          v-for="(value, key) in compliance"
          :key="key"
          class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
        >
          <div class="flex items-center gap-3">
            <component
              :is="statusIcon(value)"
              class="w-6 h-6"
              :class="[
                value === true || value >= 80
                  ? 'text-green-600'
                  : 'text-red-500'
              ]"
            />

            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 capitalize">
                {{ key.replaceAll('_', ' ') }}
              </h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Status overview
              </p>
            </div>
          </div>

          <div class="mt-4 text-sm text-gray-700 dark:text-gray-300">
            <span v-if="typeof value === 'boolean'">
              {{ value ? 'Compliant' : 'Non-compliant' }}
            </span>
            <span v-else>
              {{ value }}
            </span>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
        <Calendar class="w-4 h-4" />
        Data reflects compliance status within the selected reporting period.
      </div>

    </div>
  </AppLayout>
</template>
