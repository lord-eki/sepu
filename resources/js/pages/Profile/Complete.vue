<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Complete Your Profile</h2>
        <Link
          :href="route('logout')"
          method="post"
          class="bg-gray-700 hover:cursor-pointer hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow transition"
        >
          Logout
        </Link>
      </div>
    </header>

    <!-- Flash messages -->
    <div class="max-w-2xl mx-auto mt-2 sm:mt-6 px-4">
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
            'relative w-full px-6 py-3 rounded-lg mb-4 flex items-center shadow-sm'
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
    </div>

    <!-- Content -->
    <main class="py-5">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-8 formprofile">
          <!-- Intro -->
          <div class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Welcome</h3>
            <p class="text-sm text-gray-500">
              Please fill in the required fields to complete your membership profile.
            </p>
          </div>

          <!-- Profile Photo -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Profile Photo</h3>
            <p class="text-sm text-gray-500 mb-4">
              Upload a clear passport-style photo.
            </p>
            <div class="flex items-center gap-6">
              <div class="flex-shrink-0">
                <img
                  v-if="photoPreview"
                  :src="photoPreview"
                  alt="Profile preview"
                  class="h-20 w-20 rounded-full object-cover ring-2 ring-indigo-500"
                />
                <div
                  v-else
                  class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center"
                >
                  <User class="h-10 w-10 text-gray-400" />
                </div>
              </div>
              <input
                type="file"
                ref="photoInput"
                accept="image/*"
                class="hidden"
                @change="handlePhotoUpload"
              />
              <button
                type="button"
                @click="$refs.photoInput.click()"
                class="bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-md text-sm font-medium text-indigo-700 border border-indigo-200 shadow-sm"
              >
                Change Photo
              </button>
            </div>
          </section>

          <!-- Personal Information -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
            <p class="text-sm text-gray-500 mb-6">Your basic details.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- First Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700">First Name</label>
                <input
                  v-model="form.first_name"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Last Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                <input
                  v-model="form.last_name"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Other Names -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Other Names</label>
                <input
                  v-model="form.other_names"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- DOB -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Gender -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Gender</label>
                <select
                  v-model="form.gender"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                >
                  <option value="">Select Gender</option>
                  <option
                    v-for="(label, value) in genders"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
              </div>
              <!-- Marital -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Marital Status</label>
                <select
                  v-model="form.marital_status"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                >
                  <option value="">Select Status</option>
                  <option
                    v-for="(label, value) in maritalStatuses"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
              </div>
            </div>
          </section>

          <!-- Contact Information -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
            <p class="text-sm text-gray-500 mb-6">How we can reach you.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  readonly
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input
                  v-model="form.phone"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Physical Address -->
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Physical Address</label>
                <input
                  v-model="form.physical_address"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- City -->
              <div>
                <label class="block text-sm font-medium text-gray-700">City</label>
                <input
                  v-model="form.city"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- County -->
              <div>
                <label class="block text-sm font-medium text-gray-700">County</label>
                <input
                  v-model="form.county"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Postal Code -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                <input
                  v-model="form.postal_code"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Identification -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Identification</h3>
            <p class="text-sm text-gray-500 mb-6">Provide a valid ID document.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">ID Type</label>
                <select
                  v-model="form.id_type"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                >
                  <option value="">Select ID Type</option>
                  <option
                    v-for="(label, value) in idTypes"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">ID Number</label>
                <input
                  v-model="form.id_number"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Documents -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Documents</h3>
            <p class="text-sm text-gray-500 mb-4">Upload any supporting documents.</p>
            <input
              type="file"
              multiple
              class="hidden"
              ref="documentsInput"
              @change="handleDocumentsUpload"
            />
            <button
              type="button"
              @click="$refs.documentsInput.click()"
              class="bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-md text-sm font-medium text-indigo-700 border border-indigo-200 shadow-sm"
            >
              Upload Documents
            </button>
            <ul class="mt-4 space-y-2">
              <li
                v-for="(doc, index) in selectedDocuments"
                :key="index"
                class="flex items-center justify-between bg-gray-50 rounded-md p-2 text-sm shadow-sm"
              >
                <span class="flex items-center gap-2">
                  <FileIcon class="w-4 h-4 text-gray-400" /> {{ doc.name }}
                </span>
                <button
                  type="button"
                  @click="removeDocument(index)"
                  class="text-red-500 hover:text-red-700 text-xs"
                >
                  Remove
                </button>
              </li>
            </ul>
          </section>

          <!-- Employment -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Employment Information</h3>
            <p class="text-sm text-gray-500 mb-6">Your work details.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Occupation</label>
                <input
                  v-model="form.occupation"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Employer</label>
                <input
                  v-model="form.employer"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Employee Number</label>
                <input
                  v-model="form.employee_number"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Monthly Income</label>
                <input
                  v-model="form.monthly_income"
                  type="number"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Emergency Contact -->
          <section class="bg-white shadow rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-900">Emergency Contact</h3>
            <p class="text-sm text-gray-500 mb-6">Who we should reach in case of emergency.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input
                  v-model="form.emergency_contact_name"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input
                  v-model="form.emergency_contact_phone"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Relationship</label>
                <input
                  v-model="form.emergency_contact_relationship"
                  type="text"
                  class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>


          <!-- Submit -->
          <div class="flex justify-end max-sm:mr-5">
            <button
              type="submit"
              :disabled="form.processing || !isFormValid"
              class="inline-flex items-center px-6 py-3 rounded-lg hover:cursor-pointer text-white font-medium shadow-sm
                     bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 transition"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Complete Profile</span>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import { User, FileIcon } from 'lucide-vue-next'

const page = usePage()
const flash = computed(() => page.props.flash || {})
const flashMessage = ref(null)
const flashType = ref('success')

watch(flash, (val) => {
  if (val.success) {
    flashMessage.value = val.success
    flashType.value = 'success'
    form.reset()
    photoPreview.value = null
    selectedDocuments.value = []
  } else if (val.error) {
    flashMessage.value = val.error
    flashType.value = 'error'
    form.reset() 
    photoPreview.value = null
    selectedDocuments.value = []
  }
  if (flashMessage.value) {
    setTimeout(() => (flashMessage.value = null), 5000)
  }
})

const props = defineProps({
  user: Object,
  idTypes: Object,
  genders: Object,
  maritalStatuses: Object,
  errors: Object,
})

const firstName = props.user?.name ? props.user.name.split(' ')[0] : ''
const lastName = props.user?.name ? props.user.name.split(' ')[1] ?? '' : ''

const isFormValid = computed(() => {
  return (
    form.first_name &&
    form.last_name &&
    form.date_of_birth &&
    form.gender &&
    form.marital_status &&
    form.physical_address &&
    form.id_type &&
    form.id_number &&
    form.occupation &&
    form.employer &&
    form.monthly_income &&
    form.emergency_contact_name &&
    form.emergency_contact_phone &&
    form.emergency_contact_relationship
  )
})

const form = useForm({
  first_name: firstName,
  last_name: lastName,
  other_names: '',
  date_of_birth: '',
  gender: '',
  marital_status: '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  physical_address: '',
  city: '',
  county: '',
  postal_code: '',
  id_type: '',
  id_number: '',
  occupation: '',
  employer: '',
  monthly_income: '',
  employee_number: '',
  photo: null,
  documents: [],
  emergency_contact_name: '',
  emergency_contact_phone: '',
  emergency_contact_relationship: '',
})

const photoPreview = ref(null)
const handlePhotoUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    photoPreview.value = URL.createObjectURL(file)
    form.photo = file
  }
}

const selectedDocuments = ref([])
const handleDocumentsUpload = (e) => {
  selectedDocuments.value = Array.from(e.target.files)
  form.documents = selectedDocuments.value
}
const removeDocument = (index) => {
  selectedDocuments.value.splice(index, 1)
  form.documents = selectedDocuments.value
}

const submit = () => {
  form.post(route('profile.complete.store'))
}
</script>

<style>
.formprofile label {
  color: #081642ff;
}
</style>