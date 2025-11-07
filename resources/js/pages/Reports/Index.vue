<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'

// Lucide Icons
import { Landmark, Users, Banknote, Receipt, FileBadge, Settings } from 'lucide-vue-next'

const breadcrumbs = [{ title: 'Reports' }]

const reportCards = ref([
  {
    title: 'Financial Reports',
    description: 'Balance sheet, income statement, cash flow, and trial balance.',
    icon: Landmark,
    route: route('reports.financial.balance-sheet'),
    color: 'from-blue-500 to-indigo-600'
  },
  {
    title: 'Member Reports',
    description: 'Member register, shares, savings, and loans.',
    icon: Users,
    route: route('reports.members.register'),
    color: 'from-emerald-500 to-green-600'
  },
  {
    title: 'Loan Reports',
    description: 'Portfolio, arrears, disbursement, and collections.',
    icon: Banknote,
    route: route('reports.loans.portfolio'),
    color: 'from-purple-500 to-indigo-600'
  },
  {
    title: 'Transaction Reports',
    description: 'Daily, monthly and annual transactions.',
    icon: Receipt,
    route: route('reports.transactions.daily'),
    color: 'from-orange-500 to-red-600'
  },
  {
    title: 'Regulatory Reports',
    description: 'Compliance and statutory reporting.',
    icon: FileBadge,
    route: route('reports.regulatory.statutory'),
    color: 'from-pink-500 to-rose-600'
  },
  {
    title: 'Custom Reports',
    description: 'Build and export custom reports based on filters.',
    icon: Settings,
    route: route('reports.custom.builder'),
    color: 'from-sky-500 to-cyan-600'
  }
])
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Reports" />

    <div class="p-6 space-y-8">
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800">Reports</h1>
      <p class="text-gray-600">
        Access and generate detailed reports across financial, member, loan and transaction data.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="card in reportCards"
          :key="card.title"
          class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition p-6 border border-gray-100"
        >
          <div
            class="w-14 h-14 flex items-center justify-center rounded-xl bg-gradient-to-br text-white mb-4"
            :class="card.color"
          >
            <component :is="card.icon" class="w-8 h-8" />
          </div>

          <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ card.title }}</h2>
          <p class="text-gray-500 mb-4">{{ card.description }}</p>

          <Link
            :href="card.route"
            class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium"
          >
            View Report
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
