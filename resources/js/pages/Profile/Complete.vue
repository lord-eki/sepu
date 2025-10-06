<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-blue-900 shadow-md">
      <div class="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
          Complete Your Profile
        </h2>
        <Link
          :href="route('logout')"
          method="post"
          class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow transition"
        >
          Logout
        </Link>
      </div>
    </header>

    <!-- Flash messages -->
    <div class="max-w-2xl mx-auto mt-4 px-4">
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
    <main class="py-8 max-lg:px-4">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-8 formprofile">
          <!-- Intro -->
          <div class="bg-white shadow-md border-l-4 border-orange-500 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642] mb-1">Welcome</h3>
            <p class="text-sm text-gray-500">
              Please fill in the required fields to complete your membership profile.
            </p>
          </div>

          <!-- Profile Photo -->
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Profile Photo</h3>
            <p class="text-sm text-gray-500 mb-4">Upload a clear passport-style photo.</p>
            <div class="flex items-center gap-6">
              <div class="flex-shrink-0">
                <img
                  v-if="photoPreview"
                  :src="photoPreview"
                  alt="Profile preview"
                  class="h-20 w-20 rounded-full object-cover ring-2 ring-orange-500"
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
                class="bg-orange-50 hover:bg-orange-100 px-4 py-2 rounded-md text-sm font-medium text-orange-700 border border-orange-200 shadow-sm"
              >
                Change Photo
              </button>
            </div>
          </section>

          <!-- Personal Information -->
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Personal Information</h3>
            <p class="text-sm text-gray-500 mb-6">Your basic details.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- First Name -->
              <div>
                <label class="block text-sm font-medium">First Name</label>
                <input
                  v-model="form.first_name"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Last Name -->
              <div>
                <label class="block text-sm font-medium">Last Name</label>
                <input
                  v-model="form.last_name"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Other Names -->
              <div>
                <label class="block text-sm font-medium">Other Names</label>
                <input
                  v-model="form.middle_name"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- DOB -->
              <div>
                <label class="block text-sm font-medium">Date of Birth</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Gender -->
              <div>
                <label class="block text-sm font-medium">Gender</label>
                <select
                  v-model="form.gender"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
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
                <label class="block text-sm font-medium">Marital Status</label>
                <select
                  v-model="form.marital_status"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
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
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Contact Information</h3>
            <p class="text-sm text-gray-500 mb-6">How we can reach you.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  readonly
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium">Phone</label>
                <input
                  v-model="form.phone"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Physical Address -->
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium">Physical Address</label>
                <input
                  v-model="form.physical_address"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- City -->
              <div>
                <label class="block text-sm font-medium">City</label>
                <input
                  v-model="form.city"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- County -->
              <div>
                <label class="block text-sm font-medium">County</label>
                <input
                  v-model="form.county"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <!-- Postal Address -->
              <div>
                <label class="block text-sm font-medium">Postal Address</label>
                <input
                  v-model="form.postal_address"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Identification -->
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Identification</h3>
            <p class="text-sm text-gray-500 mb-6">Provide a valid ID document.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium">ID Type</label>
                <select
                  v-model="form.id_type"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
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
                <label class="block text-sm font-medium">ID Number</label>
                <input
                  v-model="form.id_number"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Documents -->
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Documents</h3>
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
              class="bg-orange-50 hover:bg-orange-100 px-4 py-2 rounded-md text-sm font-medium text-orange-700 border border-orange-200 shadow-sm"
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
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Employment Information</h3>
            <p class="text-sm text-gray-500 mb-6">Your work details.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium">Occupation</label>
                <input
                  v-model="form.occupation"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Employer</label>
                <input
                  v-model="form.employer"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Employee Number</label>
                <input
                  v-model="form.employee_number"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Monthly Income</label>
                <input
                  v-model="form.monthly_income"
                  type="number"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Emergency Contact -->
          <section class="bg-white shadow-md rounded-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642]">Emergency Contact</h3>
            <p class="text-sm text-gray-500 mb-6">Who we should reach in case of emergency.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium">Name</label>
                <input
                  v-model="form.emergency_contact_name"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium">Phone</label>
                <input
                  v-model="form.emergency_contact_phone"
                  type="tel"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium">Relationship</label>
                <input
                  v-model="form.emergency_contact_relationship"
                  type="text"
                  class="mt-1 block w-full rounded-lg border border-gray-300 focus:ring-orange-500 focus:border-orange-500 shadow-sm sm:text-sm p-2"
                />
              </div>
            </div>
          </section>

          <!-- Submit -->
          <div class="flex justify-end max-sm:mr-5">
            <button
              type="submit"
              :disabled="form.processing || !isFormValid"
              class="inline-flex items-center px-6 py-3 rounded-lg text-white font-medium shadow-md
                     bg-blue-900 hover:bg-orange-600 disabled:bg-gray-300 transition"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Complete Profile</span>
            </button>
          </div>
        </form>

        <!-- Payment Modal -->
        <transition name="fade" class="max-sm:px-2">
          <div
            v-if="showPaymentModal"
            class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
          >
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
              <h3 class="text-lg font-semibold text-[#081642] mb-2">Complete Membership Payment</h3>
              <p class="text-sm text-gray-600 mb-4">
                To proceed with SEPU Sacco membership, kindly make the following minimum payments:
              </p>

              <ul class="text-sm space-y-1 mb-4">
                <li>• Registration Fee: <strong>Kshs. 2,500</strong></li>
                <li>• Minimum Share Capital: <strong>Kshs. 5,000</strong></li>
                <li>• Minimum Share Deposits: <strong>Kshs. 5,000</strong></li>
              </ul>

              <div class="space-y-3">
                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                <select
                  v-model="selectedPaymentMethod"
                  class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-orange-500 focus:border-orange-500"
                >
                  <option value="">Select method</option>
                  <option value="mpesa">Paybill (M-Pesa)</option>
                  <option value="bank">Bank Deposit</option>
                </select>
              </div>

              <div v-if="selectedPaymentMethod === 'mpesa'" class="mt-4 bg-orange-50 p-3 rounded-md text-sm">
                <p><strong>Paybill Number:</strong> 400200</p>
                <p><strong>Account Number:</strong> 01120040146200</p>
              </div>

              <div v-if="selectedPaymentMethod === 'bank'" class="mt-4 bg-blue-50 p-3 rounded-md text-sm">
                <p><strong>Bank Name:</strong> Co-operative Bank</p>
                <p><strong>Account No:</strong> 001234567890</p>
              </div>

              <div class="mt-6 flex justify-end gap-3">
                <button
                  @click="showPaymentModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                >
                  Cancel
                </button>
                <button
                  @click="confirmPayment"
                  class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg"
                >
                  I’ve Paid
                </button>
              </div>
            </div>
          </div>
        </transition>

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
const selectedPaymentMethod = ref('')
const confirmPayment = () => {
  showPaymentModal.value = false
  alert('Payment confirmation received. Admin will verify your payment.')
}

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
  middle_name: '',
  date_of_birth: '',
  gender: '',
  marital_status: '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  physical_address: '',
  city: '',
  county: '',
  postal_address: '',
  id_type: '',
  id_number: '',
  occupation: '',
  employer: '',
  monthly_income: '',
  employee_number: '',
  profile_photo: null,
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
    form.profile_photo = file
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
const showPaymentModal = ref(false)

const submit = () => {
  // Example eligibility check logic
  const registrationFeePaid = false
  const shareCapitalPaid = false
  const shareDepositsPaid = false

  // If all minimums met, complete profile
  if (registrationFeePaid && shareCapitalPaid && shareDepositsPaid) {
    form.post(route('profile.complete.store'), {
      forceFormData: true,
    })
  } else {
    // Otherwise show payment modal
    showPaymentModal.value = true
  }
}


</script>

<style>
.formprofile label {  
  color: #081642ff;
}
button:hover {
  cursor: pointer;
}
</style>