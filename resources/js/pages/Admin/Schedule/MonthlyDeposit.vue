<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Monthly Deposit Schedule', href: '#' }
    ]"
  >
    <div class="page-container">
      <!-- Page Header -->
      <div class="page-header">
        <h1>Monthly Deposit Schedule</h1>
        <p>Scheduled and completed monthly member deposits</p>
      </div>

      <!-- Card -->
      <div class="card">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Member ID</th>
              <th>Member Name</th>
              <th>Deposit Amount</th>
              <th>Deposit Due Date</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="deposit in deposits" :key="deposit.id">
              <td>#{{ deposit.member_id }}</td>
              <td>{{ deposit.member_name }}</td>
              <td class="amount">
                KES {{ deposit.amount.toLocaleString() }}
              </td>
              <td>{{ deposit.due_date }}</td>
              <td>
                <span
                  class="status"
                  :class="deposit.status.toLowerCase()"
                >
                  {{ deposit.status }}
                </span>
              </td>
            </tr>

            <tr v-if="deposits.length === 0">
              <td colspan="5" class="empty">
                No monthly deposits scheduled.
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
  deposits: Array<{
    id: number
    member_id: number
    member_name: string
    amount: number
    due_date: string
    status: string
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
}

/* Status badges */
.status {
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status.pending {
  background: #fff7ed;
  color: #c2410c;
}

.status.paid {
  background: #ecfdf5;
  color: #047857;
}

.status.overdue {
  background: #fef2f2;
  color: #b91c1c;
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
