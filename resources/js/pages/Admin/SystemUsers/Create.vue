<template>
  <AppLayout :breadcrumbs="[
    { title: 'System Users', href: route('system-users.index') },
    { title: 'Add User' }
  ]">
    <Head title="Add System User" />

    <!-- Flash Messages -->
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-3"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-3"
        >
        <div>
            <div v-if="$page.props.flash.success" class="mb-4 p-3 rounded-lg text-white bg-green-600">
            {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 p-3 rounded-lg text-white bg-red-600">
            {{ $page.props.flash.error }}
            </div>
        </div>
        </transition>


    <!-- Title -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-[#0B1F3A] dark:text-white">Add System User</h1>
      <Link
        :href="route('system-users.index')"
        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition"
      >
        Back
      </Link>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-[#0B1F3A] rounded-2xl shadow p-6 border border-gray-100 dark:border-gray-700 max-w-3xl mx-auto">
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Name -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Enter full name"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          />
          <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="Enter email address"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          />
          <p v-if="form.errors.email" class="text-sm text-red-500 mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Phone -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Phone Number</label>
          <input
            v-model="form.phone"
            type="text"
            placeholder="e.g. 0712345678"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          />
          <p v-if="form.errors.phone" class="text-sm text-red-500 mt-1">{{ form.errors.phone }}</p>
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">User Role</label>
          <select
            v-model="form.role"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          >
            <option disabled value="">Select role</option>
            <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
          </select>
          <p v-if="form.errors.role" class="text-sm text-red-500 mt-1">{{ form.errors.role }}</p>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="Enter password"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          />
          <p v-if="form.errors.password" class="text-sm text-red-500 mt-1">{{ form.errors.password }}</p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            placeholder="Confirm password"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          />
        </div>

        <!-- Active Status -->
        <div class="md:col-span-2 flex items-center gap-2 mt-2">
          <input type="checkbox" v-model="form.is_active" id="active" class="rounded text-orange-600 focus:ring-orange-500" />
          <label for="active" class="text-gray-700 dark:text-gray-300">Active</label>
        </div>

        <!-- Submit -->
        <div class="md:col-span-2 flex justify-end mt-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Create User' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  roles: Object,
})

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role: '',
  is_active: true,
})

const submit = () => {
  form.post(route('system-users.store'))
}
</script>
