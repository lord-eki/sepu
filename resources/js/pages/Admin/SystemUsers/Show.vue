<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Trash2, Edit2, ToggleLeft, ArrowLeft } from 'lucide-vue-next'

const page = usePage()

const props = defineProps({
  user: Object,
})

/* Toast */
const toast = ref({ visible: false, message: '', type: 'success' })
watch(() => page.props.flash, (flash: any) => {
  if (flash?.success || flash?.error) {
    toast.value = {
      visible: true,
      message: flash.success || flash.error,
      type: flash.success ? 'success' : 'error',
    }
    setTimeout(() => (toast.value.visible = false), 3500)
  }
}, { immediate: true })

const deleteConfirm = ref(false)
const processingToggle = ref(false)
const processingDelete = ref(false)

const toggleStatus = () => {
  if (props.user.id === page.props.auth.user?.id) return
  processingToggle.value = true
  router.patch(route('system-users.toggle-status', props.user.id), {}, {
    preserveState: true,
    onFinish: () => (processingToggle.value = false),
  })
}

const doDelete = () => {
  processingDelete.value = true
  router.delete(route('system-users.destroy', props.user.id), {
    preserveState: true,
    onFinish: () => (processingDelete.value = false),
  })
}

const fmtDateTime = (d: string) => d ? new Date(d).toLocaleString() : '—'
const fmtCurrency = (v: number) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(Number(v || 0))
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'System Users', href: route('system-users.index') },
    { title: 'View User' }
  ]">
    <Head title="System User" />

    <!-- Toast -->
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0 scale-95">
      <div v-if="toast.visible" class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md">
        <div :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'" class="text-white px-5 py-4 rounded-2xl shadow-xl">
          {{ toast.message }}
        </div>
      </div>
    </transition>

    <div class="max-w-7xl mx-auto px-6 py-6 space-y-6 animate-fadeIn">

      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-[#0B1F3A] dark:text-white">{{ user.name }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-300">{{ user.email }} • {{ user.phone }}</p>
          <div class="mt-3 flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-700 capitalize">{{ user.role.replace('_',' ') }}</span>
            <span v-if="user.is_active" class="px-3 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700">Active</span>
            <span v-else class="px-3 py-1 rounded-full text-xs bg-rose-100 text-rose-700">Inactive</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('system-users.index')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-800 dark:text-white">
            <ArrowLeft class="h-4 w-4" /> Back
          </Link>
          <Link :href="route('system-users.edit', user.id)" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B1F3A] text-white">
            <Edit2 class="h-4 w-4" /> Edit
          </Link>
          <button @click="toggleStatus" :disabled="processingToggle" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-600 text-white">
            <ToggleLeft class="h-4 w-4" /> {{ user.is_active ? 'Deactivate' : 'Activate' }}
          </button>
          <button @click="deleteConfirm = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 text-white">
            <Trash2 class="h-4 w-4" /> Remove Role
          </button>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile -->
        <div class="rounded-3xl bg-white dark:bg-[#0B1F3A] shadow border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Account Created</p>
            <p class="font-medium">{{ fmtDateTime(user.created_at) }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Last Login</p>
            <p class="font-medium">{{ fmtDateTime(user.last_login_at) }}</p>
          </div>
          <div class="border-t pt-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Contact</p>
            <p>{{ user.phone }}</p>
            <p>{{ user.email }}</p>
          </div>
        </div>

        <!-- Loans -->
        <div class="lg:col-span-2 rounded-3xl bg-white dark:bg-[#0B1F3A] shadow border border-gray-100 dark:border-gray-800 p-6">
          <h3 class="text-lg font-semibold mb-4">Recent Loans</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-4 py-2">#</th>
                  <th class="px-4 py-2">Product</th>
                  <th class="px-4 py-2">Amount</th>
                  <th class="px-4 py-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="loan in user.loans || []" :key="loan.id" class="border-b hover:bg-orange-50 dark:hover:bg-gray-800">
                  <td class="px-4 py-2">#{{ loan.id }}</td>
                  <td class="px-4 py-2">{{ loan.loan_product?.name || '—' }}</td>
                  <td class="px-4 py-2">{{ fmtCurrency(loan.amount) }}</td>
                  <td class="px-4 py-2">
                    <span class="px-2 py-1 rounded-full text-xs" :class="loan.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700'">{{ loan.status }}</span>
                  </td>
                </tr>
                <tr v-if="!(user.loans?.length)">
                  <td colspan="4" class="text-center py-6 text-gray-400">No loans available</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="deleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-semibold">Confirm Action</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">This removes system access only. Records remain intact.</p>
          <div class="mt-6 flex justify-end gap-3">
            <button @click="deleteConfirm = false" class="px-4 py-2 rounded-xl border">Cancel</button>
            <button @click="doDelete" :disabled="processingDelete" class="px-4 py-2 rounded-xl bg-rose-600 text-white">
              {{ processingDelete ? 'Processing...' : 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn { animation: fadeIn 0.45s ease-out; }
</style>
