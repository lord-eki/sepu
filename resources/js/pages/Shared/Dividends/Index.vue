<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps<{
  dividends: any
  availableYears: number[]
  filters: {
    status?: string
    year?: number
  }
  stats: any
}>()

const filters = ref({
  status: props.filters.status || 'all',
  year: props.filters.year || ''
})

// Auto filter reload
watch(filters, () => {
  router.get(route('dividends.index'), filters.value, {
    preserveState: true,
    replace: true
  })
}, { deep: true })

const formatMoney = (val: number) =>
  new Intl.NumberFormat('en-KE').format(val || 0)
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/dividends' }]">
    <Head title="Dividends" />

    <div class="p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
            Dividend Management
          </h1>
          <p class="text-sm text-slate-500">
            Manage, calculate, and distribute member dividends
          </p>
        </div>

        <a
          :href="route('dividends.create')"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition"
        >
          + Create Dividend
        </a>
      </div>

      <!-- STATS -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow">
          <p class="text-xs text-slate-500">Total Dividends</p>
          <h2 class="text-xl font-bold">{{ stats.total_dividends }}</h2>
        </div>

        <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow">
          <p class="text-xs text-slate-500">Distributed</p>
          <h2 class="text-xl font-bold text-green-600">
            {{ formatMoney(stats.total_distributed) }}
          </h2>
        </div>

        <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow">
          <p class="text-xs text-slate-500">Pending Approval</p>
          <h2 class="text-xl font-bold text-yellow-600">
            {{ stats.pending_approval }}
          </h2>
        </div>

        <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow">
          <p class="text-xs text-slate-500">Ready to Distribute</p>
          <h2 class="text-xl font-bold text-blue-600">
            {{ stats.approved_pending_distribution }}
          </h2>
        </div>

      </div>

      <!-- FILTERS -->
      <div class="bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow flex flex-col md:flex-row gap-4">

        <select
          v-model="filters.status"
          class="w-full md:w-48 border rounded-lg px-3 py-2 text-sm dark:bg-slate-800"
        >
          <option value="all">All Status</option>
          <option value="calculated">Calculated</option>
          <option value="approved">Approved</option>
          <option value="distributed">Distributed</option>
        </select>

        <select
          v-model="filters.year"
          class="w-full md:w-48 border rounded-lg px-3 py-2 text-sm dark:bg-slate-800"
        >
          <option value="">All Years</option>
          <option v-for="y in availableYears" :key="y" :value="y">
            {{ y }}
          </option>
        </select>

      </div>

      <!-- TABLE -->
      <div class="bg-white dark:bg-slate-900 border rounded-2xl shadow overflow-x-auto">

        <table class="w-full text-sm">

          <thead class="bg-slate-50 dark:bg-slate-800 text-left">
            <tr>
              <th class="p-3">Year</th>
              <th class="p-3">Total Profit</th>
              <th class="p-3">Dividends</th>
              <th class="p-3">Rate</th>
              <th class="p-3">Status</th>
              <th class="p-3 text-right">Actions</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="d in dividends.data"
              :key="d.id"
              class="border-t hover:bg-slate-50 dark:hover:bg-slate-800 transition"
            >

              <td class="p-3 font-medium">
                {{ d.dividend_year }}
              </td>

              <td class="p-3">
                KES {{ formatMoney(d.total_profit) }}
              </td>

              <td class="p-3 font-semibold text-green-600">
                KES {{ formatMoney(d.total_dividends) }}
              </td>

              <td class="p-3">
                {{ d.dividend_rate }}%
              </td>

              <td class="p-3">
                <span
                  class="px-2 py-1 text-xs rounded-lg"
                  :class="{
                    'bg-yellow-100 text-yellow-700': d.status === 'calculated',
                    'bg-blue-100 text-blue-700': d.status === 'approved',
                    'bg-green-100 text-green-700': d.status === 'distributed'
                  }"
                >
                  {{ d.status }}
                </span>
              </td>

              <td class="p-3 text-right space-x-2">

                <a
                  :href="route('dividends.show', d.id)"
                  class="text-blue-600 hover:underline text-sm"
                >
                  View
                </a>

                <a
                  v-if="d.status === 'calculated'"
                  :href="route('dividends.edit', d.id)"
                  class="text-orange-600 hover:underline text-sm"
                >
                  Edit
                </a>

              </td>

            </tr>

          </tbody>

        </table>

        <!-- EMPTY STATE -->
        <div
          v-if="dividends.data.length === 0"
          class="p-10 text-center text-slate-500"
        >
          No dividends found.
        </div>

      </div>

    </div>
  </AppLayout>
</template>