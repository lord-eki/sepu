<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loan Disbursement Schedule', href: '#' }
    ]"
  >
    <div class="page-container">
      <!-- Page Header -->
      <div class="page-header">
        <h1>Loan Disbursement Schedule</h1>
        <p>Scheduled and completed loan disbursements</p>
      </div>

      <!-- Card -->
      <div class="card">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Loan ID</th>
              <th>Member Name</th>
              <th>Amount</th>
              <th>Disbursement Date</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="loan in loans" :key="loan.id">
              <td>#{{ loan.id }}</td>
              <td>{{ loan.member_name }}</td>
              <td class="amount">
                KES {{ loan.amount.toLocaleString() }}
              </td>
              <td>{{ loan.disbursement_date }}</td>
            </tr>

            <tr v-if="loans.length === 0">
              <td colspan="4" class="empty">
                No loan disbursements yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { defineProps } from 'vue'

defineProps<{
  loans: Array<{
    id: number
    member_name: string
    amount: number
    disbursement_date: string
  }>
}>()
</script>

<style scoped>
/* Page container */
.page-container {
  padding: 1.5rem;
}

/* Header */
.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.6rem;
  font-weight: 700;
  color: #0f2a44;
  /* dark blue */
}

.page-header p {
  font-size: 0.9rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

/* Card */
.card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(15, 42, 68, 0.08);
  overflow-x: auto;
}

/* Table */
.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: #0f2a44;
  /* dark blue */
}

.modern-table th {
  padding: 0.9rem;
  text-align: left;
  font-size: 0.85rem;
  font-weight: 600;
  color: #ffffff;
}

.modern-table td {
  padding: 0.85rem;
  font-size: 0.85rem;
  color: #1f2937;
  border-bottom: 1px solid #e5e7eb;
}

.modern-table tbody tr:hover {
  background: #f9fafb;
}

/* Amount highlight */
.amount {
  font-weight: 600;
  color: #f97316;
  /* orange */
}

/* Empty state */
.empty {
  text-align: center;
  padding: 2rem;
  font-size: 0.9rem;
  color: #6b7280;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header h1 {
    font-size: 1.3rem;
  }

  .modern-table th,
  .modern-table td {
    padding: 0.65rem;
    font-size: 0.8rem;
  }
}
</style>
