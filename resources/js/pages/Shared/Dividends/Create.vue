<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps<{
  suggestedYear: number
  previousYear: number
  existingDividend: any
  financialData: any
  totalShareCapital: number
  activeMembers: number
  settings: {
    share_dividend_rate: number
    deposit_interest_rate: number
    tax_rate: number
  }
}>()

const form = ref({
  dividend_year: props.suggestedYear,
  notes: ''
})

const loading = ref(false)
const previewLoading = ref(false)
const previewData = ref<any>(null)

const hasExisting = computed(() => !!props.existingDividend)

/**
 * PREVIEW CALCULATION
 */
const previewCalculation = async () => {
  previewLoading.value = true
  previewData.value = null

  try {
    const res = await fetch(route('dividends.preview'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN':
          document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        dividend_year: form.value.dividend_year
      })
    })

    previewData.value = await res.json()
  } catch (e) {
    console.error(e)
  } finally {
    previewLoading.value = false
  }
}

/**
 * SUBMIT DIVIDEND
 */
const submit = () => {
  loading.value = true

  router.post(route('dividends.store'), form.value, {
    onFinish: () => (loading.value = false)
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/dividends' }, { title: 'Create' }]">
    <Head title="Create Dividend" />

    <div class="p-6 space-y-6">

      <!-- HEADER -->
      <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
          Dividend Calculation Engine
        </h1>
        <p class="text-sm text-slate-500">
          Preview and calculate dividends before final approval and distribution.
        </p>
      </div>

      <!-- WARNING -->
      <div
        v-if="hasExisting"
        class="bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-xl text-sm"
      >
        A dividend already exists for this year or previous year.
      </div>

      <!-- GRID -->
      <div class="grid gap-6 lg:grid-cols-3">

        <!-- LEFT FORM -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow p-5 space-y-4">

          <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200">
            Dividend Settings
          </h2>

          <!-- YEAR -->
          <div>
            <label class="text-sm text-slate-500">Dividend Year</label>
            <input
              v-model="form.dividend_year"
              type="number"
              class="w-full mt-1 border rounded-lg px-3 py-2 text-sm dark:bg-slate-800 focus:ring-2 focus:ring-blue-500 outline-none"
            />
          </div>

          <!-- NOTES -->
          <div>
            <label class="text-sm text-slate-500">Notes</label>
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full mt-1 border rounded-lg px-3 py-2 text-sm dark:bg-slate-800 focus:ring-2 focus:ring-blue-500 outline-none"
            />
          </div>

          <!-- ACTIONS -->
          <button
            @click="previewCalculation"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition"
          >
            {{ previewLoading ? 'Calculating...' : 'Preview Calculation' }}
          </button>

          <button
            @click="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg transition"
          >
            {{ loading ? 'Saving...' : 'Save Dividend' }}
          </button>

        </div>

        <!-- RIGHT SUMMARY -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow p-5">

          <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-4">
            Financial Overview
          </h2>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
              <p class="text-xs text-slate-500">Members</p>
              <h3 class="text-lg font-bold">{{ activeMembers }}</h3>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
              <p class="text-xs text-slate-500">Shares</p>
              <h3 class="text-lg font-bold">
                KES {{ totalShareCapital.toLocaleString() }}
              </h3>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
              <p class="text-xs text-slate-500">Share Rate</p>
              <h3 class="text-lg font-bold">
                {{ settings.share_dividend_rate }}%
              </h3>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
              <p class="text-xs text-slate-500">Deposit Rate</p>
              <h3 class="text-lg font-bold">
                {{ settings.deposit_interest_rate }}%
              </h3>
            </div>

          </div>
        </div>
      </div>

      <!-- SUMMARY PREVIEW -->
      <div
        v-if="previewData"
        class="bg-white dark:bg-slate-900 border rounded-2xl shadow p-5"
      >
        <h2 class="text-lg font-semibold mb-4 text-slate-700 dark:text-slate-200">
          Summary Preview
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

          <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
            <p class="text-xs text-slate-500">Members</p>
            <h3 class="font-bold">{{ previewData.summary.member_count }}</h3>
          </div>

          <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
            <p class="text-xs text-slate-500">Gross</p>
            <h3 class="font-bold">
              KES {{ previewData.summary.total_gross }}
            </h3>
          </div>

          <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
            <p class="text-xs text-slate-500">Tax</p>
            <h3 class="font-bold">
              KES {{ previewData.summary.total_tax }}
            </h3>
          </div>

          <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-xl text-center">
            <p class="text-xs text-green-500">Net Payable</p>
            <h3 class="font-bold text-green-500">
              KES {{ previewData.summary.total_net_payable }}
            </h3>
          </div>

        </div>
      </div>

      <!-- MEMBER PREVIEW TABLE -->
      <div
        v-if="previewData"
        class="bg-white dark:bg-slate-900 border rounded-2xl shadow p-5"
      >
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200">
            Member Breakdown Preview
          </h2>

          <span class="text-xs text-slate-500">
            Top {{ previewData.preview.length }} members
          </span>
        </div>

        <div class="overflow-x-auto">

          <table class="w-full text-sm">

            <thead class="bg-slate-50 dark:bg-slate-800">
              <tr>
                <th class="p-3 text-left">Member</th>
                <th class="p-3">Shares</th>
                <th class="p-3">Share Div</th>
                <th class="p-3">Deposit Int</th>
                <th class="p-3">Gross</th>
                <th class="p-3 text-red-500">Tax</th>
                <th class="p-3 text-green-600">Net</th>
              </tr>
            </thead>

            <tbody>

              <tr
                v-for="m in previewData.preview"
                :key="m.member_id"
                class="border-t hover:bg-slate-50 dark:hover:bg-slate-800"
              >

                <td class="p-3">
                  <div>
                    <p class="font-semibold">{{ m.member_name }}</p>
                    <p class="text-xs text-slate-500">
                      {{ m.membership_id }}
                    </p>
                  </div>
                </td>

                <td class="p-3 text-center">
                  {{ m.share_capital.toLocaleString() }}
                </td>

                <td class="p-3 text-center">
                  {{ m.share_dividend.toFixed(2) }}
                </td>

                <td class="p-3 text-center">
                  {{ m.deposit_interest.toFixed(2) }}
                </td>

                <td class="p-3 text-center">
                  {{ m.gross.toFixed(2) }}
                </td>

                <td class="p-3 text-center text-red-500">
                  {{ m.tax.toFixed(2) }}
                </td>

                <td class="p-3 text-center font-bold text-green-600">
                  {{ m.net_payable.toFixed(2) }}
                </td>

              </tr>

            </tbody>

          </table>

        </div>
      </div>

    </div>
  </AppLayout>
</template>