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
    <div class="space-y-10 p-6 bg-gradient-to-br from-white via-gray-50 to-gray-100 min-h-screen">

      <Head title="Dividends" />

      <!-- Header -->
      <div class="bg-blue-900 shadow-lg rounded-2xl px-6 py-5 flex items-center justify-between">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
            Dividends
          </h1>
          <p class="text-sm text-gray-200 mt-1">
            Track declared dividends, view your earnings history, and manage 
            distributions with ease.
          </p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Dividends -->
        <Card class="rounded-2xl shadow-md hover:shadow-xl transition bg-gradient-to-tr from-[#081642]/10 to-white border border-[#081642]/20">
          <CardHeader class="flex items-center gap-2">
            <DollarSign class="h-5 w-5 max-sm:text-sm text-[#081642]" />
            <CardTitle class="text-[#081642]">Total Dividends</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg sm:text-xl font-semibold text-[#081642]">{{ totalDividends }}</p>
          </CardContent>
        </Card>

        <!-- Total Amount -->
        <Card class="rounded-2xl shadow-md hover:shadow-xl transition bg-gradient-to-tr from-orange-50 to-white border border-orange-200">
          <CardHeader class="flex items-center gap-2">
            <Calendar class="h-5 w-5 max-sm:text-sm text-orange-600" />
            <CardTitle class="text-gray-800">Total Amount</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg sm:text-xl font-semibold text-orange-600">
              KES {{ formattedTotalAmount }}
            </p>
          </CardContent>
        </Card>

        <!-- Status -->
        <Card class="rounded-2xl shadow-md hover:shadow-xl transition bg-gradient-to-tr from-blue-50 to-white border border-blue-200">
          <CardHeader class="flex items-center gap-2">
            <CheckCircle class="h-5 w-5 max-sm:text-sm text-blue-700" />
            <CardTitle class="text-gray-800">Status</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg sm:text-xl font-semibold"
              :class="props.dividends.length ? 'text-[#081642]' : 'text-gray-500'">
              {{ props.dividends.length ? "Paid Dividends" : "No Records" }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Dividends Table -->
      <div class="bg-white shadow-lg rounded-2xl overflow-x-auto border border-gray-200">
        <table class="min-w-full">
          <thead class="bg-[#081642]/10 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold text-[#081642]">Year</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-[#081642]">Declared</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-[#081642]">Amount</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-[#081642]">Status</th>
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
