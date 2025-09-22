<template>
  <AppLayout title="Edit Member">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('members.show', member.id)" class="mr-4">
        <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit Member: {{ member.first_name }} {{ member.last_name }}
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Personal Information -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Personal Information</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Update basic personal details of the member.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Profile Photo -->
                  <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                    <div class="mt-1 flex items-center space-x-5">
                      <div class="flex-shrink-0">
                        <img v-if="photoPreview || member.profile_photo"
                          :src="photoPreview || `/storage/${member.profile_photo}`" alt="Profile preview"
                          class="h-20 w-20 rounded-full object-cover" />
                        <div v-else class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                          <UserIcon class="h-12 w-12 text-gray-400" />
                        </div>
                      </div>
                      <input type="file" ref="photoInput" accept="image/*" class="hidden" @change="handlePhotoUpload" />
                      <button type="button" @click="$refs.photoInput.click()"
                        class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Change photo
                      </button>
                    </div>
                  </div>

                  <!-- Membership ID -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Membership ID</label>
                    <input type="text" :value="member.membership_id" disabled
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm" />
                  </div>

                  <!-- Membership Status -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <span :class="{
          'inline-flex px-3 py-2 text-sm font-semibold rounded-md mt-1': true,
          'bg-green-100 text-green-800': member.membership_status === 'active',
          'bg-red-100 text-red-800': member.membership_status === 'inactive',
          'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended'
        }">
                      {{ member.membership_status }}
                    </span>
                  </div>

                  <!-- First Name -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      First Name *
                    </label>
                    <input v-model="form.first_name" type="text" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.first_name }" />
                    <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
                  </div>

                  <!-- Last Name -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Last Name *
                    </label>
                    <input v-model="form.last_name" type="text" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.last_name }" />
                    <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
                  </div>

                  <!-- Other Names -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Other Names</label>
                    <input v-model="form.other_names" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- Date of Birth -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Date of Birth *
                    </label>
                    <input v-model="form.date_of_birth" type="date" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.date_of_birth }" />
                    <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ errors.date_of_birth }}</p>
                  </div>

                  <!-- Gender -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Gender *
                    </label>
                    <select v-model="form.gender" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.gender }">
                      <option value="">Select Gender</option>
                      <option v-for="(label, value) in genders" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
                  </div>

                  <!-- Marital Status -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Marital Status *
                    </label>
                    <select v-model="form.marital_status" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.marital_status }">
                      <option value="">Select Status</option>
                      <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <p v-if="errors.marital_status" class="mt-1 text-sm text-red-600">{{ errors.marital_status }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Contact Information</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Update contact details and address information.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Email -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Email Address *
                    </label>
                    <input v-model="form.email" type="email" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.email }" />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                  </div>

                  <!-- Phone -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Phone Number *
                    </label>
                    <input v-model="form.phone" type="tel" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.phone }" />
                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                  </div>

                  <!-- Address -->
                  <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input v-model="form.address" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- City -->
                  <div class="col-span-6 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input v-model="form.city" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- State/County -->
                  <div class="col-span-6 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">State/County</label>
                    <input v-model="form.state" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- Postal Code -->
                  <div class="col-span-6 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                    <input v-model="form.postal_code" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Identification -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Identification</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Update identification documents and numbers.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- ID Type -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      ID Type *
                    </label>
                    <select v-model="form.id_type" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.id_type }">
                      <option value="">Select ID Type</option>
                      <option v-for="(label, value) in idTypes" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <p v-if="errors.id_type" class="mt-1 text-sm text-red-600">{{ errors.id_type }}</p>
                  </div>

                  <!-- ID Number -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      ID Number *
                    </label>
                    <input v-model="form.id_number" type="text" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.id_number }" />
                    <p v-if="errors.id_number" class="mt-1 text-sm text-red-600">{{ errors.id_number }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Employment Information -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Employment Information</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Update work and employment details.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Occupation -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Occupation</label>
                    <input v-model="form.occupation" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- Employer -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Employer</label>
                    <input v-model="form.employer" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- Monthly Income -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Monthly Income</label>
                    <input v-model="form.monthly_income" type="number" step="0.01"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>

                  <!-- Employee Number -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Employee Number</label>
                    <input v-model="form.employee_number" type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3">
            <Link :href="route('members.show', member.id)"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
            </Link>
            <button type="submit" :disabled="processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              <span v-if="processing">Updating...</span>
              <span v-else>Update Member</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, reactive } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  ArrowLeftIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

export default {
  components: {
    AppLayout,
    Head,
    Link,
    ArrowLeftIcon,
    UserIcon
  },
  props: {
    member: Object,
    idTypes: Object,
    genders: Object,
    maritalStatuses: Object,
    errors: Object
  },
  setup(props) {
    const photoPreview = ref(null)

    const form = useForm({
      first_name: props.member.first_name || '',
      last_name: props.member.last_name || '',
      other_names: props.member.other_names || '',
      date_of_birth: props.member.date_of_birth || '',
      gender: props.member.gender || '',
      marital_status: props.member.marital_status || '',
      email: props.member.user?.email || '',
      phone: props.member.user?.phone || '',
      address: props.member.address || '',
      city: props.member.city || '',
      state: props.member.state || '',
      postal_code: props.member.postal_code || '',
      id_type: props.member.id_type || '',
      id_number: props.member.id_number || '',
      occupation: props.member.occupation || '',
      employer: props.member.employer || '',
      monthly_income: props.member.monthly_income || '',
      employee_number: props.member.employee_number || '',
      profile_photo: null
    })

    const handlePhotoUpload = (event) => {
      const file = event.target.files[0]
      if (file) {
        form.profile_photo = file
        const reader = new FileReader()
        reader.onload = (e) => {
          photoPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
      }
    }

    const submit = () => {
      form.put(route('members.update', props.member.id))
    }

    return {
      form,
      photoPreview,
      handlePhotoUpload,
      submit,
      processing: form.processing
    }
  }
}
</script>