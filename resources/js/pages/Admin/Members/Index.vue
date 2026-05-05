<template>
  <AppLayout :breadcrumbs="[{ title: 'Members', href: '/members' }]">

    <Head title="Members" />

    <!-- Flash -->
    <div ref="flashBox" class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
      <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-3"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
        <div v-if="flashMessage" class="flex items-start gap-3 rounded-2xl border px-4 py-4 shadow-lg backdrop-blur-xl"
          :class="flashType === 'success'
      ? 'border-emerald-200 bg-emerald-50/90 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
      : 'border-red-200 bg-red-50/90 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300'
    ">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="mt-0.5 h-5 w-5 shrink-0" />
          <p class="text-sm font-medium">{{ flashMessage }}</p>
          <button type="button"
            class="ml-auto rounded-lg p-1 text-current/70 transition hover:bg-black/5 hover:text-current dark:hover:bg-white/10"
            @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>

    <div class="min-h-screen bg-slate-50 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100">

      <!-- Hero -->
      <section class="mx-auto max-w-7xl px-4 py-6 px-4">
        <div
          class="relative rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 shadow-2xl shadow-blue-950/20">
          <!-- Background Effects -->
          <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,146,60,0.18),transparent_30%),radial-gradient(circle_at_left,rgba(59,130,246,0.14),transparent_35%)]">
          </div>
          <div class="absolute inset-0 bg-grid-white/[0.03] bg-[size:24px_24px]"></div>

          <div class="relative px-4 py-6 sm:px-5 sm:py-7 lg:px-6 lg:py-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
              <!-- Left Content -->
              <div class="flex flex-1 items-start gap-4">
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/10 shadow-lg shadow-black/20 backdrop-blur-md">
                  <Users class="h-6 w-6 text-white" />
                </div>

                <div class="min-w-0">
                  <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                      Members Management
                    </h1>
                  </div>

                  <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300 sm:text-base">
                    Manage registrations, approvals, account access, and member records.
                  </p>
                </div>
              </div>

              <!-- Right Actions -->
              <div class="flex flex-wrap items-center gap-3 xl:justify-end">
                <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.create')"
                  class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-orange-600">
                <PlusCircle class="h-4 w-4" />
                Add Member
                </Link>

                <!-- Import Dropdown -->
                <div class="relative" ref="importWrapper">
                  <button @click="openImport = !openImport"
                    class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-md transition-all duration-200 hover:bg-white/15">
                    <Upload class="h-4 w-4" />
                    Import
                    <ChevronDownIcon class="h-4 w-4" />
                  </button>

                  <div v-if="openImport"
                    class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                    <Link :href="route('members.import.form')"
                      class="block px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                    Import Members
                    </Link>
                    <Link :href="route('members.deposits.import.form')"
                      class="block px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                    Import Deposits
                    </Link>
                  </div>
                </div>

                <!-- Export -->
                <button @click="exportMembers"
                  class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-100">
                  <Loader2Icon v-if="isExporting" class="h-4 w-4 animate-spin" />
                  <DownloadIcon v-else class="h-4 w-4" />
                  {{ isExporting ? 'Exporting...' : 'Export' }}
                </button>

                <!-- More Actions -->
                <div class="relative" ref="actionsWrapper">
                  <button @click="openActions = !openActions"
                    class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-md transition-all duration-200 hover:bg-white/15">
                    More
                    <ChevronDownIcon class="h-4 w-4" />
                  </button>

                  <div v-if="openActions"
                    class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                    <button @click="generateUsernames"
                      class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-medium text-blue-600 transition hover:bg-slate-50 dark:text-blue-400 dark:hover:bg-slate-800">
                      <Loader2Icon v-if="isGenerating" class="h-4 w-4 animate-spin" />
                      {{ isGenerating ? 'Generating...' : 'Generate Username(s)' }}
                    </button>

                    <button @click="confirmDelete"
                      class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-slate-800">
                      Delete Selected
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Stats -->
      <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div v-for="card in cards" :key="card.label" :title="card.tooltip || ''"
            @click="card.link ? $inertia.get(card.link) : null"
            class="group cursor-pointer rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                <p class="mt-2 text-2xl font-semibold text-center tracking-tight text-slate-900 dark:text-white">
                  {{ card.value }}
                </p>
              </div>
              <div :class="['rounded-2xl p-3', card.color]">
                <component :is="card.icon" class="h-5 w-5" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Filters -->
      <section class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white">Filter & Sort</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Refine members by status, date and keywords.</p>
            </div>

            <button @click="filtersOpen = !filtersOpen"
              class="rounded-xl px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-slate-800 sm:hidden">
              {{ filtersOpen ? 'Hide' : 'Show' }}
            </button>
          </div>

          <div :class="['grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4', filtersOpen ? '' : 'hidden sm:grid']">
            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
              <input v-model="form.search" @input="search" type="text" placeholder="Search members..."
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
            </div>

            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
              <select v-model="form.status" @change="search"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">All</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>

            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Sort By</label>
              <select v-model="form.sortBy" @change="search"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="created_at">Date Joined</option>
                <option value="first_name">First Name</option>
                <option value="last_name">Last Name</option>
                <option value="membership_id">Member ID</option>
              </select>
            </div>

            <div>
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Direction</label>
              <select v-model="form.sortDirection" @change="search"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="asc">Asc</option>
                <option value="desc">Desc</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- Mobile Cards -->
      <section class="mx-auto mt-8 max-w-7xl px-4 sm:hidden">
        <div v-for="member in members.data" :key="member.id"
          class="mb-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`"
                class="h-12 w-12 rounded-2xl object-cover" />
              <div v-else class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                <User2Icon class="h-5 w-5 text-slate-500" />
              </div>

              <div>
                <p class="font-semibold text-slate-900 dark:text-white">
                  {{ member.first_name }} {{ member.last_name }}
                </p>
                <p class="text-xs text-slate-500">{{ member.membership_id }}</p>
              </div>
            </div>

            <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
              :class="statusColors[member.membership_status]">
              {{ member.membership_status }}
            </span>
          </div>

          <div class="mt-4 space-y-1 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ member.user.email }}</p>
            <p class="text-xs text-slate-500">{{ member.user.phone }}</p>
            <p><span class="font-medium">Accounts:</span> {{ member.accounts.length }}</p>
            <p><span class="font-medium">Joined:</span> {{ formatDate(member.membership_date) }}</p>
          </div>

          <div class="mt-4 flex gap-4 text-sm font-medium">
            <Link :href="route('members.show', member.id)" class="text-blue-600 dark:text-blue-400">View</Link>
            <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
              class="text-orange-600 dark:text-orange-400">
            Edit
            </Link>
          </div>
        </div>
      </section>

      <!-- Desktop Table -->
      <section class="mx-auto mt-8 hidden max-w-7xl px-4 pb-10 sm:block sm:px-6 lg:px-8">
        <div
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-blue-950 text-left text-white">
                <tr>
                  <th class="px-4 py-4">
                    <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                  </th>
                  <th class="px-4 py-4 font-semibold">Member</th>
                  <th class="px-4 py-4 font-semibold">Username</th>
                  <th class="px-4 py-4 font-semibold">Contact</th>
                  <th class="px-4 py-4 font-semibold">Status</th>
                  <th class="px-4 py-4 font-semibold">Accounts</th>
                  <th class="px-4 py-4 font-semibold">Date Joined</th>
                  <th class="px-4 py-4 text-right font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <tr v-for="member in members.data" :key="member.id"
                  class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                  <td class="px-4 py-4">
                    <input type="checkbox" v-model="selectedMembers" :value="member.id" />
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`"
                        class="h-11 w-11 rounded-2xl object-cover" />
                      <div v-else
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <User2Icon class="h-5 w-5 text-slate-500" />
                      </div>

                      <div>
                        <p class="font-semibold text-slate-900 dark:text-white">
                          {{ member.first_name }} {{ member.last_name }}
                        </p>
                        <p class="text-xs text-slate-500">{{ member.membership_id }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                    {{ member.user.username || 'N/A' }}
                  </td>

                  <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                    <p>{{ member.user.email }}</p>
                    <p class="text-xs text-slate-500">{{ member.user.phone }}</p>
                  </td>

                  <td class="px-6 py-4">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="statusColors[member.membership_status]">
                      {{ member.membership_status }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                    {{ member.accounts.length }} accounts
                  </td>

                  <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                    {{ formatDate(member.membership_date) }}
                  </td>

                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-4 text-sm font-medium">
                      <Link :href="route('members.show', member.id)"
                        class="text-blue-600 hover:underline dark:text-blue-400">
                      View
                      </Link>
                      <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
                        class="text-orange-600 hover:underline dark:text-orange-400">
                      Edit
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <Pagination :data="members" class="mt-5" />
      </section>

      <!-- Mobile FAB -->
      <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.create')"
        class="fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-500 text-white shadow-2xl shadow-orange-500/30 sm:hidden">
      <PlusCircle class="h-6 w-6" />
      </Link>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteConfirm"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
      <div
        class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Confirm Delete</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
          Are you sure you want to delete the selected members? This action cannot be undone.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button @click="showDeleteConfirm = false"
            class="rounded-2xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">
            Cancel
          </button>

          <button @click="deleteMembers" :disabled="isDeleting"
            class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-70">
            <Loader2Icon v-if="isDeleting" class="h-4 w-4 animate-spin" />
            {{ isDeleting ? 'Deleting...' : 'Delete Members' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import {
  CheckCircle,
  CircleX,
  PlusCircle,
  TriangleAlert,
  Loader2Icon,
  DownloadIcon,
  ChevronDownIcon,
  User2Icon,
  Users,
  AlertCircle,
  Upload
} from 'lucide-vue-next'

const props = defineProps({
  members: Object,
  filters: Object,
  stats: Object
})

const page = usePage()
const flash = computed(() => page.value?.props?.flash || {})
const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

const filtersOpen = ref(false)
const isDeleting = ref(false)
const isExporting = ref(false)
const isGenerating = ref(false)

const openImport = ref(false)
const importWrapper = ref(null)

const openActions = ref(false)
const actionsWrapper = ref(null)

const selectedMembers = ref([])
const selectAll = ref(false)
const showDeleteConfirm = ref(false)

const statuses = ref(['active', 'inactive', 'suspended', 'pending', 'approved', 'rejected'])

const statusColors = {
  active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
  inactive: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
  suspended: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
  pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
  approved: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
  rejected: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'
}

const cards = computed(() => [
  {
    label: 'Total Members',
    value: props.stats.total,
    icon: Users,
    color: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300'
  },
  {
    label: 'Active',
    value: props.stats.active,
    icon: CheckCircle,
    color: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300'
  },
  {
    label: 'Inactive',
    value: props.stats.inactive,
    icon: CircleX,
    color: 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-300'
  },
  {
    label: 'Suspended',
    value: props.stats.suspended,
    icon: TriangleAlert,
    color: 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-300'
  },
  {
    label: 'Pending Approvals',
    value: props.stats.pending,
    icon: TriangleAlert,
    color: 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300',
    link: route('admin.pending-members'),
    tooltip: 'View pending approvals'
  },
  {
    label: 'Rejected',
    value: props.stats.rejected,
    icon: CircleX,
    color: 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-300'
  },
  {
    label: 'Approved',
    value: props.stats.approved,
    icon: CheckCircle,
    color: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-300',
    link: route('admin.pending-members'),
    tooltip: 'View pending activations'
  }
])

watch(
  flash,
  (val) => {
    if (val?.success) {
      flashMessage.value = val.success
      flashType.value = 'success'
    } else if (val?.error) {
      flashMessage.value = val.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      window.scrollTo({ top: 0, behavior: 'smooth' })
      flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

const handleOutside = (event) => {
  if (importWrapper.value && !importWrapper.value.contains(event.target)) openImport.value = false
  if (actionsWrapper.value && !actionsWrapper.value.contains(event.target)) openActions.value = false
}

onMounted(() => window.addEventListener('click', handleOutside))
onBeforeUnmount(() => window.removeEventListener('click', handleOutside))

const toggleSelectAll = () => {
  selectedMembers.value = selectAll.value ? props.members.data.map((m) => m.id) : []
}

const exportMembers = () => {
  if (!selectedMembers.value.length) {
    flashMessage.value = 'Please select at least one member to export'
    flashType.value = 'error'
    return setTimeout(() => (flashMessage.value = null), 4000)
  }

  isExporting.value = true
  const params = new URLSearchParams()
  selectedMembers.value.forEach((id) => params.append('member_ids[]', id))
  window.location.href = route('members.export') + '?' + params.toString()
  setTimeout(() => (isExporting.value = false), 2000)
}

const generateUsernames = () => {
  if (!selectedMembers.value.length) {
    flashMessage.value = 'Please select at least one member'
    flashType.value = 'error'
    return setTimeout(() => (flashMessage.value = null), 4000)
  }

  isGenerating.value = true

  router.post(
    route('members.assignUsernames'),
    { member_ids: selectedMembers.value },
    {
      onFinish: () => {
        isGenerating.value = false
        selectedMembers.value = []
        selectAll.value = false
      },
      onError: () => {
        flashMessage.value = 'Something went wrong'
        flashType.value = 'error'
      }
    }
  )
}

const confirmDelete = () => {
  if (!selectedMembers.value.length) {
    flashMessage.value = 'Please select at least one member'
    flashType.value = 'error'
    return setTimeout(() => (flashMessage.value = null), 4000)
  }
  showDeleteConfirm.value = true
}

const deleteMembers = () => {
  isDeleting.value = true

  router.post(
    route('members.bulkDelete'),
    { member_ids: selectedMembers.value },
    {
      preserveScroll: true,
      onFinish: () => {
        isDeleting.value = false
        showDeleteConfirm.value = false
        selectedMembers.value = []
        selectAll.value = false
      }
    }
  )
}

const form = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  sortBy: props.filters.sortBy || 'created_at',
  sortDirection: props.filters.sortDirection || 'desc'
})

const search = debounce(() => {
  router.get(route('members.index'), form.value, {
    preserveState: true,
    replace: true
  })
}, 300)

const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.4s ease-in-out;
}

button:hover {
  cursor: pointer;
}
</style>
