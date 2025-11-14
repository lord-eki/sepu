<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Save, User, Pencil } from 'lucide-vue-next'
import { route } from 'ziggy-js'

import { PageProps as InertiaPageProps } from '@inertiajs/core'



//  Define interfaces FIRST
interface Member {
  id: number
  membership_id: string
  first_name: string
  last_name: string
  email: string
  phone: string
  middle_name?: string | null
  id_number?: string | null
  date_of_birth?: string | null
  gender?: string | null
  marital_status?: string | null
  occupation?: string | null
  employer?: string | null
  monthly_income?: number | null
  physical_address?: string | null
  postal_address?: string | null
  city?: string | null
  county?: string | null
  country?: string | null
  emergency_contact_name?: string | null
  emergency_contact_phone?: string | null
  emergency_contact_relationship?: string | null
  membership_status?: string | null
  membership_date?: string | null
  profile_photo?: string | null
}

interface User {
  id: number
  name: string
  email: string
  phone?: string
  username?: string
}

interface PageProps extends InertiaPageProps {
  auth: InertiaPageProps['auth'] & {
    user: {
      id: number
      name: string
      email: string
      role?: string
    }
  }
  member: Member
  user: User
  flash?: {
    success?: string
    error?: string
  }
}

// ✅ usePage with type
const page = usePage<PageProps>()


// ✅ Flash messages
const flashMessage = ref<string | null>(null)
const flashType = ref<'success' | 'error'>('success')

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      flashMessage.value = flash.success
      flashType.value = 'success'
    } else if (flash?.error) {
      flashMessage.value = flash.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

// ✅ Computed props
const member = computed(() => page.props.member)
const user = computed(() => page.props.user)

const isEditing = ref(false)

const formatDate = (date: string | null | undefined) =>
  date ? new Date(date).toLocaleDateString('en-GB') : '-'

// ✅ Form setup
const form = useForm({
  middle_name: member.value.middle_name,
  occupation: member.value.occupation,
  employer: member.value.employer,
  monthly_income: member.value.monthly_income,
  physical_address: member.value.physical_address,
  postal_address: member.value.postal_address,
  city: member.value.city,
  county: member.value.county,
  country: member.value.country,
  emergency_contact_name: member.value.emergency_contact_name,
  emergency_contact_phone: member.value.emergency_contact_phone,
  emergency_contact_relationship: member.value.emergency_contact_relationship,
  marital_status: member.value.marital_status
    ? member.value.marital_status.toLowerCase().trim()
    : '',
  profile_photo: null as File | null,
})

const previewUrl = ref<string | null>(null)

function handlePhotoUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    form.profile_photo = file
    previewUrl.value = URL.createObjectURL(file)
    form.post(route('member.updatePhoto'), {
      forceFormData: true,
      preserveScroll: true,
    })
  }
}

function submit() {
  form.put(route('member.updateProfile'), {
    onSuccess: () => (isEditing.value = false),
  })

}


</script>


<template>

  <Head title="My Profile" />

  <AppLayout :breadcrumbs="[{ title: 'My Profile', href: '/member/profile' }]">
    <div class="p-6 md:p-10 bg-gradient-to-b from-slate-50 to-white min-h-screen">

      <!-- Flash Message -->
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-800'
      : 'bg-red-50 border border-red-200 text-red-800',
    'max-w-3xl mx-auto px-6 py-3 rounded-xl flex items-center shadow-sm mb-8 backdrop-blur-sm',
  ]">
          <span class="flex-1 font-medium">{{ flashMessage }}</span>
          <button type="button" class="ml-3 text-gray-500 hover:text-gray-700" @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>

      <!-- Content Grid -->
      <div class="w-full mx-auto grid grid-cols-1 lg:grid-cols-3 md:gap-8 sm:p-2">
        <!-- Profile Card -->
        <div
          class="bg-white/90 backdrop-blur-xl rounded-2xl max-sm:mb-6 border border-slate-100 shadow-lg hover:shadow-xl transition-all duration-300">
          <div class="bg-gradient-to-r from-blue-900 to-indigo-800 p-8 text-center text-white rounded-t-2xl">
            <div class="relative group">
              <img v-if="previewUrl" :src="previewUrl" alt="Preview"
                class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-white shadow-md transition-transform group-hover:scale-105" />
              <img v-else-if="member.profile_photo" :src="`/storage/${member.profile_photo}`" alt="Profile"
                class="w-28 h-28 mx-auto rounded-full object-cover border-4 border-white shadow-md transition-transform group-hover:scale-105" />
              <div v-else
                class="w-28 h-28 mx-auto bg-white/20 flex items-center justify-center rounded-full border-4 border-white shadow-md">
                <User class="h-12 w-12 text-white/80" />
              </div>

              <label
                class="absolute -bottom-1 ml-10 left-1/2 -translate-x-1/2 flex items-center justify-center shadow-md hover:shadow-lg hover:scale-105 transition-all cursor-pointer"
                title="Edit Photo">
                <div class="relative rounded-sm p-1">
                  <Pencil class="w-4 h-4 text-white z-10" />
                  <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden" />
                  <div
                    class="absolute bottom-0 left-1/2 -translate-x-1/2 h-0.5 w-5 bg-amber-400 rounded-b-lg border-t border-orange-300 shadow-sm">
                  </div>
                </div>
              </label>
            </div>

            <h2 class="mt-4 text-xl font-semibold">
              {{ member.first_name }} {{ member.last_name }}
            </h2>
            <p class="text-sm text-blue-100">M/ship ID: {{ member.membership_id }}</p>
          </div>

          <!-- Profile Details -->
          <div class="p-6 space-y-4">
            <div v-for="info in [
    { label: 'Username', value: user.username || 'N/A' },
    { label: 'ID Number', value: member.id_number || '-' },
    { label: 'Date of Birth', value: formatDate(member.date_of_birth) },
    { label: 'Gender', value: member.gender || '-' },
    { label: 'Membership Status', value: member.membership_status },
    { label: 'Joined On', value: formatDate(member.membership_date) },
  ]" :key="info.label" class="p-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg transition-colors">
              <label class="text-xs text-slate-600 font-medium">{{ info.label }}</label>
              <p class="mt-1 font-semibold text-slate-800">{{ info.value }}</p>
            </div>
          </div>
        </div>

        <!-- Editable Form -->
        <div
          class="col-span-2 bg-white/90 backdrop-blur-xl rounded-2xl border border-slate-100 shadow-lg p-8 transition-all duration-300">
          <div class="flex flex-wrap justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-blue-900">Personal Information</h3>
            <div class="flex gap-3">
              <Button v-if="!isEditing" @click="isEditing = true"
                class="bg-blue-900 hover:bg-blue-800 text-white rounded-md px-4 py-2 flex items-center gap-2 shadow-sm">
                <Pencil class="w-4 h-4" /> Edit
              </Button>
              <Button v-if="isEditing" @click="isEditing = false"
                class="bg-gray-400 hover:bg-gray-500 text-white rounded-md px-4 py-2">
                Cancel
              </Button>
              <Button v-if="isEditing" type="button" @click="submit"
                class="bg-orange-500 hover:bg-orange-600 text-white rounded-md px-4 py-2 flex items-center gap-2">
                <Save class="w-4 h-4" /> Save
              </Button>
            </div>
          </div>

          <!-- Inputs -->
          <div class="space-y-10">
            <!-- Personal Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <template v-for="field in [
    { key: 'first_name', label: 'First Name', disabled: true },
    { key: 'last_name', label: 'Last Name', disabled: true },
    { key: 'middle_name', label: 'Middle Name' },
    { key: 'email', label: 'Email', disabled: true },
    { key: 'phone', label: 'Phone', disabled: true },
    { key: 'marital_status', label: 'Marital Status', type: 'select' },
    { key: 'occupation', label: 'Occupation' },
    { key: 'employer', label: 'Employer' },
    { key: 'monthly_income', label: 'Monthly Income', type: 'number' },
  ]" :key="field.key">
                <div>
                  <label class="block text-sm font-medium mb-1 text-slate-700">{{ field.label }}</label>

                  <input v-if="['first_name', 'last_name', 'email', 'phone'].includes(field.key)" :value="field.key === 'email' ? user.email :
    field.key === 'phone' ? user.phone :
      member[field.key]" type="text" class="w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 shadow-sm"
                    disabled />

                  <select v-else-if="field.key === 'marital_status'" v-model="form.marital_status"
                    class="w-full rounded-lg border border-slate-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                    :disabled="!isEditing">
                    <option value="" disabled>Select status</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="divorced">Divorced</option>
                    <option value="widowed">Widowed</option>
                  </select>

                  <input v-else v-model="form[field.key as keyof typeof form]" :type="field.type || 'text'"
                    class="w-full rounded-lg border border-slate-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200 bg-white"
                    :disabled="!isEditing" />
                </div>
              </template>
            </div>

            <!-- Address -->
            <section>
              <h3 class="text-lg font-semibold text-blue-900 mb-4">Address</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="field in [
    { key: 'physical_address', label: 'Physical Address' },
    { key: 'postal_address', label: 'Postal Address' },
    { key: 'city', label: 'City' },
    { key: 'county', label: 'County' },
  ]" :key="field.key">
                  <label class="block text-sm font-medium mb-1 text-slate-700">{{ field.label }}</label>
                  <input v-model="form[field.key as keyof typeof form]" type="text"
                    class="w-full rounded-lg border border-slate-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                    :disabled="!isEditing" />
                </div>

                <div>
                  <label class="block text-sm font-medium mb-1 text-slate-700">Country</label>
                  <select v-model="form.country"
                    class="w-full rounded-lg border border-slate-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                    :disabled="!isEditing">
                    <option value="" disabled>Select country</option>
                    <option value="Kenya">Kenya</option>
                    <option value="Uganda">Uganda</option>
                    <option value="Tanzania">Tanzania</option>
                    <option value="Rwanda">Rwanda</option>
                    <option value="Burundi">Burundi</option>
                    <option value="South Sudan">South Sudan</option>
                  </select>
                </div>
              </div>
            </section>

            <!-- Emergency -->
            <section>
              <h3 class="text-lg font-semibold text-blue-900 mb-4">Emergency Contact</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="field in [
    { key: 'emergency_contact_name', label: 'Contact Name' },
    { key: 'emergency_contact_phone', label: 'Contact Phone' },
    { key: 'emergency_contact_relationship', label: 'Relationship' },
  ]" :key="field.key">
                  <label class="block text-sm font-medium mb-1 text-slate-700">{{ field.label }}</label>
                  <input v-model="form[field.key as keyof typeof form]" type="text"
                    class="w-full rounded-lg border border-slate-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                    :disabled="!isEditing" />
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>


<style>
.inputsborder input,
select {
  border: 1px solid #cbd5e1;
}
</style>
