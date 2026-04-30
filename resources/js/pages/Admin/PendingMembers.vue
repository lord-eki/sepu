<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
  User2Icon,
  Search,
  ShieldCheck,
  Clock3,
  CheckCircle2,
  AlertCircle,
  ChevronRight,
  Users,
} from 'lucide-vue-next'

const loading = ref(true)
const pendingMembers = ref<any[]>([])
const awaitingActivation = ref<any[]>([])
const paymentFilter = ref<'all' | 'paid' | 'unpaid'>('all')

const pendingSearch = ref('')
const approvedSearch = ref('')

const fetchMembers = async () => {
  loading.value = true
  try {
    const res = await fetch('/admin/pending-members/list')
    if (!res.ok) throw new Error('Failed to fetch members')

    const data = await res.json()
    pendingMembers.value = data.pending || []

    awaitingActivation.value = [
      ...(data.approvedAwaitingActivation || []),
      ...(data.approvedButUnpaid || []),
    ]
  } catch (error) {
    console.error('Error fetching members:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchMembers)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })

const isFullyPaid = (member: any) =>
  member.accounts?.some(
    (a: any) => a.account_type === 'share_deposits' && a.available_balance >= 7500
  ) &&
  member.accounts?.some(
    (a: any) => a.account_type === 'share_capital' && a.available_balance >= 5000
  )

const filteredPending = computed(() =>
  pendingMembers.value.filter((member) => {
    const term = pendingSearch.value.toLowerCase()
    return (
      member.first_name.toLowerCase().includes(term) ||
      member.last_name.toLowerCase().includes(term) ||
      (member.membership_id || '').toLowerCase().includes(term) ||
      (member.user?.email || '').toLowerCase().includes(term) ||
      (member.user?.phone || '').toLowerCase().includes(term)
    )
  })
)

const filteredApproved = computed(() =>
  awaitingActivation.value
    .filter((member) => {
      const term = approvedSearch.value.toLowerCase()
      return (
        member.first_name.toLowerCase().includes(term) ||
        member.last_name.toLowerCase().includes(term) ||
        (member.membership_id || '').toLowerCase().includes(term) ||
        (member.user?.email || '').toLowerCase().includes(term) ||
        (member.user?.phone || '').toLowerCase().includes(term)
      )
    })
    .filter((member) => {
      if (paymentFilter.value === 'all') return true
      if (paymentFilter.value === 'paid') return isFullyPaid(member)
      if (paymentFilter.value === 'unpaid') return !isFullyPaid(member)
      return true
    })
)

const stats = computed(() => ({
  pending: pendingMembers.value.length,
  awaiting: awaitingActivation.value.length,
  ready: awaitingActivation.value.filter((m) => isFullyPaid(m)).length,
  unpaid: awaitingActivation.value.filter((m) => !isFullyPaid(m)).length,
}))
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Members Approvals', href: '/admin/pending-members' }]">
    <Head title="Member Approvals" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white sm:p-6">
      <div class="mx-auto max-w-7xl space-y-8">
        <!-- Hero -->
        <section
          class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 p-6 shadow-2xl shadow-blue-950/20 sm:p-8"
        >
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(249,115,22,0.18),transparent_25%),radial-gradient(circle_at_left,rgba(59,130,246,0.18),transparent_30%)]" />
          <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                Member Approvals
              </h1>
              <p class="mt-2 max-w-2xl text-xs sm:text-sm text-slate-300 ">
                Review pending applications, track approval progress, and activate members once
                onboarding requirements are complete.
              </p>
            </div>

            <div class="grid w-full max-w-3xl grid-cols-2 gap-3 sm:grid-cols-4">
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-xl">
                <p class="text-xs text-slate-400">Pending</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ stats.pending }}</p>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-xl">
                <p class="text-xs text-slate-400">Awaiting Activation</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ stats.awaiting }}</p>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-xl">
                <p class="text-xs text-slate-400">Ready</p>
                <p class="mt-1 text-2xl font-bold text-emerald-400">{{ stats.ready }}</p>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-xl">
                <p class="text-xs text-slate-400">Awaiting Payment</p>
                <p class="mt-1 text-2xl font-bold text-orange-400">{{ stats.unpaid }}</p>
              </div>
            </div>
          </div>
        </section>

        <div v-if="loading" class="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/40">
            <Users class="h-6 w-6 animate-pulse text-blue-600 dark:text-blue-400" />
          </div>
          <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Loading member approvals...</p>
        </div>

        <template v-else>
          <!-- Pending -->
          <section class="space-y-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Pending Approvals</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Members awaiting administrative review.
                </p>
              </div>

              <div class="relative w-full max-w-md">
                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="pendingSearch"
                  type="text"
                  placeholder="Search name, email, phone or ID..."
                  class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-blue-950"
                />
              </div>
            </div>

            <div
              class="overflow-hidden rounded-3xl border border-slate-300 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
              <div v-if="filteredPending.length === 0" class="p-10 text-center">
                <Clock3 class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" />
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                  No pending member applications.
                </p>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                      <th class="px-6 py-4">Member</th>
                      <th class="px-6 py-4">Contact</th>
                      <th class="px-6 py-4">Status</th>
                      <th class="px-6 py-4">Date Joined</th>
                      <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                      v-for="member in filteredPending"
                      :key="member.id"
                      class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                    >
                      <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                          <div v-if="member.profile_photo" class="h-11 w-11 overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                            <img :src="`/storage/${member.profile_photo}`" class="h-full w-full object-cover" />
                          </div>
                          <div v-else class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <User2Icon class="h-5 w-5 text-slate-500" />
                          </div>

                          <div>
                            <p class="font-semibold text-slate-900 dark:text-white">
                              {{ member.first_name }} {{ member.last_name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                              {{ member.membership_id || 'N/A' }}
                            </p>
                          </div>
                        </div>
                      </td>

                      <td class="px-6 py-4">
                        <p class="text-slate-800 dark:text-slate-200">{{ member.user?.email || '-' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ member.user?.phone || '-' }}</p>
                      </td>

                      <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                          <Clock3 class="h-3.5 w-3.5" />
                          Pending
                        </span>
                      </td>

                      <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                        {{ formatDate(member.membership_date || member.created_at) }}
                      </td>

                      <td class="px-6 py-4 text-right">
                        <Link
                          :href="route('members.show', member.id)"
                          class="inline-flex items-center gap-1 font-medium text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                          View
                          <ChevronRight class="h-4 w-4" />
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- Awaiting Activation -->
          <section class="space-y-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Awaiting Activation</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Approved members waiting for activation and payment completion.
                </p>
              </div>

              <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <div class="relative w-full sm:max-w-md">
                  <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                  <input
                    v-model="approvedSearch"
                    type="text"
                    placeholder="Search name, email, phone or ID..."
                    class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-orange-950"
                  />
                </div>

                <select
                  v-model="paymentFilter"
                  class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:ring-orange-950"
                >
                  <option value="all">All Status</option>
                  <option value="paid">Paid (Ready)</option>
                  <option value="unpaid">Awaiting Payment</option>
                </select>
              </div>
            </div>

            <div
              class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
              <div v-if="filteredApproved.length === 0" class="p-10 text-center">
                <AlertCircle class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" />
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                  No approved members awaiting activation.
                </p>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead class="border-b border-slate-200 bg-slate-200 dark:border-slate-800 dark:bg-slate-950/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-blue-950 dark:text-slate-400">
                      <th class="px-6 py-4">Member</th>
                      <th class="px-6 py-4">Contact</th>
                      <th class="px-6 py-4">Payment Status</th>
                      <th class="px-6 py-4">Date Approved</th>
                      <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                      v-for="member in filteredApproved"
                      :key="member.id"
                      class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                    >
                      <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                          <div v-if="member.profile_photo" class="h-11 w-11 overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                            <img :src="`/storage/${member.profile_photo}`" class="h-full w-full object-cover" />
                          </div>
                          <div v-else class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <User2Icon class="h-5 w-5 text-slate-500" />
                          </div>

                          <div>
                            <p class="font-semibold text-slate-900 dark:text-white">
                              {{ member.first_name }} {{ member.last_name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                              {{ member.membership_id || 'N/A' }}
                            </p>
                          </div>
                        </div>
                      </td>

                      <td class="px-6 py-4">
                        <p class="text-slate-800 dark:text-slate-200">{{ member.user?.email || '-' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ member.user?.phone || '-' }}</p>
                      </td>

                      <td class="px-6 py-4">
                        <span
                          v-if="isFullyPaid(member)"
                          class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                        >
                          <CheckCircle2 class="h-3.5 w-3.5" />
                          Paid (Ready)
                        </span>

                        <span
                          v-else
                          class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                        >
                          <AlertCircle class="h-3.5 w-3.5" />
                          Awaiting Payment
                        </span>
                      </td>

                      <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                        {{ formatDate(member.updated_at) }}
                      </td>

                      <td class="px-6 py-4 text-right">
                        <Link
                          :href="route('members.show', member.id)"
                          class="inline-flex items-center gap-1 font-medium text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                          View
                          <ChevronRight class="h-4 w-4" />
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>
        </template>
      </div>
    </div>
  </AppLayout>
</template>