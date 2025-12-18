<template>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Dividend Repayment Schedule</h1>
  
      <table class="min-w-full table-auto border border-gray-200">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2">Member ID</th>
            <th class="border px-4 py-2">Member Name</th>
            <th class="border px-4 py-2">Dividend Amount</th>
            <th class="border px-4 py-2">Payment Date</th>
            <th class="border px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="dividend in dividends" :key="dividend.id">
            <td class="border px-4 py-2">{{ dividend.member_id }}</td>
            <td class="border px-4 py-2">{{ dividend.member_name }}</td>
            <td class="border px-4 py-2">{{ dividend.amount }}</td>
            <td class="border px-4 py-2">{{ dividend.payment_date }}</td>
            <td class="border px-4 py-2">{{ dividend.status }}</td>
          </tr>
          <tr v-if="dividends.length === 0">
            <td class="border px-4 py-2 text-center" colspan="5">No dividend payments scheduled.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineProps } from 'vue'
  
  const props = defineProps<{
    dividends: Array<{
      id: number
      member_id: number
      member_name: string
      amount: number
      payment_date: string
      status: string
    }>
  }>()
  </script>
  