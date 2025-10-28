<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ShieldCheck, Users, CheckCircle2, Info, LockKeyhole } from 'lucide-vue-next'

const props = defineProps({
  roles: Array,
  stats: Object,
})
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'System Users', href: route('system-users.index') },
    { title: 'User Roles' }
  ]">
    <Head title="System Roles & Permissions" />

    <div class="max-w-6xl mx-auto py-8 px-6 space-y-10 animate-fadeIn">
      <!-- Header Section -->
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight">
            System Roles & Permissions
          </h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">
            View and understand each role’s access level and responsibilities.
          </p>
        </div>
      </header>

      <!-- Stats Overview -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <Card class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-md">
          <CardHeader class="flex items-center gap-2">
            <Users class="h-5 w-5" />
            <CardTitle>Total System Users</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ stats.total_system_users }}</p>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-orange-500 to-orange-700 text-white shadow-md">
          <CardHeader class="flex items-center gap-2">
            <CheckCircle2 class="h-5 w-5" />
            <CardTitle>Active Users</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ stats.active_users }}</p>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-indigo-500 to-indigo-700 text-white shadow-md">
          <CardHeader class="flex items-center gap-2">
            <ShieldCheck class="h-5 w-5" />
            <CardTitle>Roles Available</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ roles.length }}</p>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-emerald-500 to-emerald-700 text-white shadow-md">
          <CardHeader class="flex items-center gap-2">
            <LockKeyhole class="h-5 w-5" />
            <CardTitle>Access Control</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">Secure</p>
          </CardContent>
        </Card>
      </div>

      <!-- Roles List -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="(role, i) in roles"
          :key="i"
          class="border border-gray-200 dark:border-gray-700 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-200 bg-white/80 dark:bg-gray-900/60 backdrop-blur-sm"
        >
          <CardHeader class="pb-2">
            <div class="flex items-center justify-between">
              <CardTitle class="text-lg font-semibold text-blue-700 dark:text-blue-400">
                {{ role.label }}
              </CardTitle>
              <span
                class="text-xs px-2 py-1 rounded-full"
                :class="{
                  'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300': role.name === 'admin',
                  'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300': role.name === 'management',
                  'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300': role.name === 'loan_officer',
                  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300': role.name === 'accountant'
                }"
              >
                {{ role.count }} users
              </span>
            </div>
          </CardHeader>

          <CardContent class="space-y-3">
            <p class="text-sm text-gray-600 dark:text-gray-300 flex items-start gap-2">
              <Info class="h-4 w-4 text-orange-500 mt-0.5" /> {{ role.description }}
            </p>

            <div>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Permissions:
              </p>
              <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li v-for="(perm, j) in role.permissions" :key="j">
                  {{ perm }}
                </li>
              </ul>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.5s ease-in-out;
}
</style>
