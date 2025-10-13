<template>
  <!-- Loader Overlay -->
  <div v-if="form.processing"
    class="fixed inset-0 bg-gray-800/50 flex flex-col items-center justify-center z-50 cursor-wait">
    <svg class="animate-spin h-10 w-10 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
      viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
    <p class="text-white mt-3 text-sm font-medium">Updating...</p>
  </div>

  <AppLayout :breadcrumbs="[
    { title: 'Members', href: route('members.index') },
    { title: 'Update Member' }
  ]">
    <!-- Header -->
    <div class="flex items-center bg-darkBlue text-white p-4 rounded-md shadow mb-6">
      <Link :href="route('members.index')" class="mr-4 text-orange-400 hover:text-orange-500">
      <ArrowLeft class="w-5 h-5" />
      </Link>
      <h2 class="font-semibold text-lg sm:text-xl">Update Member Details</h2>
    </div>

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" class="flex gap-3" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700',
    'mb-4 rounded-md p-4 shadow flex items-center'
  ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'" />
          <p class="ml-3 text-sm">{{ flashMessage }}</p>
          <button type="button" class="ml-auto text-gray-500 hover:text-gray-700" @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>

    <!-- Form -->
    <div class="pb-10 max-sm:px-4">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Personal Info -->
          <section class="section-card">
            <h3 class="section-title">Personal Information</h3>
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6">
                <label class="label">Profile Photo</label>
                <div class="mt-1 flex items-center space-x-5">
                  <div class="flex-shrink-0">
                    <img v-if="photoPreview" :src="photoPreview" class="h-20 w-20 rounded-full object-cover" />
                    <img v-else-if="member.profile_photo" :src="`/storage/${member.profile_photo}`" alt="Profile"
                      class="w-28 h-28 bg-gray-100 rounded-full object-cover border-4 border-white shadow-md" />
                    <div v-else class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                      <User class="h-12 w-12 text-gray-400" />
                    </div>
                  </div>
                  <input type="file" ref="photoInput" accept="image/*" class="hidden" @change="handlePhotoUpload" />
                  <button type="button" @click="$refs.photoInput.click()" class="btn-outline">
                    Change photo
                  </button>
                </div>
              </div>

              <div class="col-span-6 sm:col-span-3">
                <label class="label">First Name *</label>
                <input v-model="form.first_name" type="text" required class="input" />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <label class="label">Last Name *</label>
                <input v-model="form.last_name" type="text" required class="input" />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <label class="label">Middle Name </label>
                <input v-model="form.middle_name" type="text" class="input" />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <label class="label">Date of Birth *</label>
                <input v-model="form.date_of_birth" type="date" required class="input" />
              </div>


              <div class="col-span-6 sm:col-span-3">
                <label class="label">Gender *</label>
                <select v-model="form.gender" required class="input">
                  <option value="">Select Gender</option>
                  <option v-for="(label, key) in genders" :key="key" :value="key">
                    {{ label }}
                  </option>
                </select>
              </div>

              <div class="col-span-6 sm:col-span-3">
                <label class="label">Marital Status *</label>
                <select v-model="form.marital_status" required class="input">
                  <option value="">Select Status</option>
                  <option v-for="(label, key) in maritalStatuses" :key="key" :value="key">
                    {{ label }}
                  </option>
                </select>
              </div>
            </div>
          </section>

          <!-- Contact Information -->
          <section class="section-card">
            <h3 class="section-title">Contact Information</h3>
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Email *</label>
                <input v-model="form.email" type="email" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Phone *</label>
                <input v-model="form.phone" type="tel" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Physical Address *</label>
                <input v-model="form.physical_address" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Postal Address *</label>
                <input v-model="form.postal_address" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">City *</label>
                <input v-model="form.city" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">County *</label>
                <input v-model="form.county" required class="input" />
              </div>
            </div>
          </section>

          <!-- Employment Details -->
          <section class="section-card">
            <h3 class="section-title">Employment Details</h3>
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Employer *</label>
                <input v-model="form.employer" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Occupation *</label>
                <input v-model="form.occupation" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Monthly Income *</label>
                <input v-model="form.monthly_income" required class="input" />
              </div>
            </div>
          </section>

          <!-- Documents & Identification -->
          <section class="section-card">
            <h3 class="section-title">Documents & Identification</h3>
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-3">
                <label class="label">ID Type *</label>
                <select v-model="form.id_type" required class="input">
                  <option value="">Select ID Type</option>
                  <option v-for="(label, key) in idTypes" :key="key" :value="key">
                    {{ label }}
                  </option>
                </select>
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">ID Number *</label>
                <input v-model="form.id_number" required class="input" />
              </div>
              <div class="col-span-6">
                <label class="label">Upload ID Document</label>
                <input type="file" @change="handleIdUpload" class="input" />
              </div>
            </div>
          </section>

          <!-- Emergency Contact -->
          <section class="section-card">
            <h3 class="section-title">Emergency Contact</h3>
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Name *</label>
                <input v-model="form.emergency_contact_name" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Relationship *</label>
                <input v-model="form.emergency_contact_relationship" required class="input" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Phone *</label>
                <input v-model="form.emergency_contact_phone" required class="input" />
              </div>
            </div>
          </section>

          <!-- Account Information -->
          <section class="section-card">
            <h3 class="section-title">Account Information</h3>
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Member Number</label>
                <input v-model="form.membership_id" disabled class="input bg-gray-100" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <label class="label">Account Status</label>
                <select v-model="form.status" class="input">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </section>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <Link :href="route('members.index')"
              class="bg-gray-200 py-2 px-4 rounded-md shadow-sm text-sm font-medium text-darkBlue hover:bg-gray-300">
            Cancel
            </Link>
            <button type="submit" :disabled="form.processing || !formValid" class="btn-primary">
              <span v-if="form.processing">Updating...</span>
              <span v-else>Update Member</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { ArrowLeft, CheckCircle, AlertCircle, User } from "lucide-vue-next";

const props = defineProps({
  member: Object,
  genders: Object,
  idTypes: Object,
  maritalStatuses: Object,
});

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
      window.scrollTo({ top: 0, behavior: "smooth" });
      flashBox.value?.scrollIntoView({ behavior: "smooth" });
      setTimeout(() => (flashMessage.value = null), 5000);
    }
  },
  { immediate: true, deep: true }
);

const form = useForm({
  first_name: props.member.first_name || "",
  last_name: props.member.last_name || "",
  middle_name: props.member.middle_name || "",
  date_of_birth: props.member.date_of_birth
    ? props.member.date_of_birth.split("T")[0]
    : "",
  gender: props.member.gender || "",
  marital_status: props.member.marital_status || "",
  email: props.member.user?.email || "",
  phone: props.member.user?.phone || "",
  physical_address: props.member.physical_address || "",
  postal_address: props.member.postal_address || "",
  city: props.member.city || "",
  county: props.member.county || "",
  employer: props.member.employer || "",
  occupation: props.member.occupation || "",
  monthly_income: props.member.monthly_income || "",
  id_type: props.member.id_type || "",
  id_number: props.member.id_number || "",
  emergency_contact_name: props.member.emergency_contact_name || "",
  emergency_contact_relationship: props.member.emergency_contact_relationship || "",
  emergency_contact_phone: props.member.emergency_contact_phone || "",
  membership_id: props.member.membership_id || "",
  status: props.member.status || "active",
  profile_photo: null,
  id_document: null,
});

const photoPreview = ref(props.member.profile_photo_url || null);
const handlePhotoUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (e) => (photoPreview.value = e.target.result);
    reader.readAsDataURL(file);
  }
};

const handleIdUpload = (e) => {
  const file = e.target.files[0];
  if (file) form.id_document = file;
};

const formValid = computed(() =>
  [
    "first_name",
    "last_name",
    "date_of_birth",
    "gender",
    "email",
    "phone",
    "physical_address",
    "postal_address",
    "employer",
    "occupation",
    "id_type",
    "id_number",
    "membership_id",
    "emergency_contact_name",
    "emergency_contact_relationship",
    "emergency_contact_phone",
  ].every((f) => form[f])
);

const submit = () => {
  form.put(route("members.update", props.member.id), {
    _method: "PUT",
    preserveScroll: true,
  });
};
</script>

<style scoped>
.section-card {
  background-color: #ffffff;
  border-left: 4px solid #0a2342;
  /* darkBlue */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  padding: 1rem;
  border-radius: 0.5rem;
}

@media (min-width: 640px) {
  .section-card {
    padding: 1.5rem;
  }
}

.section-title {
  font-size: 1.125rem;
  /* text-lg */
  font-weight: 700;
  color: #0a2342;
  /* darkBlue */
  margin-bottom: 1rem;
}

.input {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  border: 1px solid #d1d5db;
  /* gray-300 */
  border-radius: 0.375rem;
  /* rounded-md */
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  font-size: 0.875rem;
  /* text-sm */
  padding: 0.5rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input:focus {
  outline: none;
  border-color: #f97316;
  /* orange-500 */
  box-shadow: 0 0 0 1px #f97316;
}

.label {
  color: #0a2342;
  /* darkBlue */
  font-size: 0.875rem;
  /* text-sm */
  font-weight: 500;
}

.btn-outline {
  background-color: #ffffff;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  /* gray-300 */
  border-radius: 0.375rem;
  font-size: 0.875rem;
  /* text-sm */
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-outline:hover {
  background-color: #f9fafb;
  /* gray-50 */
}

.btn-primary {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 0.5rem 1.5rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  /* text-sm */
  font-weight: 500;
  color: #ffffff;
  background-color: #0a2342;
  transition: background-color 0.2s, opacity 0.2s;
}

.btn-primary:hover {
  background-color: #1e3a8a;
  /* blue-900 */
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.bg-darkBlue {
  background-color: #0a2342;
  /* deep dark blue */
}

.text-darkBlue {
  color: #0a2342;
}
</style>
