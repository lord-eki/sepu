<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const breadcrumbs = [
  { title: 'Reports', href: '#' },
  { title: 'Financial Reports' },
]
</script>

<template>
  <Head title="Financial Reports" />

  <AppLayout :breadcrumbs="breadcrumbs" title="Financial Reports">
    <div class="report-grid">

      <Link :href="route('reports.financial.balance-sheet')" class="report-card">
        <h3>Balance Sheet</h3>
        <p>Assets, Liabilities & Equity</p>
      </Link>

      <Link :href="route('reports.financial.income-statement')" class="report-card">
        <h3>Income Statement</h3>
        <p>Revenue & Expenses</p>
      </Link>

      <Link :href="route('reports.financial.cash-flow')" class="report-card">
        <h3>Cash Flow</h3>
        <p>Operating, Investing & Financing</p>
      </Link>

      <Link :href="route('reports.financial.trial-balance')" class="report-card">
        <h3>Trial Balance</h3>
        <p>Debits & Credits</p>
      </Link>

    </div>
  </AppLayout>
</template>

<style scoped>
/* Grid layout */
.report-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1.5rem;
}

@media (min-width: 640px) {
  .report-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .report-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Card styling */
.report-card {
  background-color: #ffffff;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: box-shadow 0.2s ease;
  text-decoration: none;
  cursor: pointer;
  color: inherit;
}

.report-card:hover {
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

/* Dark mode */
.dark .report-card {
  background-color: #1e293b; /* slate-900 */
}

.report-card h3 {
  font-weight: 600;
  color: #1e3a8a; /* blue-900 */
  margin: 0 0 0.5rem 0;
}

.dark .report-card h3 {
  color: #f97316; /* orange-400 */
}

.report-card p {
  font-size: 0.875rem; /* 14px */
  color: #6b7280;       /* gray-500 */
  margin: 0;
}

.dark .report-card p {
  color: #d1d5db;       /* gray-300 */
}
</style>
