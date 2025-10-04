<template>
  <AppLayout title="Create Member">
      <div class="flex items-center">
        <Link :href="route('members.index')" class="mr-4">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Create New Member
        </h2>
      </div>
   

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
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
              ? 'bg-green-50 border border-green-200 text-green-700'
              : 'bg-red-50 border border-red-200 text-red-700',
            'mb-4 rounded-md p-4 shadow flex items-center'
          ]"
        >
          <component
            :is="flashType === 'success' ? CheckCircle : AlertCircle"
            class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'"
          />
          <p class="ml-3 text-sm">{{ flashMessage }}</p>
          <button
            type="button"
            class="ml-auto text-gray-500 hover:text-gray-700"
            @click="flashMessage = null"
          >
            ✕
          </button>
        </div>
      </transition>
    </div>

    <!-- Form -->
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Personal Information -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
                <p class="mt-1 text-sm text-gray-500">Basic personal details of the member.</p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Profile Photo -->
                  <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                    <div class="mt-1 flex items-center space-x-5">
                      <div class="flex-shrink-0">
                        <img
                          v-if="photoPreview"
                          :src="photoPreview"
                          alt="Profile preview"
                          class="h-20 w-20 rounded-full object-cover"
                        />
                        <div
                          v-else
                          class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center"
                        >
                          <User class="h-12 w-12 text-gray-400" />
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
                        class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50"
                      >
                        Change photo
                      </button>
                    </div>
                  </div>

                  <!-- Membership ID -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Membership ID</label>
                    <input
                      type="text"
                      :value="membershipId"
                      disabled
                      class="mt-1 block w-full border-gray-300 rounded-md bg-gray-100 shadow-sm sm:text-sm p-2"
                    />
                  </div>

                  

                  <!-- First Name -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">First Name *</label>
                    <input
                      v-model="form.first_name"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.first_name }"
                    />
                    <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
                  </div>

                  <!-- Last Name -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Last Name *</label>
                    <input
                      v-model="form.last_name"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.last_name }"
                    />
                    <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
                  </div>

                  <!-- Middle Name -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                    <input
                      v-model="form.middle_name"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                    />
                  </div>

                  <!-- Date of Birth -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                    <input
                      v-model="form.date_of_birth"
                      type="date"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.date_of_birth }"
                    />
                    <p v-if="errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ errors.date_of_birth }}</p>
                  </div>

                  <!-- Gender -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Gender *</label>
                    <select
                      v-model="form.gender"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.gender }"
                    >
                      <option value="">Select Gender</option>
                      <option v-for="(label, value) in genders" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
                  </div>

                  <!-- Marital Status -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Marital Status *</label>
                    <select
                      v-model="form.marital_status"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.marital_status }"
                    >
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
                  Contact details and address information.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Email -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Email Address *
                    </label>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                  </div>

                  <!-- Phone -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Phone Number *
                    </label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.phone }"
                    />
                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                  </div>

                  <!-- Address -->
                  <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700">Physical Address</label>
                    <input
                      v-model="form.physical_address"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>

                  <!-- City -->
                  <div class="col-span-6 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input
                      v-model="form.city"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>

                  <!-- County -->
                  <div class="col-span-6 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">County</label>
                    <input
                      v-model="form.county"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>

                  <!-- Postal Code -->
                  <div class="col-span-6 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                    <input
                      v-model="form.postal_code"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
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
                  Official identification documents and numbers.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- ID Type -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      ID Type *
                    </label>
                    <select
                      v-model="form.id_type"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.id_type }"
                    >
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
                    <input
                      v-model="form.id_number"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.id_number }"
                    />
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
                  Work and employment details.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Occupation -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Occupation</label>
                    <input
                      v-model="form.occupation"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>

                  <!-- Employer -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Employer</label>
                    <input
                      v-model="form.employer"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>

                  <!-- Monthly Income -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Monthly Income</label>
                    <input
                      v-model="form.monthly_income"
                      type="number"
                      step="0.01"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>

                  <!-- Employee Number -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Employee Number</label>
                    <input
                      v-model="form.employee_number"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Documents Upload -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Documents</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Upload supporting documents (ID copy, passport photo, etc.)
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="max-w-lg">
                  <input
                    type="file"
                    ref="documentsInput"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    class="hidden"
                    @change="handleDocumentsUpload"
                  />
                  <button
                    type="button"
                    @click="$refs.documentsInput.click()"
                    class="relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    <File class="mx-auto h-12 w-12 text-gray-400" />
                    <span class="mt-2 block text-sm font-medium text-gray-900">
                      Upload documents
                    </span>
                  </button>
                  
                  <!-- Document List -->
                  <div v-if="selectedDocuments.length" class="mt-4">
                    <h4 class="text-sm font-medium text-gray-900">Selected Files:</h4>
                    <ul class="mt-2 border border-gray-200 rounded-md divide-y divide-gray-200">
                      <li v-for="(doc, index) in selectedDocuments" :key="index" class="pl-3 pr-4 py-3 flex items-center justify-between text-sm p-2">
                        <div class="w-0 flex-1 flex items-center">
                          <FileIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                          <span class="ml-2 flex-1 w-0 truncate">{{ doc.name }}</span>
                        </div>
                        <button
                          type="button"
                          @click="removeDocument(index)"
                          class="ml-4 flex-shrink-0 text-indigo-600 hover:text-indigo-500"
                        >
                          Remove
                        </button>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Emergency Contact Section -->
          <div class="col-span-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Emergency Contact</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              
              <!-- Contact Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input
                  v-model="form.emergency_contact_name"
                  type="text"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                />
              </div>

              <!-- Contact Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Phone *</label>
                <input
                  v-model="form.emergency_contact_phone"
                  type="tel"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                />
              </div>

              <!-- Relationship -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Relationship *</label>
                <input
                  v-model="form.emergency_contact_relationship"
                  type="text"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2"
                />
              </div>

            </div>
          </div>


          <!-- Account Setup -->
          <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
              <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Account Setup</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Login credentials for the member portal.
                </p>
              </div>
              <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Password -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Password
                    </label>
                    <input
                      v-model="form.password"
                      type="password"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                      :class="{ 'border-red-300': errors.password }"
                    />
                    <p class="mt-1 text-xs text-gray-500">Leave blank to generate automatically</p>
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                  </div>

                  <!-- Confirm Password -->
                  <div class="col-span-6 sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">
                      Confirm Password
                    </label>
                    <input
                      v-model="form.password_confirmation"
                      type="password"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="route('members.index')"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
            >
              <span v-if="form.processing">Creating...</span>
              <span v-else>Create Member</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { ArrowLeft, File, FileIcon, User, CheckCircle, AlertCircle } from "lucide-vue-next";

// Props
const props = defineProps({
  membershipId: String,
  idTypes: Object,
  genders: Object,
  maritalStatuses: Object,
  errors: Object,
});

// ✅ Flash handling
const page = usePage();
const flash = computed(() => page.props.flash || {});
const flashMessage = ref(null);
const flashType = ref("success");
const flashBox = ref(null);

watch(
  flash,
  (val) => {
    if (val.success) {
      flashMessage.value = val.success;
      flashType.value = "success";
    } else if (val.error) {
      flashMessage.value = val.error;
      flashType.value = "error";
    }

    if (flashMessage.value) {
      flashBox.value?.scrollIntoView({ behavior: "smooth" });
      setTimeout(() => (flashMessage.value = null), 3000);
    }
  },
  { immediate: true, deep: true }
);

// Form state
const photoPreview = ref(null);
const selectedDocuments = ref([]);

const form = useForm({
  first_name: "",
  last_name: "",
  middle_name: "",
  date_of_birth: "",
  gender: "",
  marital_status: "",
  email: "",
  phone: "",
  physical_address: "",
  city: "",
  county: "",
  postal_code: "",
  id_type: "",
  id_number: "",
  occupation: "",
  employer: "",
  monthly_income: "",
  employee_number: "",
  password: "",
  password_confirmation: "",
  profile_photo: null,
  documents: [],
  emergency_contact_name: "",
  emergency_contact_phone: "",
  emergency_contact_relationship: "",
});

// Handlers
const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleDocumentsUpload = (event) => {
  const files = Array.from(event.target.files);
  selectedDocuments.value = [...selectedDocuments.value, ...files];
  form.documents = selectedDocuments.value;
};

const removeDocument = (index) => {
  selectedDocuments.value.splice(index, 1);
  form.documents = selectedDocuments.value;
};

const submit = () => {
  form.post(route("members.store"), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      photoPreview.value = null;
      selectedDocuments.value = [];
    },
    onError: (errors) => {
      console.error("Validation errors:", errors);
    },
  });
};
</script>