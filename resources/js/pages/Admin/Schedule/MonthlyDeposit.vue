<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Monthly Deposit Schedule</h1>
  
      <table class="min-w-full table-auto border border-gray-200">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2">Member ID</th>
            <th class="border px-4 py-2">Member Name</th>
            <th class="border px-4 py-2">Deposit Amount</th>
            <th class="border px-4 py-2">Deposit Due Date</th>
            <th class="border px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="deposit in deposits" :key="deposit.id">
            <td class="border px-4 py-2">{{ deposit.member_id }}</td>
            <td class="border px-4 py-2">{{ deposit.member_name }}</td>
            <td class="border px-4 py-2">{{ deposit.amount }}</td>
            <td class="border px-4 py-2">{{ deposit.due_date }}</td>
            <td class="border px-4 py-2">{{ deposit.status }}</td>
          </tr>
          <tr v-if="deposits.length === 0">
            <td class="border px-4 py-2 text-center" colspan="5">No monthly deposits scheduled.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineProps } from 'vue'
  
  const props = defineProps<{
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
  