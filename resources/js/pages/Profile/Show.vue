<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Save,
  User,
  Pencil,
  Camera,
  ShieldCheck,
  MapPin,
  Phone,
  Briefcase,
  X,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next'
import { route } from 'ziggy-js'
import { PageProps as InertiaPageProps } from '@inertiajs/core'

/* ---------------- INTERFACES ---------------- */

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



/* ---------------- PAGE ---------------- */

const page = usePage<PageProps>()

/* ---------------- FLASH ---------------- */

const flashVisible = ref(true)

const successMessage = computed(() =>
  flashVisible.value ? page.props.flash?.success || null : null
)

const errorMessage = computed(() =>
  flashVisible.value ? page.props.flash?.error || null : null
)

watch(
  () => [page.props.flash?.success, page.props.flash?.error],
  ([success, error]) => {
    if (success || error) {
      flashVisible.value = true

      setTimeout(() => {
        flashVisible.value = false
      }, 4000)
    }
  },
  { immediate: true }
)

/* ---------------- COMPUTED ---------------- */

const member = computed(() => page.props.member)
const user = computed(() => page.props.user)

const isEditing = ref(false)

const formatDate = (date: string | null | undefined) =>
  date ? new Date(date).toLocaleDateString('en-GB') : '-'

const formatCurrency = (amount: number | null | undefined) =>
  amount
    ? new Intl.NumberFormat('en-KE').format(amount)
    : '0'

/* ---------------- FORM ---------------- */

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
const imageError = ref(false)

/* ---------------- PHOTO ---------------- */

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

/* ---------------- SUBMIT ---------------- */

function submit() {
  form.put(route('member.updateProfile'), {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value = false
    },
  })
}

watch(
  () => form.errors.profile_photo,
  (error) => {
    if (error) {
      setTimeout(() => {
        form.clearErrors('profile_photo')
      }, 4000)
    }
  }
)
</script>

<template>

  <Head title="My Profile" />

  <AppLayout :breadcrumbs="[
    { title: 'My Profile', href: '/member/profile' },
  ]">
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-6 lg:p-8">

      <div class="mx-auto max-w-7xl space-y-6">

        <!-- FLASH -->
        <transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-3"
          enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200"
          leave-to-class="opacity-0 -translate-y-3">
          <div v-if="successMessage || errorMessage" :class="[
    successMessage
      ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
      : 'border-rose-200 bg-rose-50 text-rose-700',
    'flex items-center gap-3 rounded-2xl border px-5 py-4 shadow-sm backdrop-blur-xl',
  ]">
            <component :is="successMessage ? CheckCircle2 : AlertCircle" class="h-5 w-5 shrink-0" />

            <p class="flex-1 text-sm font-medium">
              {{ successMessage || errorMessage }}
            </p>

            <button @click="flashVisible = false" class="rounded-lg p-1 hover:bg-black/5 transition">
              <X class="h-4 w-4" />
            </button>
          </div>
        </transition>

        <!-- HERO -->
        <section
          class="relative rounded-[32px] bg-gradient-to-br from-[#0F172A] via-[#132F57] to-[#1E3A8A] p-6 sm:p-8 shadow-2xl">
          <div class="absolute -top-20 -right-20 h-72 w-72 rounded-full bg-orange-400/20 blur-3xl"></div>

          <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"></div>

          <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

            <!-- LEFT -->
            <div class="flex items-center gap-5">
              <div class="relative group">
                <!-- PREVIEW -->
                <img v-if="previewUrl" :src="previewUrl" alt="Preview"
                  class="h-28 w-28 rounded-3xl object-cover border-4 border-white/20 shadow-2xl" />

                <!-- SAVED IMAGE -->
                <img v-else-if="member.profile_photo && !imageError" :src="`/storage/${member.profile_photo}`"
                  alt="Profile" @error="imageError = true"
                  class="h-24 w-24 rounded-3xl object-cover border-4 border-white/20 shadow-2xl" />

                <!-- DEFAULT AVATAR -->
                <div v-else
                  class="flex h-24 w-24 items-center justify-center rounded-3xl border border-white/20 bg-gradient-to-br from-slate-200/20 to-slate-400/20 backdrop-blur-xl shadow-2xl">
                  <User class="h-10 w-10 text-white/80" />
                </div>

                <!-- Upload -->
                <div class="relative group">
                  <label
                    class="absolute -bottom-2 -right-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-2xl bg-orange-500 text-white shadow-lg transition hover:scale-105 hover:bg-orange-600">
                    <Camera class="h-4 w-4" />
                    <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden" />
                  </label>

                  <!-- Floating Error -->
                  <transition
                    enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 translate-y-2 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition-all duration-200"
                    leave-to-class="opacity-0 scale-95"
                  >
                    <div
                        v-if="form.errors.profile_photo"
                        class="absolute top-full left-[60%] z-50 mt-3 w-64 sm:w-72 -translate-x-0 rounded-2xl border border-rose-300/40 bg-rose-500/90 px-4 py-3 shadow-2xl backdrop-blur-xl"
                      >
                      <div class="flex items-start gap-3">
                        <AlertCircle class="mt-0.5 h-5 w-5 shrink-0 text-rose-100" />

                        <div class="flex-1">
                          <p class="text-xs font-semibold uppercase tracking-wider text-rose-100">
                            Upload Failed
                          </p>

                          <p class="mt-1 text-sm text-white">
                            {{ form.errors.profile_photo }}
                          </p>
                        </div>

                        <button
                          @click="form.clearErrors('profile_photo')"
                          class="rounded-lg p-1 transition hover:bg-white/10"
                        >
                          <X class="h-4 w-4 text-white" />
                        </button>
                      </div>
                    </div>
                  </transition>
                </div>
              </div>
          
              <div>
                <div
                  class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-1.5 backdrop-blur">
                  <ShieldCheck class="h-4 w-4 text-emerald-400" />

                  <span class="text-xs font-medium text-white">
                    Verified <span class="max-sm:hidden">Member</span>
                  </span>
                </div>

                <h1 class="mt-4 text-2xl font-bold tracking-tight text-white">
                  {{ member.first_name }} {{ member.last_name }}
                </h1>

                <p class="mt-2 text-sm text-slate-300">
                  Membership ID:
                  <span class="font-semibold text-white">
                    {{ member.membership_id }}
                  </span>
                </p>
              </div>
            </div>

            <!-- ACTIONS -->
            <div class="flex flex-wrap gap-3">
              <Button v-if="!isEditing" @click="isEditing = true"
                class="h-12 rounded-2xl bg-white text-sm text-slate-900 hover:bg-slate-100 px-5 font-semibold shadow-xl">
                <Pencil class="mr-2 h-4 w-4" />
                Edit Profile
              </Button>

              <template v-if="isEditing">
                <Button @click="isEditing = false"
                  class="h-12 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white px-5">
                  Cancel
                </Button>

                <Button @click="submit" :disabled="form.processing"
                  class="h-12 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white px-5 font-semibold shadow-xl">
                  <Save class="mr-2 h-4 w-4" />

                  {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
              </template>
            </div>
          </div>
        </section>

        <!-- CONTENT -->
        <div class="grid gap-6 xl:grid-cols-[360px,1fr]">

          <!-- SIDEBAR -->
          <div class="space-y-6">

            <!-- INFO CARD -->
            <div class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">

              <h2 class="text-lg font-bold text-slate-900">
                Profile Overview
              </h2>

              <div class="mt-6 space-y-4 sm:flex sm:gap-6">

                <div v-for="info in [
    { label: 'Username', value: user.username || 'N/A' },
    { label: 'ID Number', value: member.id_number || '-' },
    { label: 'Date of Birth', value: formatDate(member.date_of_birth) },
    { label: 'Gender', value: member.gender || '-' },
    { label: 'Membership Status', value: member.membership_status || '-' },
    { label: 'Joined On', value: formatDate(member.membership_date) },
  ]" :key="info.label" class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                  <p class="text-xs uppercase tracking-wide text-slate-500">
                    {{ info.label }}
                  </p>

                  <p class="mt-1 font-semibold text-slate-800">
                    {{ info.value }}
                  </p>
                </div>

              </div>
            </div>

            <!-- QUICK STATS -->
            <div class="rounded-[30px] bg-gradient-to-br from-orange-400 to-orange-500 p-6 text-white shadow-xl">
              <div class="flex items-center gap-3">
                <Briefcase class="h-6 w-6" />

                <h3 class="text-lg font-bold ">
                  Employment
                </h3>
              </div>

              <div class="mt-5 space-y-4">
                <div>
                  <p class="text-sm text-orange-100">
                    Occupation
                  </p>

                  <p class="font-semibold">
                    {{ member.occupation || 'Not Provided' }}
                  </p>
                </div>

                <div>
                  <p class="text-sm text-orange-100">
                    Monthly Income
                  </p>

                  <p class="font-semibold">
                    KES {{ formatCurrency(member.monthly_income) }}
                  </p>
                </div>
              </div>
            </div>

          </div>

          <!-- MAIN FORM -->
          <div class="rounded-[30px] border border-slate-200 bg-white shadow-sm overflow-hidden">

            <!-- HEADER -->
            <div class="border-b border-slate-100 px-6 py-5">
              <h2 class="text-2xl font-bold text-slate-900">
                Personal Information
              </h2>

              <p class="mt-1 text-sm text-slate-500">
                Manage your personal details and emergency contacts
              </p>
            </div>

            <!-- FORM -->
            <div class="space-y-10 p-6">

              <!-- BASIC -->
              <section>
                <div class="mb-5 flex items-center gap-2">
                  <User class="h-5 w-5 text-blue-600" />

                  <h3 class="text-lg font-semibold text-slate-900">
                    Basic Information
                  </h3>
                </div>

                <div class="grid gap-5 md:grid-cols-2">

                  <template v-for="field in [
    { key: 'first_name', label: 'First Name', disabled: true },
    { key: 'last_name', label: 'Last Name', disabled: true },
    { key: 'middle_name', label: 'Middle Name' },
    { key: 'email', label: 'Email', disabled: true },
    { key: 'phone', label: 'Phone', disabled: true },
    { key: 'occupation', label: 'Occupation' },
    { key: 'employer', label: 'Employer' },
    { key: 'monthly_income', label: 'Monthly Income', type: 'number' },
  ]" :key="field.key">
                    <div>
                      <label class="mb-2 block text-sm font-medium text-slate-700">
                        {{ field.label }}
                      </label>

                      <input v-if="['first_name', 'last_name', 'email', 'phone'].includes(field.key)" :value="field.key === 'email'
      ? user.email
      : field.key === 'phone'
        ? user.phone
        : member[field.key]
    " type="text" disabled class="input-modern bg-slate-100" />

                      <input v-else v-model="form[field.key as keyof typeof form]" :type="field.type || 'text'"
                        :disabled="!isEditing" class="input-modern" />
                    </div>
                  </template>

                  <!-- MARITAL -->
                  <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                      Marital Status
                    </label>

                    <select v-model="form.marital_status" :disabled="!isEditing" class="input-modern">
                      <option value="">Select Status</option>
                      <option value="single">Single</option>
                      <option value="married">Married</option>
                      <option value="divorced">Divorced</option>
                      <option value="widowed">Widowed</option>
                    </select>
                  </div>

                </div>
              </section>

              <!-- ADDRESS -->
              <section>
                <div class="mb-5 flex items-center gap-2">
                  <MapPin class="h-5 w-5 text-orange-500" />

                  <h3 class="text-lg font-semibold text-slate-900">
                    Address Information
                  </h3>
                </div>

                <div class="grid gap-5 md:grid-cols-2">

                  <div v-for="field in [
    { key: 'physical_address', label: 'Physical Address' },
    { key: 'postal_address', label: 'Postal Address' },
    { key: 'city', label: 'City' },
    { key: 'county', label: 'County' },
  ]" :key="field.key">
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                      {{ field.label }}
                    </label>

                    <input v-model="form[field.key as keyof typeof form]" type="text" :disabled="!isEditing"
                      class="input-modern" />
                  </div>

                  <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                      Country
                    </label>

                    <select v-model="form.country" :disabled="!isEditing" class="input-modern">
                      <option value="">Select Country</option>
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

              <!-- EMERGENCY -->
              <section>
                <div class="mb-5 flex items-center gap-2">
                  <Phone class="h-5 w-5 text-emerald-600" />

                  <h3 class="text-lg font-semibold text-slate-900">
                    Emergency Contact
                  </h3>
                </div>

                <div class="grid gap-5 md:grid-cols-2">

                  <div v-for="field in [
    { key: 'emergency_contact_name', label: 'Contact Name' },
    { key: 'emergency_contact_phone', label: 'Contact Phone' },
    { key: 'emergency_contact_relationship', label: 'Relationship' },
  ]" :key="field.key">
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                      {{ field.label }}
                    </label>

                    <input v-model="form[field.key as keyof typeof form]" type="text" :disabled="!isEditing"
                      class="input-modern" />
                  </div>

                </div>
              </section>

            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
.input-modern {
  width: 100%;
  border-radius: 16px;
  border: 1px solid #dbe3ee;
  background: white;
  padding: 0.85rem 1rem;
  font-size: 0.95rem;
  color: #0f172a;
  transition: all 0.2s ease;
  outline: none;
}

.input-modern:focus {
  border-color: #fb923c;
  box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.15);
}

.input-modern:disabled {
  cursor: not-allowed;
  background: #f8fafc;
  color: #64748b;
}
</style>