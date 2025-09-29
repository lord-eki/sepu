<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Save, User, Pencil } from 'lucide-vue-next'
import { route } from 'ziggy-js'

const page = usePage<{ member: Member }>()

const flashMessage = ref(null)
const flashType = ref('success')

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

const member = computed(() => page.props.member)
const user = computed(() => page.props.user)

const isEditing = ref(false)

const formatDate = (date: string | null | undefined) =>
  date ? new Date(date).toLocaleDateString('en-GB') : '-'

const form = useForm({
  first_name: member.value.first_name,
  last_name: member.value.last_name,
  middle_name: member.value.middle_name,
  email: user.value.email,
  phone: user.value.phone,
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
    forceFormData: true,
    onSuccess: () => (isEditing.value = false),
  })
}
</script>

<template>
  <Head title="My Profile" />
  <AppLayout :breadcrumbs="[{ title: 'Member Profile', href: '/member/profile' }]">
    <div class="p-4">
      <!-- Flash -->
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage"
          :class="[
            flashType === 'success'
              ? 'bg-green-100 text-green-800 border border-green-300'
              : 'bg-red-100 text-red-800 border border-red-300',
            'max-w-2xl mx-auto px-6 py-3 rounded-xl flex items-center shadow-sm mb-6',
          ]"
        >
          <span class="flex-1">{{ flashMessage }}</span>
          <button
            type="button"
            class="ml-3 text-gray-500 hover:text-gray-700"
            @click="flashMessage = null"
          >
            ✕
          </button>
        </div>
      </transition>

      <!-- Layout -->
      <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 lg:gap-6">
        <!-- Profile Card -->
        <div
          class="col-span-1 max-lg:mb-4 bg-white rounded-2xl shadow border border-gray-100 overflow-hidden"
        >
          <!-- Profile header -->
          <div
            class="bg-blue-50 p-6 flex flex-col items-center text-white"
          >
            <img
              v-if="previewUrl"
              :src="previewUrl"
              alt="Preview"
              class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-md"
            />
            <img
              v-else-if="member.profile_photo"
              :src="`/storage/${member.profile_photo}`"
              alt="Profile"
              class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-md"
            />
            <div v-else>
              <User
                class="h-20 w-20 bg-gray-200 p-2 text-gray-400 rounded-full border-4 border-white shadow-md"
              />
            </div>

            <!-- Upload -->
            <label
              class="mt-4 px-3 py-1.5 bg-white text-orange-500 text-sm rounded-lg shadow-md hover:bg-orange-100 cursor-pointer"
            >
              Change Photo
              <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden" />
            </label>

            <h2 class="mt-4 text-xl text-[#081642] font-semibold">
              {{ member.first_name }} {{ member.last_name }}
            </h2>
            <p class="text-sm text-[#081642] opacity-80">M/ship ID: {{ member.membership_id }}</p>
          </div>

          <!-- Static Info -->
          <div class="p-6 space-y-4">
            <div
              v-for="info in [
                { label: 'ID Number', value: member.id_number || '-' },
                { label: 'Date of Birth', value: formatDate(member.date_of_birth) },
                { label: 'Gender', value: member.gender || '-' },
                { label: 'Membership Status', value: member.membership_status },
                { label: 'Joined On', value: formatDate(member.membership_date) },
              ]"
              :key="info.label"
              class="p-3 rounded-lg bg-gray-50 border border-gray-100"
            >
              <label class="text-xs text-[#081642] font-medium">{{ info.label }}</label>
              <p class="mt-1 font-semibold text-gray-800">{{ info.value }}</p>
            </div>
          </div>
        </div>

        <!-- Right Form -->
        <div
          class="col-span-2 bg-white rounded-2xl shadow border border-gray-100 p-6 space-y-8"
        >
          <div class="flex flex-wrap justify-between items-center">
            <h3 class="text-lg font-semibold text-blue-900 ">Personal Info</h3>
            <div class="flex gap-3">
              <Button
                v-if="!isEditing"
                @click="isEditing = true"
                class="bg-blue-900 hover:bg-orange-600 text-white rounded-md px-3 py-2 shadow-sm flex items-center gap-2"
              >
                <Pencil class="w-4 h-4" /> Edit
              </Button>
              <Button
                v-if="isEditing"
                @click="isEditing = false"
                class="bg-gray-500 hover:bg-gray-600 text-white rounded-md px-3 py-2 shadow-sm"
              >
                Cancel
              </Button>
              <Button
                v-if="isEditing"
                type="button"
                @click="submit"
                class="bg-orange-500 hover:bg-orange-600 text-white rounded-md px-3 py-2 shadow-sm flex items-center gap-2"
              >
                <Save class="w-4 h-4" /> <span>Save</span>
              </Button>
            </div>
          </div>

          <!-- Inputs -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="field in [
                { key: 'first_name', label: 'First Name', disabled: true },
                { key: 'last_name', label: 'Last Name', disabled: true },
                { key: 'middle_name', label: 'Middle Name' },
                { key: 'email', label: 'Email', disabled: true },
                { key: 'phone', label: 'Phone', disabled: true },
                { key: 'marital_status', label: 'Marital Status', type: 'select' },
                { key: 'occupation', label: 'Occupation' },
                { key: 'employer', label: 'Employer' },
                { key: 'monthly_income', label: 'Monthly Income', type: 'number' },
              ]"
              :key="field.key"
            >
              <label class="block text-sm font-medium mb-1 text-[#081642]">{{
                field.label
              }}</label>
              <input
                v-if="field.type !== 'select'"
                v-model="form[field.key]"
                :type="field.type || 'text'"
                class="w-full rounded-lg border border-gray-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                :disabled="!isEditing || field.disabled"
              />
              <select
                v-else
                v-model="form.marital_status"
                class="w-full rounded-lg border border-gray-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                :disabled="!isEditing"
              >
                <option value="" disabled>Select status</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="divorced">Divorced</option>
                <option value="widowed">Widowed</option>
              </select>
            </div>
          </div>

          <!-- Address -->
          <h3 class="text-lg font-semibold text-blue-900 border-b pb-1">Address</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="field in [
                { key: 'physical_address', label: 'Physical Address' },
                { key: 'postal_address', label: 'Postal Address' },
                { key: 'city', label: 'City' },
                { key: 'county', label: 'County' },
              ]"
              :key="field.key"
            >
              <label class="block text-sm font-medium mb-1 text-[#081642]">{{
                field.label
              }}</label>
              <input
                v-model="form[field.key]"
                type="text"
                class="w-full rounded-lg border border-gray-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                :disabled="!isEditing"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-[#081642]"
                >Country</label
              >
              <select
                v-model="form.country"
                class="w-full rounded-lg border border-gray-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                :disabled="!isEditing"
              >
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

          <!-- Emergency -->
          <h3 class="text-lg font-semibold text-blue-900 border-b pb-1">
            Emergency Contact
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="field in [
                { key: 'emergency_contact_name', label: 'Contact Name' },
                { key: 'emergency_contact_phone', label: 'Contact Phone' },
                { key: 'emergency_contact_relationship', label: 'Relationship' },
              ]"
              :key="field.key"
            >
              <label class="block text-sm font-medium mb-1 text-[#081642]">{{
                field.label
              }}</label>
              <input
                v-model="form[field.key]"
                type="text"
                class="w-full rounded-lg border border-gray-300 p-2.5 shadow-sm focus:ring focus:ring-orange-200"
                :disabled="!isEditing"
              />
            </div>
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
