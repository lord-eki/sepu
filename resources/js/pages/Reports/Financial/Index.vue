<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const breadcrumbs = [
  { title: 'Reports', href: '#' },
  { title: 'Financial Reports' },
]
</script>

<template>
  <Head title="Financial Reports" />

  <AppLayout :breadcrumbs="breadcrumbs" title="Financial Reports">
    <div class="report-grid m-5">

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
/* =========================
   GRID LAYOUT
========================= */
.report-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 2rem;
}

/* Tablet & Desktop */
@media (min-width: 640px) {
  .report-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* =========================
   CARD STYLING
========================= */
.report-card {
  background: linear-gradient(135deg, #ffffff, #f8fafc);
  border-radius: 1.25rem;
  padding: 2.25rem;
  min-height: 180px;

  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.75rem;

  box-shadow:
    0 10px 25px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);

  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease;

  text-decoration: none;
  cursor: pointer;
  color: inherit;
}

/* Hover effect */
.report-card:hover {
  transform: translateY(-6px);
  box-shadow:
    0 20px 40px rgba(0, 0, 0, 0.15);
}

/* =========================
   DARK MODE
========================= */
.dark .report-card {
  background: linear-gradient(135deg, #0f172a, #1e293b);
  box-shadow:
    0 10px 25px rgba(0, 0, 0, 0.6);
}

/* =========================
   TEXT STYLING
========================= */
.report-card h3 {
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: -0.015em;
  color: #1e3a8a;
  margin: 0;
}

.dark .report-card h3 {
  color: #fb923c;
}

.report-card p {
  font-size: 0.95rem;
  line-height: 1.5;
  color: #64748b;
  margin: 0;
}

.dark .report-card p {
  color: #cbd5e1;
}
</style>
