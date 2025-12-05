<template>
  <AppLayout :breadcrumbs="[{ title: 'Members', href: '/members' }]">

    <Head title="Members Management" />
    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" class="flex gap-3 mb-4 rounded-md p-4 shadow items-center border" :class="flashType === 'success'
    ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-700 dark:text-green-300'
    : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300'">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5" :class="flashType === 'success'
    ? 'text-green-600 dark:text-green-300'
    : 'text-red-600 dark:text-red-300'" />

          <p class="ml-3 text-sm">
            {{ flashMessage }}
          </p>

          <button type="button"
            class="ml-auto text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100"
            @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>


    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-20 transition-colors">
      <!-- HEADER -->
      <div
        class="mx-4 mt-6 rounded-2xl shadow-lg bg-gradient-to-br from-[#0a2342] via-[#0c2e55] to-[#103a66] dark:from-gray-800 dark:to-gray-700 text-white p-6 sm:p-8">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold">Members Management</h1>
            <p class="text-blue-100 max-sm:text-sm mt-1 dark:text-gray-300">
              Manage and organize all SACCO members efficiently.
            </p>
          </div>

          <!-- SMART ACTIONS -->
          <div class="flex flex-wrap gap-3 sm:gap-4 items-center">

            <!-- Add Member -->
            <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.create')" class="px-4 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 
                    text-white text-sm sm:text-base shadow flex items-center gap-1">
            <PlusCircle class="w-4 h-4" />
            Add Member
            </Link>

            <!-- Import dropdown -->
            <div class="relative" ref="importWrapper">
              <button @click="openImport = !openImport" class="px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 
                      text-white text-sm sm:text-base shadow flex items-center gap-1">
                <Upload class="w-4 h-4" /> Import
                <ChevronDownIcon class="w-4 h-4" />
              </button>

              <div v-if="openImport" class="absolute right-0 py-2 mt-2 w-48 bg-gray-100 dark:bg-gray-800 
                      shadow-lg rounded-xl overflow-hidden border dark:border-gray-700 z-50">
                <Link :href="route('members.import.form')" class="block px-4 py-2 text-sm hover:bg-blue-200 dark:hover:bg-gray-700 
                        text-slate-900 dark:text-gray-200">
                Import Members
                </Link>

                <Link :href="route('members.deposits.import.form')" class="block px-4 py-2 text-sm hover:bg-blue-200 dark:hover:bg-gray-700 
                        text-slate-900 dark:text-gray-200">
                Import Deposits
                </Link>
              </div>
            </div>

            <!-- Generate usernames -->
            <button @click="generateUsernames" class="px-4 py-3 rounded-xl bg-green-600 hover:bg-green-700 
                    text-white text-sm sm:text-base flex items-center gap-2 shadow" :disabled="isGenerating">
              <Loader2Icon v-if="isGenerating" class="w-4 h-4 animate-spin" />
              {{ isGenerating ? "Generating..." : "Generate Username(s)" }}
            </button>


            <!-- More actions -->
            <div class="relative" ref="actionsWrapper">
              <button @click="openActions = !openActions"
                class="px-4 py-3 rounded-xl bg-[#0a2342] hover:bg-[#103a66] text-white text-sm sm:text-base shadow flex items-center gap-1 dark:bg-gray-700 dark:hover:bg-gray-600">
                More
                <ChevronDownIcon class="w-4 h-4" />
              </button>

              <div v-if="openActions"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden border dark:border-gray-700 z-50">
                <button @click="confirmDelete"
                  class="w-full px-4 py-3 text-left text-sm sm:text-base text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-gray-700 flex items-center gap-2">
                  <CircleX class="w-4 h-4" />
                  Delete Selected
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 mt-8 animate-fadeIn">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="card in [
    {
      label: 'Total Members',
      value: stats.total,
      icon: Users,
      color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 dark:text-indigo-300'
    },
    {
      label: 'Active',
      value: stats.active,
      icon: CheckCircle,
      color: 'text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-300'
    },
    {
      label: 'Inactive',
      value: stats.inactive,
      icon: CircleX,
      color: 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-300'
    },
    {
      label: 'Suspended',
      value: stats.suspended,
      icon: TriangleAlert,
      color: 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-300'
    },
    {
      label: 'Pending Approvals',
      value: stats.pending,
      icon: TriangleAlert,
      color: 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-300',
      link: route('admin.pending-members'),
      tooltip: 'View pending approvals'
    },
    {
      label: 'Rejected',
      value: stats.rejected,
      icon: CircleX,
      color: 'text-rose-600 bg-rose-50 dark:bg-rose-900/20 dark:text-rose-300'
    },
    {
      label: 'Approved',
      value: stats.approved,
      icon: CheckCircle,
      color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-300',
      link: route('admin.pending-members'),
      tooltip: 'View pending activations'
    }
  ]" :key="card.label" :title="card.tooltip || ''" class="p-5 rounded-2xl bg-white/90 dark:bg-gray-900/50 shadow-sm border border-gray-100 
                dark:border-gray-700 backdrop-blur-sm hover:shadow-md transition cursor-pointer"
            @click="card.link ? $inertia.get(card.link) : null">
            <div class="flex items-center gap-4">
              <div :class="['rounded-xl p-3', card.color]">
                <component :is="card.icon" class="h-6 w-6" />
              </div>

              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- FILTERS -->
      <div
        class="max-w-7xl mx-auto mt-10 px-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm p-6">

        <div class="flex justify-between items-center mb-4">
          <h3 class="font-semibold text-gray-800 dark:text-gray-200">Filter & Sort</h3>

          <button @click="filtersOpen = !filtersOpen" class="sm:hidden text-sm text-blue-600 dark:text-blue-400">
            {{ filtersOpen ? "Hide" : "Show" }} Filters
          </button>
        </div>

        <div :class="['grid grid-cols-1 sm:grid-cols-4 gap-4', filtersOpen ? '' : 'hidden sm:grid']">

          <!-- SEARCH -->
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Search</label>
            <input v-model="form.search" @input="search" type="text" placeholder="Search..."
              class="mt-1 w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm" />
          </div>

          <!-- STATUS -->
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
            <select v-model="form.status" @change="search"
              class="mt-1 w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm">
              <option value="">All</option>
              <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>

          <!-- SORT BY -->
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sort By</label>
            <select v-model="form.sortBy" @change="search"
              class="mt-1 w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm">
              <option value="created_at">Date Joined</option>
              <option value="first_name">First Name</option>
              <option value="last_name">Last Name</option>
              <option value="membership_id">Member ID</option>
            </select>
          </div>

          <!-- SORT DIRECTION -->
          <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Direction</label>
            <select v-model="form.sortDirection" @change="search"
              class="mt-1 w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 shadow-sm">
              <option value="asc">Asc</option>
              <option value="desc">Desc</option>
            </select>
          </div>

        </div>
      </div>

      <!-- MOBILE LIST VIEW -->
      <div class="max-w-7xl mx-auto mt-8 px-4 sm:hidden">
        <div v-for="member in members.data" :key="member.id"
          class="p-4 mb-4 rounded-xl bg-white dark:bg-gray-800 border dark:border-gray-700 shadow-sm">

          <div class="flex justify-between items-center">
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">
                {{ member.first_name }} {{ member.last_name }}
              </p>
              <p class="text-xs text-gray-500">{{ member.membership_id }}</p>
            </div>

            <span class="text-xs px-2 py-1 rounded-full" :class="statusColors[member.membership_status]">
              {{ member.membership_status }}
            </span>
          </div>

          <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">{{ member.user.email }}</p>
          <p class="text-xs text-gray-500">{{ member.user.phone }}</p>

          <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">
            <p><strong>Accounts:</strong> {{ member.accounts.length }}</p>
            <p><strong>Joined:</strong> {{ formatDate(member.membership_date) }}</p>
          </div>

          <div class="mt-3 flex gap-4 text-sm">
            <Link :href="route('members.show', member.id)" class="text-blue-600 dark:text-blue-400">View</Link>
            <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
              class="text-orange-600 dark:text-orange-400">
            Edit
            </Link>
          </div>
        </div>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="max-w-7xl mx-auto mt-10 px-4 hidden sm:block">
        <div class="overflow-x-auto rounded-sm shadow border border-gray-200 dark:border-gray-700">
          <table class="w-full text-sm px-2">
            <thead class="bg-[rgb(10,35,66)] dark:bg-gray-800 text-white">
              <tr>
                <th class="px-3 py-4"><input type="checkbox" v-model="selectAll" @change="toggleSelectAll" /></th>
                <th class="px-6 py-4 text-left font-medium">Member</th>
                <th class="px-6 py-4 text-left font-medium">Username</th>
                <th class="px-6 py-4 text-left font-medium">Contact</th>
                <th class="px-6 py-4 text-left font-medium">Status</th>
                <th class="px-3 py-4 text-left font-medium">Accounts</th>
                <th class="px-6 py-4 text-left font-medium">Date Joined</th>
                <th class="px-3 py-4 text-right font-medium">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="member in members.data" :key="member.id"
                class="hover:bg-blue-50 dark:hover:bg-gray-700 transition">

                <td class="px-3 py-4">
                  <input type="checkbox" v-model="selectedMembers" :value="member.id" />
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`"
                      class="h-10 w-10 rounded-full object-cover" />

                    <div v-else
                      class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                      <User2Icon class="h-6 w-6 text-gray-600 dark:text-gray-300" />
                    </div>

                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">
                        {{ member.first_name }} {{ member.last_name }}
                      </p>
                      <p class="text-xs text-gray-500">{{ member.membership_id }}</p>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 dark:text-gray-200">
                  {{ member.user.username || "N/A" }}
                </td>

                <td class="px-6 py-4 dark:text-gray-200">
                  <p>{{ member.user.email }}</p>
                  <p class="text-xs text-gray-500">{{ member.user.phone }}</p>
                </td>

                <td class="px-6 py-4">
                  <span class="px-2 py-1 rounded-full text-xs font-medium"
                    :class="statusColors[member.membership_status]">
                    {{ member.membership_status }}
                  </span>
                </td>

                <td class="px-3 py-4 dark:text-gray-200">
                  {{ member.accounts.length }} accounts
                </td>

                <td class="px-6 py-4 dark:text-gray-200">
                  {{ formatDate(member.membership_date) }}
                </td>

                <td class="px-3 py-4 text-right space-x-3">
                  <Link :href="route('members.show', member.id)"
                    class="text-blue-600 dark:text-blue-400 hover:underline">
                  View
                  </Link>

                  <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
                    class="text-orange-600 dark:text-orange-400 hover:underline">
                  Edit
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination :data="members" class="mt-4" />
      </div>

      <!-- MOBILE FLOATING BUTTON -->
      <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.create')"
        class="fixed bottom-6 right-6 sm:hidden p-4 bg-[#0a2342] dark:bg-orange-600 text-white rounded-full shadow-lg">
      <PlusCircle class="w-6 h-6" />
      </Link>

    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl max-w-sm w-full">
        <h2 class="text-lg font-bold dark:text-white">Confirm Delete</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300">Are you sure you want to delete the selected members?</p>

        <div class="flex justify-end gap-3 mt-6">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">
            Cancel
          </button>

          <button @click="deleteMembers" class="px-4 py-2 bg-red-600 text-white rounded flex items-center gap-2"
            :disabled="isDeleting">

            <Loader2Icon v-if="isDeleting" class="animate-spin w-4 h-4" />

            <span>
              {{ isDeleting ? "Deleting..." : "Delete" }}
            </span>
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
import { CheckCircle, CircleX, PlusCircle, TriangleAlert, ChevronDownIcon, User2Icon, Users, AlertCircle, Upload } from 'lucide-vue-next'
import Pagination from '@/components/Pagination.vue'

// Flash Message Handling
const page = usePage();
const flash = computed(() => (page.value?.props?.flash || {}));
const flashMessage = ref(null);
const flashType = ref("success");
const flashBox = ref(null);

watch(
  flash,
  (val) => {
    if (val?.success) {
      flashMessage.value = val.success;
      flashType.value = "success";
    } else if (val?.error) {
      flashMessage.value = val.error;
      flashType.value = "error";
    }

    if (flashMessage.value) {
      window.scrollTo({ top: 0, behavior: "smooth" });
      flashBox.value?.scrollIntoView({ behavior: "smooth", block: "start" });
      setTimeout(() => (flashMessage.value = null), 5000);
    }
  },
  { immediate: true, deep: true }
);


const isDeleting = ref(false)


// Import dropdown
const openImport = ref(false)
const importWrapper = ref(null)

const handleClickOutside = (event) => {
  if (importWrapper.value && !importWrapper.value.contains(event.target)) {
    openImport.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  window.removeEventListener('click', handleClickOutside)
})

// Selected members
const selectedMembers = ref([])
const selectAll = ref(false)

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedMembers.value = props.members.data.map(m => m.id)
  } else {
    selectedMembers.value = []
  }
}

// Generate usernames
const isGenerating = ref(false)

const generateUsernames = () => {
  if (!selectedMembers.value.length) {
    flashMessage.value = 'Please select at least one member'
    flashType.value = 'error'
    setTimeout(() => flashMessage.value = null, 5000)
    return
  }

  isGenerating.value = true

  router.post(route('members.assignUsernames'), { member_ids: selectedMembers.value }, {
    onFinish: () => {
      isGenerating.value = false
      selectedMembers.value = []
      selectAll.value = false
    },
    onError: () => {
      flashMessage.value = 'Something went wrong'
      flashType.value = 'error'
    }
  })
}

// More actions dropdown
const openActions = ref(false)
const actionsWrapper = ref(null)

const handleClickOutsideActions = (e) => {
  if (actionsWrapper.value && !actionsWrapper.value.contains(e.target)) {
    openActions.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', handleClickOutsideActions)
})

onBeforeUnmount(() => {
  window.removeEventListener('click', handleClickOutsideActions)
})

// Delete members
const showDeleteConfirm = ref(false)

const confirmDelete = () => {
  if (!selectedMembers.value.length) {
    flashMessage.value = 'Please select members to delete'
    flashType.value = 'error'
    return
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
      }
    }
  )
}



// Member statuses
const statuses = ref(['active', 'inactive', 'suspended', 'pending', 'approved', 'rejected'])
const statusColors = {
  active: 'bg-green-100 text-green-700',
  inactive: 'bg-red-100 text-red-700',
  suspended: 'bg-yellow-100 text-yellow-700',
  pending: 'bg-yellow-100 text-yellow-700',
  approved: 'bg-blue-100 text-blue-700',
  rejected: 'bg-red-100 text-red-700'
}

// Flash messages watcher
watch(flash, (val) => {
  if (val.success) {
    flashMessage.value = val.success
    flashType.value = 'success'
  } else if (val.error) {
    flashMessage.value = val.error
    flashType.value = 'error'
  }

  if (flashMessage.value) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    setTimeout(() => flashMessage.value = null, 5000)
  }
}, { immediate: true, deep: true })

// Props
const props = defineProps({
  members: Object,
  filters: Object,
  stats: Object
})

// Form state & search
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

// Format date
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
