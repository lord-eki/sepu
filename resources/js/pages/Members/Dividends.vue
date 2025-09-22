<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Head } from "@inertiajs/vue3"
import { computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { DollarSign, Calendar, CheckCircle } from "lucide-vue-next"

const props = defineProps<{
  member: any
  dividends: any[]
}>()

// Smart stats
const totalDividends = computed(() => props.dividends.length)
const totalAmount = computed(() =>
  props.dividends.reduce((sum, d) => sum + Number(d.amount || 0), 0)
)
const formattedTotalAmount = computed(() =>
  Number(totalAmount.value).toLocaleString()
)
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/my-dividends' }]">
    <div class="space-y-10 p-6 bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">

      <Head title="Dividends" />

      <!-- Header -->
      <div class="bg-blue-50 shadow-sm rounded-xl px-6 py-5 flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-700">
          Dividends
        </h1>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card
          class="rounded-2xl shadow-md hover:shadow-xl transition bg-gradient-to-tr from-indigo-50 to-white border border-indigo-100">
          <CardHeader class="flex items-center gap-2">
            <DollarSign class="h-5 w-5 max-sm:text-sm text-indigo-600" />
            <CardTitle class="text-gray-700">Total Dividends</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg sm:text-xl font-medium text-indigo-700">{{ totalDividends }}</p>
          </CardContent>
        </Card>

        <Card
          class="rounded-2xl shadow-md hover:shadow-xl transition bg-gradient-to-tr from-green-50 to-white border border-green-100">
          <CardHeader class="flex items-center gap-2">
            <Calendar class="h-5 w-5 max-sm:text-sm text-green-600" />
            <CardTitle class="text-gray-800">Total Amount</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg sm:text-xl font-medium text-green-700">KES {{ formattedTotalAmount }}</p>
          </CardContent>
        </Card>

        <Card
          class="rounded-2xl shadow-md hover:shadow-xl transition bg-gradient-to-tr from-blue-50 to-white border border-blue-100">
          <CardHeader class="flex items-center gap-2">
            <CheckCircle class="h-5 w-5 max-sm:text-sm text-blue-600" />
            <CardTitle class="text-gray-800">Status</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg sm:text-xl font-medium"
              :class="props.dividends.length ? 'text-blue-700' : 'text-gray-500'">
              {{ props.dividends.length ? "Paid Dividends" : "No Records" }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Dividends Table -->
      <div class="bg-white shadow-lg rounded-2xl overflow-x-auto border border-gray-100">
        <table class="min-w-full">
          <thead class="bg-indigo-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Year</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Declared</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Amount</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="d in props.dividends" :key="d.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ d.dividend?.year || "-" }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ d.dividend?.declared_at ? new Date(d.dividend.declared_at).toLocaleDateString() : "-" }}
              </td>
              <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                KES {{ Number(d.amount).toLocaleString() }}
              </td>
              <td class="px-6 py-4 text-sm">
                <span :class="{
    'px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800': d.status === 'paid',
    'px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800': d.status === 'pending',
    'px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800': d.status === 'rejected',
    'px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600': !d.status
  }">
                  {{ d.status || 'Unknown' }}
                </span>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!props.dividends.length">
              <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                <div class="flex flex-col items-center">
                  <div class="w-16 sm:w-20 h-16 sm:h-20 flex items-center justify-center rounded-full bg-gray-100 mb-3">
                    <svg class="w-8 sm:w-10 h-8 sm:h-10 text-gray-400" fill="none" stroke="currentColor"
                      stroke-width="2" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" fill="none" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                    </svg>
                  </div>
                  <p class="text-base font-medium">No dividends found</p>
                  <p class="text-sm text-gray-400 mt-1">Records will appear here once dividends are declared.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
