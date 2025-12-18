<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Loan Repayment Schedule</h1>
  
      <table class="min-w-full table-auto border border-gray-200">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2">Loan ID</th>
            <th class="border px-4 py-2">Member Name</th>
            <th class="border px-4 py-2">Amount Due</th>
            <th class="border px-4 py-2">Repayment Date</th>
            <th class="border px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="repayment in repayments" :key="repayment.id">
            <td class="border px-4 py-2">{{ repayment.loan_id }}</td>
            <td class="border px-4 py-2">{{ repayment.member_name }}</td>
            <td class="border px-4 py-2">{{ repayment.amount_due }}</td>
            <td class="border px-4 py-2">{{ repayment.repayment_date }}</td>
            <td class="border px-4 py-2">{{ repayment.status }}</td>
          </tr>
          <tr v-if="repayments.length === 0">
            <td class="border px-4 py-2 text-center" colspan="5">No loan repayments scheduled.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineProps } from 'vue'
  
  const props = defineProps<{
    repayments: Array<{
      id: number
      loan_id: number
      member_name: string
      amount_due: number
      repayment_date: string
      status: string
    }>
  }>()
  </script>
  