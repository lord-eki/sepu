<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  ShieldCheck,
  AlertTriangle,
  CheckCircle2,
  XCircle,
  Calendar,
  Filter,
  TrendingUp,
} from 'lucide-vue-next'

const props = defineProps<{
  compliance: Record<string, any>
  compliance_score: number
  start_date: string
  end_date: string
}>()

const breadcrumbs = [
  { title: 'Regulatory Reports', href: route('reports.regulatoryReport.index') },
  { title: 'Compliance Reports' },
]

const submitFilter = (e: Event) => {
  e.preventDefault()

  const form = e.target as HTMLFormElement
  const data = new FormData(form)

  router.get(route('reports.regulatory.compliance'), {
    start_date: data.get('start_date'),
    end_date: data.get('end_date'),
  }, {
    preserveState: true,
    replace: true,
  })
}

const getStatusIcon = (value: boolean | number) => {
  if (value === true || value >= 80) return CheckCircle2
  if (value === false || value < 50) return XCircle
  return AlertTriangle
}

const getStatusColor = (value: boolean | number) => {
  if (value === true || value >= 80)
    return 'text-green-600 bg-green-50 dark:bg-green-900/20'

  if (value === false || value < 50)
    return 'text-red-600 bg-red-50 dark:bg-red-900/20'

  return 'text-orange-600 bg-orange-50 dark:bg-orange-900/20'
}

const formatValue = (value: any) => {

  if (value === null || value === undefined)
    return '-'

  // boolean
  if (typeof value === 'boolean')
    return value ? 'Compliant' : 'Non-Compliant'

  // number
  if (typeof value === 'number')
    return `${value}%`

  // array
  if (Array.isArray(value)) {

    if (value.length === 0)
      return 'No issues'

    return `${value.length} item(s)`
  }

  // object
  if (typeof value === 'object') {

    const keys = Object.keys(value)

    if (keys.length === 0)
      return 'No issues'

    return `${keys.length} record(s)`
  }

  // string
  return value
}


const scoreColor = () => {
  if (props.compliance_score >= 80)
    return 'from-green-600 to-emerald-500'

  if (props.compliance_score >= 50)
    return 'from-orange-500 to-amber-500'

  return 'from-red-600 to-rose-500'
}
</script>

<template>

  <Head title="Compliance Reports" />

  <AppLayout title="Compliance Reports" :breadcrumbs="breadcrumbs">

    <div class="space-y-6 mx-5 mt-5">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Compliance Reports
        </h1>

        <p class="text-sm text-gray-500 dark:text-gray-400">
          Regulatory compliance monitoring, audit readiness, and risk assessment
        </p>
      </div>


      <!-- Filters -->
      <form @submit="submitFilter"
        class="flex flex-col md:flex-row gap-4 items-end bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">

        <div class="flex-1">
          <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
            Start Date
          </label>

          <input type="date" name="start_date" :value="start_date"
            class="mt-1 w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" />
        </div>

        <div class="flex-1">
          <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
            End Date
          </label>

          <input type="date" name="end_date" :value="end_date"
            class="mt-1 w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" />
        </div>

        <button type="submit"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-medium hover:opacity-90 transition">
          <Filter class="w-4 h-4" />
          Apply Filter
        </button>

      </form>


      <!-- Compliance Score Card -->
      <div class="rounded-2xl p-6 text-white shadow-md bg-gradient-to-r" :class="scoreColor()">

        <div class="flex items-center justify-between">

          <div class="flex items-center gap-4">

            <div class="p-3 bg-white/20 rounded-xl">
              <ShieldCheck class="w-8 h-8" />
            </div>

            <div>
              <p class="text-sm opacity-90">
                Overall Compliance Score
              </p>

              <p class="text-3xl font-bold">
                {{ compliance_score }}%
              </p>
            </div>

          </div>


          <div class="hidden md:flex items-center gap-2 text-sm opacity-90">
            <TrendingUp class="w-4 h-4" />
            Regulatory Health Indicator
          </div>

        </div>

      </div>


      <!-- Compliance Items Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <div v-for="(value, key) in compliance" :key="key"
          class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm hover:shadow-md transition">

          <!-- Header -->
          <div class="flex items-center gap-3">

            <div class="p-2 rounded-lg" :class="getStatusColor(value)">
              <component :is="getStatusIcon(value)" class="w-5 h-5" />
            </div>

            <div>

              <h3 class="text-sm font-semibold text-gray-900 dark:text-white capitalize">
                {{ key.replaceAll('_', ' ') }}
              </h3>

              <p class="text-xs text-gray-500 dark:text-gray-400">
                Compliance indicator
              </p>

            </div>

          </div>


          <!-- Value -->
          <div class="mt-4">

            <div class="text-base text-gray-900 dark:text-white">
              {{ formatValue(value) }}
            </div>

          </div>

        </div>

      </div>


      <!-- Footer -->
      <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <Calendar class="w-4 h-4" />
        Showing compliance data from
        <span class="font-medium">
          {{ start_date }}
        </span>
        to
        <span class="font-medium">
          {{ end_date }}
        </span>
      </div>


    </div>

  </AppLayout>

</template>
