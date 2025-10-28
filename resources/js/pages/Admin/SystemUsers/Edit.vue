<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { User, ShieldCheck, Save } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps({
  user: Object,
  roles: Object,
})

// ✅ Inertia form setup
const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  phone: props.user.phone || '',
  role: props.user.role || 'loan_officer',
  is_active: props.user.is_active || false,
})

const saving = ref(false)

const submit = () => {
  saving.value = true
  form.put(route('system-users.update', props.user.id), {
    preserveScroll: true,
    onFinish: () => (saving.value = false),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Edit System User" />

    <div class="max-w-4xl mx-auto py-10 px-6 space-y-8 animate-fadeIn">
      <!-- Header -->
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-gray-900 dark:text-white tracking-tight">
            Edit System User
          </h1>
          <p class="text-gray-500 dark:text-gray-400">
            Update account details and system permissions.
          </p>
        </div>
      </header>

      <!-- Edit Form Card -->
      <Card class="backdrop-blur-md bg-white/80 dark:bg-gray-900/60 shadow-md border border-gray-100 dark:border-gray-800">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
            <User class="h-5 w-5" /> User Information
          </CardTitle>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                placeholder="Enter full name"
              />
              <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                placeholder="example@domain.com"
              />
              <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
              <input
                v-model="form.phone"
                type="text"
                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                placeholder="+254..."
              />
              <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
            </div>

            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
              <select
                v-model="form.role"
                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
              >
                <option v-for="(label, key) in roles" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
              <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
            </div>

            <!-- Active Status -->
            <div class="flex items-center space-x-3">
              <input
                id="is_active"
                type="checkbox"
                v-model="form.is_active"
                class="rounded text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600"
              />
              <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">
                Active User
              </label>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex justify-end">
              <Button
                type="submit"
                class="bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 shadow-md transition-all duration-200"
                :disabled="saving"
              >
                <Save class="h-4 w-4" />
                <span>{{ saving ? 'Updating...' : 'Update User' }}</span>
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
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.4s ease-in-out;
}
</style>
