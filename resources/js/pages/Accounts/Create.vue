<template>
  <AppLayout :breadcrumbs="[
    { title: isMemberRole ? 'My Accounts' : 'Accounts', href: isMemberRole ? route('my-accounts') : route('accounts.index') },
    { title: 'Create' }
  ]">
      <h2 class="font-semibold text-lg sm:text-xl pt-5 pl-5 text-blue-900 leading-tight">
        Create New Account
      </h2>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Member Selection -->
              <div>
                <label for="member_id" class="block text-sm font-medium text-gray-700">
                  Member *
                </label>
                <select
                  id="member_id"
                  v-model="form.member_id"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  required
                >
                  <option value="">Select a member</option>
                  <option 
                    v-for="member in members" 
                    :key="member.id" 
                    :value="member.id"
                  >
                    {{ member.name }} ({{ member.membership_id }})
                  </option>
                </select>
                <div v-if="form.errors.member_id" class="mt-2 text-sm text-red-600">
                  {{ form.errors.member_id }}
                </div>
              </div>

              <!-- Account Type -->
              <div>
                <label for="account_type" class="block text-sm font-medium text-gray-700">
                  Account Type *
                </label>
                <select
                  id="account_type"
                  v-model="form.account_type"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  required
                >
                  <option value="">Select account type</option>
                  <option 
                    v-for="(label, value) in accountTypes" 
                    :key="value" 
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
                <div v-if="form.errors.account_type" class="mt-2 text-sm text-red-600">
                  {{ form.errors.account_type }}
                </div>
              </div>

              <!-- Selected Member Info -->
              <div v-if="selectedMember" class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Member Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium">Name:</span> {{ selectedMember.name }}
                  </div>
                  <div>
                    <span class="font-medium">Membership ID:</span> {{ selectedMember.membership_id }}
                  </div>
                  <div>
                    <span class="font-medium">Email:</span> {{ selectedMember.email }}
                  </div>
                </div>
              </div>

              <!-- Account Type Description -->
              <div v-if="form.account_type" class="bg-blue-50 p-4 rounded-md">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                      {{ getAccountTypeDescription(form.account_type) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex items-center justify-between pt-6 border-t">
                <Link :href="isMemberRole
                ? route('my-accounts')
                : route('accounts.index')"
                  class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Creating...' : 'Create Account' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  members: Array,
  accountTypes: Object,
})

const form = useForm({
  member_id: '',
  account_type: '',
})

const selectedMember = computed(() => {
  if (!form.member_id) return null
  return props.members.find(member => member.id == form.member_id)
})

const getAccountTypeDescription = (type) => {
  const descriptions = {
    savings: 'A savings account allows members to save money and earn interest on their deposits.',
    shares: 'A shares account represents ownership in the cooperative and entitles members to dividends.',
    deposits: 'A deposits account offers higher interest rates for fixed-term investments.'
  }
  return descriptions[type] || ''
}

const submit = () => {
  form.post(route('accounts.store'))
}
</script>