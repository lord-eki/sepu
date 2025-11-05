<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  members: Array, // [{id, name, membership_no, county, status, joined_at}]
  summary: Object, // { total, active, inactive }
  filters: Object,
})

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Member Reports', href: route('reports.members.register') },
  { title: 'Member Register' },
]

const filter = ref({
  county: props.filters?.county || '',
  status: props.filters?.status || '',
})

const exportReport = (format: string) => {
  router.visit(route('reports.members.register'), {
    method: 'get',
    data: { ...filter.value, export: format },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Member Register" />
    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center border-b pb-3">
        <h1 class="text-2xl font-bold text-gray-800">Member Register</h1>

        <div class="flex gap-2">
          <button @click="exportReport('csv')" class="px-4 py-2 bg-blue-500 text-white rounded">CSV</button>
          <button @click="exportReport('pdf')" class="px-4 py-2 bg-orange-500 text-white rounded">PDF</button>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 items-end">
        <div>
          <label class="text-sm">County</label>
          <input v-model="filter.county" class="border rounded px-3 py-2" placeholder="e.g. Machakos" />
        </div>
        <div>
          <label class="text-sm">Status</label>
          <select v-model="filter.status" class="border rounded px-3 py-2">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <button
          @click="router.visit(route('reports.members.register'), { method: 'get', data: filter.value })"
          class="px-4 py-2 bg-indigo-600 text-white rounded"
        >
          Filter
        </button>
      </div>

      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm text-gray-700 divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">#</th>
              <th class="px-4 py-2 text-left">Member Name</th>
              <th class="px-4 py-2">Membership No</th>
              <th class="px-4 py-2">County</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Joined</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(m, i) in props.members" :key="m.id">
              <td class="px-4 py-2">{{ i + 1 }}</td>
              <td class="px-4 py-2">{{ m.name }}</td>
              <td class="px-4 py-2 text-center">{{ m.membership_no }}</td>
              <td class="px-4 py-2 text-center">{{ m.county }}</td>
              <td>
                <span
                  class="px-2 py-1 text-xs rounded font-medium"
                  :class="m.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                >
                  {{ m.status }}
                </span>
              </td>
              <td class="px-4 py-2 text-center">{{ m.joined_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bg-gray-50 p-4 rounded-lg border text-sm">
        Total Members: <strong>{{ props.summary.total }}</strong> |
        Active: <strong class="text-green-600">{{ props.summary.active }}</strong> |
        Inactive: <strong class="text-red-600">{{ props.summary.inactive }}</strong>
      </div>
    </div>
  </AppLayout>
</template>
