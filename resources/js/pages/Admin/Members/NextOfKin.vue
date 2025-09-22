<template>
  <AppLayout :title="`${member.first_name} ${member.last_name} - Next of Kin`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link :href="route('members.show', member.id)" class="mr-4">
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ member.first_name }} {{ member.last_name }} - Next of Kin
            </h2>
            <p class="text-sm text-gray-600">Member ID: {{ member.membership_id }}</p>
          </div>
        </div>
        <div class="flex space-x-2" v-if="canManage">
          <button
            @click="showAddModal = true"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Add Next of Kin
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Member Info Card -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-6 py-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-12 w-12">
                <img
                  v-if="member.profile_photo"
                  :src="`/storage/${member.profile_photo}`"
                  :alt="member.first_name"
                  class="h-12 w-12 rounded-full object-cover"
                />
                <div
                  v-else
                  class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center"
                >
                  <UserIcon class="h-8 w-8 text-gray-600" />
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">
                  {{ member.first_name }} {{ member.last_name }}
                </h3>
                <p class="text-sm text-gray-500">{{ member.membership_id }}</p>
              </div>
              <div class="ml-auto">
                <span
                  :class="{
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                    'bg-green-100 text-green-800': member.membership_status === 'active',
                    'bg-red-100 text-red-800': member.membership_status === 'inactive',
                    'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended'
                  }"
                >
                  {{ member.membership_status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Next of Kin List -->
        <div class="space-y-6" v-if="nextOfKin.length > 0">
          <div
            v-for="(kin, index) in nextOfKin"
            :key="kin.id"
            class="bg-white shadow rounded-lg overflow-hidden"
          >
            <div class="px-6 py-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                      <UsersIcon class="h-6 w-6 text-indigo-600" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ kin.name }}</h3>
                    <p class="text-sm text-gray-500">{{ kin.relationship }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2" v-if="canManage">
                  <button
                    @click="editNextOfKin(kin)"
                    class="text-indigo-600 hover:text-indigo-500"
                  >
                    <PencilIcon class="h-5 w-5" />
                  </button>
                  <button
                    @click="deleteNextOfKin(kin)"
                    class="text-red-600 hover:text-red-500"
                  >
                    <TrashIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kin.phone || 'Not provided' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Email</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kin.email || 'Not provided' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">ID Number</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kin.id_number || 'Not provided' }}</dd>
                </div>
                <div class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Address</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kin.address || 'Not provided' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kin.date_of_birth ? formatDate(kin.date_of_birth) : 'Not provided' }}</dd>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No next of kin</h3>
          <p class="mt-1 text-sm text-gray-500">No next of kin information has been added for this member.</p>
          <div class="mt-6" v-if="canManage">
            <button
              @click="showAddModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              Add Next of Kin
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Next of Kin Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModals"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <form @submit.prevent="submitForm">
            <div>
              <div class="mt-3 text-center sm:mt-0 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  {{ editingKin ? 'Edit Next of Kin' : 'Add Next of Kin' }}
                </h3>
                <div class="mt-6 space-y-4">
                  <!-- Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Full Name *
                    </label>
                    <input
                      v-model="kinForm.name"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.name }"
                    />
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                  </div>

                  <!-- Relationship -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Relationship *
                    </label>
                    <select
                      v-model="kinForm.relationship"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.relationship }"
                    >
                      <option value="">Select Relationship</option>
                      <option value="spouse">Spouse</option>
                      <option value="parent">Parent</option>
                      <option value="child">Child</option>
                      <option value="sibling">Sibling</option>
                      <option value="relative">Relative</option>
                      <option value="friend">Friend</option>
                      <option value="other">Other</option>
                    </select>
                    <p v-if="errors.relationship" class="mt-1 text-sm text-red-600">{{ errors.relationship }}</p>
                  </div>

                  <!-- Phone -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Phone Number
                    </label>
                    <input
                      v-model="kinForm.phone"
                      type="tel"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.phone }"
                    />
                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                  </div>

                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Email Address
                    </label>
                    <input
                      v-model="kinForm.email"
                      type="email"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                  </div>

                  <!-- ID Number -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      ID Number
                    </label>
                    <input
                      v-model="kinForm.id_number"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.id_number }"
                    />
                    <p v-if="errors.id_number" class="mt-1 text-sm text-red-600">{{ errors.id_number }}</p>
                  </div>

                  <!-- Date of Birth -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Date of Birth
                    </label>
                    <input
                      v-model="kinForm.date_of_birth"
                      type="date"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.date_of_birth }"
                    />
                    <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ errors.date_of_birth }}</p>
                  </div>

                  <!-- Address -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Address
                    </label>
                    <textarea
                      v-model="kinForm.address"
                      rows="3"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.address }"
                    ></textarea>
                    <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="processing"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ processing ? 'Saving...' : (editingKin ? 'Update' : 'Add') }}
              </button>
              <button
                type="button"
                @click="closeModals"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  ArrowLeftIcon,
  PlusIcon,
  UserIcon,
  UsersIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

export default {
  components: {
    AppLayout,
    Head,
    Link,
    ArrowLeftIcon,
    PlusIcon,
    UserIcon,
    UsersIcon,
    PencilIcon,
    TrashIcon
  },
  props: {
    member: Object,
    nextOfKin: Array,
    errors: Object
  },
  setup(props) {
    const showAddModal = ref(false)
    const showEditModal = ref(false)
    const editingKin = ref(null)
    const processing = ref(false)

    const kinForm = reactive({
      name: '',
      relationship: '',
      phone: '',
      email: '',
      id_number: '',
      date_of_birth: '',
      address: ''
    })

    const canManage = computed(() => {
      const userRole = window.Laravel?.user?.role
      return ['admin', 'management', 'loan_officer'].includes(userRole)
    })

    const resetForm = () => {
      kinForm.name = ''
      kinForm.relationship = ''
      kinForm.phone = ''
      kinForm.email = ''
      kinForm.id_number = ''
      kinForm.date_of_birth = ''
      kinForm.address = ''
    }

    const editNextOfKin = (kin) => {
      editingKin.value = kin
      kinForm.name = kin.name || ''
      kinForm.relationship = kin.relationship || ''
      kinForm.phone = kin.phone || ''
      kinForm.email = kin.email || ''
      kinForm.id_number = kin.id_number || ''
      kinForm.date_of_birth = kin.date_of_birth || ''
      kinForm.address = kin.address || ''
      showEditModal.value = true
    }

    const deleteNextOfKin = (kin) => {
      if (confirm('Are you sure you want to delete this next of kin?')) {
        router.delete(route('members.destroy-next-of-kin', [props.member.id, kin.id]), {
          preserveScroll: true
        })
      }
    }

    const closeModals = () => {
      showAddModal.value = false
      showEditModal.value = false
      editingKin.value = null
      resetForm()
    }

    const submitForm = () => {
      processing.value = true

      if (editingKin.value) {
        // Update existing
        router.put(route('members.update-next-of-kin', [props.member.id, editingKin.value.id]), kinForm, {
          preserveScroll: true,
          onSuccess: () => {
            closeModals()
          },
          onFinish: () => {
            processing.value = false
          }
        })
      } else {
        // Create new
        router.post(route('members.store-next-of-kin', props.member.id), kinForm, {
          preserveScroll: true,
          onSuccess: () => {
            closeModals()
          },
          onFinish: () => {
            processing.value = false
          }
        })
      }
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString()
    }

    return {
      showAddModal,
      showEditModal,
      editingKin,
      processing,
      kinForm,
      canManage,
      editNextOfKin,
      deleteNextOfKin,
      closeModals,
      submitForm,
      formatDate
    }
  }
}
</script>