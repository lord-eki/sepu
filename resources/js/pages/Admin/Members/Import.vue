<template>
  <!-- Loader Overlay -->
  <div v-if="form.processing"
    class="fixed inset-0 bg-gray-800/50 flex flex-col items-center justify-center z-50 cursor-wait">
    <svg class="animate-spin h-10 w-10 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
      viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
    <p class="text-white mt-3 text-sm font-medium">Processing import...</p>
  </div>

  <AppLayout :breadcrumbs="[
    { title: 'Members', href: route('members.index') },
    { title: 'Import Members' }
  ]">
    <!-- Header -->
    <div class="flex items-center bg-darkBlue max-sm:mx-2 text-white p-4 rounded-md shadow mb-4">
      <Link :href="route('members.index')" class="mr-4 text-orange-400 hover:text-orange-500">
        <ArrowLeft class="w-5 h-5" />
      </Link>
      <h2 class="font-semibold text-lg sm:text-xl">Import Members from Excel</h2>
    </div>

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-4xl mx-auto mt-4 px-4">
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

      <!-- Import Errors -->
      <div v-if="importErrors && importErrors.length > 0" class="bg-yellow-50 border-l-4 border-yellow-400 rounded-md p-4 mb-4 shadow-sm">
        <div class="flex items-start">
          <AlertCircle class="h-6 w-6 text-yellow-600 mt-0.5 flex-shrink-0" />
          <div class="ml-3 flex-1">
            <h3 class="text-base font-bold text-yellow-900">Import Errors - {{ importErrors.length }} row(s) failed</h3>
            <p class="mt-1 text-sm text-yellow-700">Please review and fix the following errors, then re-import the file.</p>
            <div class="mt-3">
              <button 
                @click="showErrors = !showErrors" 
                class="inline-flex items-center px-3 py-1.5 border border-yellow-300 rounded-md text-sm font-medium text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                {{ showErrors ? 'Hide' : 'Show' }} error details
              </button>
            </div>
            <div v-if="showErrors" class="mt-4 max-h-96 overflow-y-auto">
              <div class="space-y-3">
                <div v-for="(error, index) in importErrors" :key="index" 
                  class="bg-white p-4 rounded-md border-l-4 border-red-400 shadow-sm">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-bold text-gray-900">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mr-2">
                          Row {{ error.row }}
                        </span>
                        {{ error.name || 'Unknown Member' }}
                      </p>
                      <div class="mt-2 space-y-1">
                        <div v-for="(msg, idx) in error.errors" :key="idx" 
                          class="flex items-start text-sm text-red-700">
                          <span class="text-red-500 mr-2">•</span>
                          <span>{{ msg }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="pb-10 max-sm:px-4">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Instructions -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-md">
          <div class="flex">
            <Info class="h-5 w-5 text-blue-400 mt-0.5" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Import Instructions</h3>
              <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc list-inside space-y-1">
                  <li>Download the template file and fill in member details</li>
                  <li>Ensure all required fields are filled (marked with *)</li>
                  <li>Use correct date format: YYYY-MM-DD (e.g., 1990-01-15)</li>
                  <li>Gender values: male, female, other</li>
                  <li>Marital status: single, married, divorced, widowed</li>
                  <li>ID type: national_id, passport, driving_license</li>
                  <li>Email addresses and ID numbers must be unique</li>
                  <li>Maximum file size: 5MB</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Download Template -->
        <div class="bg-white border-l-4 border-darkBlue shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
          <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
              <h3 class="text-lg font-bold text-darkBlue">Step 1: Download Template</h3>
              <p class="mt-1 text-sm text-gray-500">Get the Excel template with sample data</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2 flex items-center">
              <a :href="route('members.import.template')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-darkBlue hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <Download class="w-5 h-5 mr-2" />
                Download Template
              </a>
            </div>
          </div>
        </div>

        <!-- Upload File -->
        <div class="bg-white border-l-4 border-orange-500 shadow px-4 py-5 sm:rounded-lg sm:p-6">
          <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
              <h3 class="text-lg font-bold text-darkBlue">Step 2: Upload File</h3>
              <p class="mt-1 text-sm text-gray-500">Upload your completed Excel or CSV file</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
              <form @submit.prevent="submitImport" class="space-y-4">
                
                <!-- File Input -->
                <div>
                  <input type="file" ref="fileInput" accept=".csv,.xlsx,.xls" class="hidden" @change="handleFileSelect" />
                  
                  <div v-if="!selectedFile" 
                    @click="$refs.fileInput.click()"
                    class="relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-12 text-center hover:border-orange-500 hover:cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <FileSpreadsheet class="mx-auto h-12 w-12 text-gray-400" />
                    <div class="flex flex-col items-center mt-2">
                      <span class="block text-sm font-medium text-darkBlue">Click to upload Excel/CSV file</span>
                      <span class="block text-xs text-gray-500 mt-1">XLS, XLSX, or CSV up to 5MB</span>
                    </div>
                  </div>

                  <!-- Selected File Display -->
                  <div v-else class="border-2 border-green-300 rounded-lg p-4 bg-green-50">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <FileSpreadsheet class="h-8 w-8 text-green-600" />
                        <div class="ml-3">
                          <p class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</p>
                          <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                        </div>
                      </div>
                      <button type="button" @click="removeFile"
                        class="text-red-600 hover:text-red-800 focus:outline-none">
                        <X class="h-5 w-5" />
                      </button>
                    </div>
                  </div>

                  <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
                </div>

                <!-- Preview Info -->
                <div v-if="selectedFile" class="bg-gray-50 rounded-md p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-2">What happens next?</h4>
                  <ul class="text-sm text-gray-600 space-y-1 ml-4 list-disc">
                    <li>File will be validated for correct format</li>
                    <li>Each row will be checked for required fields</li>
                    <li>Valid members will be imported automatically</li>
                    <li>Errors will be reported for invalid rows</li>
                    <li>Default passwords will be generated for all members</li>
                  </ul>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                  <Link :href="route('members.index')"
                    class="bg-gray-200 py-2 px-4 rounded-md shadow-sm text-sm font-medium text-darkBlue hover:bg-gray-300">
                    Cancel
                  </Link>
                  <button type="submit" :disabled="!selectedFile || form.processing"
                    class="inline-flex items-center justify-center py-2 px-6 rounded-md text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <Upload class="w-5 h-5 mr-2" />
                    <span v-if="form.processing">Importing...</span>
                    <span v-else>Import Members</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Help Section -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-2">Need Help?</h4>
          <p class="text-sm text-gray-600">
            If you encounter any issues during import, please ensure:
          </p>
          <ul class="mt-2 text-sm text-gray-600 space-y-1 ml-4 list-disc">
            <li>All column headers match the template exactly</li>
            <li>No duplicate email addresses or ID numbers</li>
            <li>Dates are in YYYY-MM-DD format</li>
            <li>Required fields are not empty</li>
            <li>File is not corrupted and can be opened in Excel</li>
          </ul>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { 
  ArrowLeft, CheckCircle, AlertCircle, Download, Upload, 
  FileSpreadsheet, X, Info 
} from "lucide-vue-next";

// Props
const props = defineProps({
  sampleHeaders: Array,
  errors: Object,
});

// Flash handling
const page = usePage();
const flash = computed(() => page.props.flash || {});
const importErrors = computed(() => page.props.import_errors || []);
const flashMessage = ref(null);
const flashType = ref("success");
const flashBox = ref(null);
const showErrors = ref(false);

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
      flashBox.value?.scrollIntoView({ behavior: "smooth", block: "start" });
      setTimeout(() => (flashMessage.value = null), 8000);
    }
  },
  { immediate: true, deep: true }
);

// Form state
const form = useForm({
  file: null,
});

// File upload state
const selectedFile = ref(null);
const fileInput = ref(null);

// Handlers
const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    form.file = file;
  }
};

const removeFile = () => {
  selectedFile.value = null;
  form.file = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const submitImport = () => {
  form.post(route("members.import"), {
    forceFormData: true,
    onSuccess: () => {
      selectedFile.value = null;
      form.reset();
    },
    onError: (errors) => {
      console.error("Import errors:", errors);
    },
  });
};
</script>

<style scoped>
.bg-darkBlue {
  background-color: #0a2342;
}

.text-darkBlue {
  color: #0a2342;
}
</style>