<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Loan Disbursement Schedule</h1>
  
      <table class="min-w-full table-auto border border-gray-200">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2">Loan ID</th>
            <th class="border px-4 py-2">Member Name</th>
            <th class="border px-4 py-2">Amount</th>
            <th class="border px-4 py-2">Disbursement Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="loan in loans" :key="loan.id">
            <td class="border px-4 py-2">{{ loan.id }}</td>
            <td class="border px-4 py-2">{{ loan.member_name }}</td>
            <td class="border px-4 py-2">{{ loan.amount }}</td>
            <td class="border px-4 py-2">{{ loan.disbursement_date }}</td>
          </tr>
          <tr v-if="loans.length === 0">
            <td class="border px-4 py-2 text-center" colspan="4">No loan disbursements yet.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineProps } from 'vue'
  
  const props = defineProps<{
    loans: Array<{
      id: number
      member_name: string
      amount: number
      disbursement_date: string
    }>
  }>()
  </script>
  