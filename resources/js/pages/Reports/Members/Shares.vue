<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  data: Array, // [{ member_name, member_no, total_shares }]
  totals: Object, // { total_shares, average_shares }
})
const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Member Reports', href: route('reports.members.shares') },
  { title: 'Member Shares' },
]
const exportReport = (format: string) => {
  router.visit(route('reports.members.shares'), { method: 'get', data: { export: format } })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Member Shares" />

    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center border-b pb-3">
        <h1 class="text-2xl font-bold">Member Shares</h1>
        <div class="flex gap-2">
          <button @click="exportReport('csv')" class="px-4 py-2 bg-blue-500 text-white rounded">CSV</button>
          <button @click="exportReport('pdf')" class="px-4 py-2 bg-orange-500 text-white rounded">PDF</button>
        </div>
      </div>

      <div class="bg-white shadow rounded-xl overflow-x-auto">
        <table class="min-w-full text-sm text-gray-700 divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">#</th>
              <th class="px-4 py-2 text-left">Member</th>
              <th class="px-4 py-2">Member No</th>
              <th class="px-4 py-2 text-right">Total Shares (KES)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(item, i) in props.data" :key="i">
              <td class="px-4 py-2">{{ i + 1 }}</td>
              <td class="px-4 py-2">{{ item.member_name }}</td>
              <td class="px-4 py-2 text-center">{{ item.member_no }}</td>
              <td class="px-4 py-2 text-right font-semibold">{{ item.total_shares.toLocaleString() }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50 font-medium">
            <tr>
              <td colspan="3" class="px-4 py-2 text-right">Total Shares:</td>
              <td class="px-4 py-2 text-right">{{ props.totals.total_shares.toLocaleString() }}</td>
            </tr>
            <tr>
              <td colspan="3" class="px-4 py-2 text-right">Average per Member:</td>
              <td class="px-4 py-2 text-right">{{ props.totals.average_shares.toLocaleString() }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
