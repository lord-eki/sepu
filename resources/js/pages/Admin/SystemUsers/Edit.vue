<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { User, Save, ArrowLeft } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const page = usePage()

const props = defineProps({
  user: Object,
  roles: Object,
})

// Editable fields only
const form = useForm({
  role: props.user.role,
  is_active: props.user.is_active,
})

const saving = ref(false)

// Toast
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

const submit = () => {
  saving.value = true
  form.put(route('system-users.update', props.user.id), {
    preserveScroll: true,
    onFinish: () => (saving.value = false),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'System Users', href: route('system-users.index') },
    { title: 'Edit User' }
  ]">
    <Head title="Edit System User" />

    <!-- Toast -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="toast.visible" class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md">
        <div
          :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'"
          class="text-white px-5 py-4 rounded-2xl shadow-xl"
        >
          {{ toast.message }}
        </div>
      </div>
    </transition>

    <!-- Back -->
    <div class="px-6 pt-6">
      <Link
        :href="route('system-users.index')"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0B1F3A] hover:bg-blue-900 text-white transition shadow"
      >
        <ArrowLeft class="h-4 w-4" /> Back
      </Link>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-8 space-y-8 animate-fadeIn">

      <!-- Header -->
      <header class="text-center space-y-1">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
          Edit System User
        </h1>
        <p class="text-gray-500 dark:text-gray-400">
          Manage role and access permissions
        </p>
      </header>

      <!-- Card -->
      <Card class="rounded-3xl shadow-lg border border-gray-100 dark:border-gray-800 bg-white dark:bg-[#0B1F3A]">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-[#0B1F3A] dark:text-orange-400">
            <User class="h-5 w-5" /> User Details
          </CardTitle>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">

            <!-- Readonly Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Full Name</label>
                <input
                  :value="user.name"
                  readonly
                  class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 dark:text-gray-300 px-3 py-2.5 cursor-not-allowed"
                />
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Email</label>
                <input
                  :value="user.email"
                  readonly
                  class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 dark:text-gray-300 px-3 py-2.5 cursor-not-allowed"
                />
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Phone</label>
                <input
                  :value="user.phone"
                  readonly
                  class="w-full rounded-xl bg-gray-100 dark:bg-gray-800 dark:text-gray-300 px-3 py-2.5 cursor-not-allowed"
                />
              </div>

              <!-- Role -->
              <div>
                <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Role</label>
                <select
                  v-model="form.role"
                  class="w-full rounded-xl px-3 py-2.5 border dark:border-gray-700 dark:bg-[#14294B] dark:text-white focus:ring-2 focus:ring-orange-500"
                >
                  <option v-for="(label, key) in roles" :key="key" :value="key">
                    {{ label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Submit -->
            <div class="pt-6 flex justify-end">
              <Button
                type="submit"
                :disabled="saving"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-orange-500 hover:from-blue-700 hover:to-orange-600 text-white font-semibold shadow transition"
              >
                <Save class="h-4 w-4" />
                {{ saving ? 'Updating...' : 'Update User' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.45s ease-out;
}
</style>
