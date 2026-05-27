<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  guarantees: {
    type: Array,
    default: () => []
  }
})

const statusConfig = (status) => {
  switch (status) {
    case 'accepted':
      return {
        badge: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        icon: '✓'
      }

    case 'rejected':
      return {
        badge: 'bg-rose-100 text-rose-700 border border-rose-200',
        icon: '✕'
      }

    default:
      return {
        badge: 'bg-amber-100 text-amber-700 border border-amber-200',
        icon: '⏳'
      }
  }
}

const pending = computed(() =>
  props.guarantees.filter(g => g.status === 'pending')
)

const accepted = computed(() =>
  props.guarantees.filter(g => g.status === 'accepted')
)

const rejected = computed(() =>
  props.guarantees.filter(g => g.status === 'rejected')
)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE').format(amount || 0)
}
</script>

<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loans', href: route('my-loans') },
      { title: 'My Guarantees' }
    ]"
  >
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-6">

      <!-- HERO -->
      <section
        class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0F172A] via-[#132F57] to-[#1E3A8A] p-6 sm:p-8 shadow-2xl"
      >
        <div
          class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"
        ></div>

        <div
          class="absolute bottom-0 left-0 h-56 w-56 rounded-full bg-orange-500/10 blur-3xl"
        ></div>

        <div
          class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
        >
          <!-- LEFT -->
          <div>
            <div
              class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-orange-500/90 px-4 py-1.5 backdrop-blur-xl"
            >
              <span class="h-2 w-2 rounded-full bg-emerald-400"></span>

              <span class="text-xs font-medium tracking-wide text-white">
                GUARANTOR DASHBOARD
              </span>
            </div>

            <h1 class="mt-5 text-2xl font-bold tracking-tight text-white sm:text-3xl">
              My Guarantees
            </h1>

            <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
              Track all your loan guarantee requests, approvals, pending responses,
              and rejected guarantees in one modern dashboard.
            </p>
          </div>

          <!-- STATS -->
          <div class="grid grid-cols-3 gap-3 sm:gap-4">

            <div
              class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 text-center backdrop-blur-xl"
            >
              <p class="text-xs uppercase tracking-wide text-slate-300">
                Pending
              </p>

              <h3 class="mt-2 text-xl font-bold text-white">
                {{ pending.length }}
              </h3>
            </div>

            <div
              class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 text-center backdrop-blur-xl"
            >
              <p class="text-xs uppercase tracking-wide text-slate-300">
                Accepted
              </p>

              <h3 class="mt-2 text-xl font-bold text-emerald-300">
                {{ accepted.length }}
              </h3>
            </div>

            <div
              class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 text-center backdrop-blur-xl"
            >
              <p class="text-xs uppercase tracking-wide text-slate-300">
                Rejected
              </p>

              <h3 class="mt-2 text-xl font-bold text-rose-300">
                {{ rejected.length }}
              </h3>
            </div>

          </div>
        </div>
      </section>

      <!-- CONTENT -->
      <section
        class="mt-8 overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm"
      >
        <!-- HEADER -->
        <div
          class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between"
        >
          <div>
            <h2 class="text-xl font-bold text-slate-900">
              Guarantee Requests
            </h2>

            <p class="mt-1 text-sm text-slate-500">
              View and manage all guarantor activities
            </p>
          </div>

          <div
            class="inline-flex items-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700"
          >
            {{ guarantees.length }} Total Requests
          </div>
        </div>

        <!-- EMPTY -->
        <div
          v-if="!guarantees.length"
          class="flex flex-col items-center justify-center px-6 py-24 text-center"
        >
          <div
            class="flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-4xl"
          >
            📄
          </div>

          <h3 class="mt-6 text-xl font-bold text-slate-900">
            No Guarantees Yet
          </h3>

          <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-500">
            You currently have no loan guarantee requests assigned to your account.
          </p>
        </div>

        <!-- LIST -->
        <div
          v-else
          class="grid gap-5 p-5 sm:p-6"
        >
          <div
            v-for="g in guarantees"
            :key="g.id"
            class="group relative overflow-hidden rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
          >
            <!-- glow -->
            <div
              class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/40 to-orange-50/0 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
            ></div>

            <div
              class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
            >
              <!-- LEFT -->
              <div class="flex items-start gap-4">

                <!-- AVATAR -->
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-900 to-blue-700 text-lg font-bold text-white shadow-lg"
                >
                  {{
                    (g.loan?.member?.first_name?.[0] || '') +
                    (g.loan?.member?.last_name?.[0] || '')
                  }}
                </div>

                <!-- DETAILS -->
                <div>
                  <h3 class="text-lg font-bold text-slate-900">
                    {{ g.loan?.member?.first_name ?? '' }}
                    {{ g.loan?.member?.last_name ?? '' }}
                  </h3>

                  <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-500">

                    <span
                      class="rounded-full bg-slate-100 px-3 py-1"
                    >
                      Loan #: {{ g.loan?.loan_number }}
                    </span>

                    <span
                      class="rounded-full bg-blue-50 px-3 py-1 text-blue-700"
                    >
                      {{ g.loan?.purpose }}
                    </span>

                  </div>

                  <div
                    class="mt-4 inline-flex items-center rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700"
                  >
                    KES {{ formatCurrency(g.guaranteed_amount) }}
                  </div>
                </div>
              </div>

              <!-- RIGHT -->
              <div
                class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center"
              >
                <!-- STATUS -->
                <div
                  class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-xs font-semibold capitalize"
                  :class="statusConfig(g.status).badge"
                >
                  <span>
                    {{ statusConfig(g.status).icon }}
                  </span>

                  {{ g.status }}
                </div>

                <!-- BUTTON -->
                <button
                  @click="router.visit(`/guarantor-requests/${g.loan_id}`)"
                  class="flex h-12 items-center justify-center rounded-2xl bg-gradient-to-r from-[#0F172A] to-[#132F57] px-6 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                >
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<style scoped>
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 999px;
}

::-webkit-scrollbar-track {
  background: transparent;
}
</style>