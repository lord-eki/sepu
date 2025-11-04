<template>
  <AppLayout>
    <Head title="Import Member Deposits" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-semibold mb-6">Import Monthly Deposits for Existing Members</h2>

            <!-- Success Message -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
              {{ $page.props.flash.success }}
            </div>

            <!-- Error Messages -->
            <div v-if="$page.props.errors.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
              {{ $page.props.errors.error }}
            </div>

            <!-- Import Errors -->
            <div v-if="$page.props.flash.import_errors && $page.props.flash.import_errors.length > 0" class="mb-4">
              <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p class="font-bold mb-2">Some rows had errors:</p>
                <ul class="list-disc list-inside max-h-60 overflow-y-auto">
                  <li v-for="error in $page.props.flash.import_errors" :key="error.row" class="mb-1">
                    <strong>Row {{ error.row }}</strong> ({{ error.name }}): 
                    {{ error.errors.join(', ') }}
                  </li>
                </ul>
              </div>
            </div>

            <!-- Instructions -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h3 class="font-semibold text-blue-900 mb-2">Instructions:</h3>
              <ol class="list-decimal list-inside space-y-1 text-blue-800 text-sm">
                <li>Download the template below</li>
                <li>Fill in member names exactly as they appear in the system</li>
                <li>Enter monthly deposit amounts in the month columns</li>
                <li>Leave cells empty for months with no deposits</li>
                <li>Select the year for these deposits</li>
                <li>Upload the completed file</li>
              </ol>
            </div>

            <!-- Download Template Button -->
            <div class="mb-6">
              <a 
                :href="route('members.deposits.import.template')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download Template
              </a>
            </div>

            <!-- Import Form -->
            <form @submit.prevent="submitImport" class="space-y-6">
              <!-- Year Selection -->
              <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
                  Year <span class="text-red-500">*</span>
                </label>
                <select
                  id="year"
                  v-model="form.year"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                  required
                >
                  <option value="">Select Year</option>
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">Select the year for which you're importing deposits</p>
              </div>

              <!-- File Upload -->
              <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                  Upload File <span class="text-red-500">*</span>
                </label>
                <input
                  id="file"
                  type="file"
                  @change="handleFileChange"
                  accept=".csv,.xlsx,.xls"
                  class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100"
                  required
                />
                <p class="mt-1 text-sm text-gray-500">Supported formats: CSV, XLSX, XLS (Max: 5MB)</p>
              </div>

              <!-- Selected File Info -->
              <div v-if="form.file" class="bg-gray-50 p-3 rounded-md">
                <p class="text-sm text-gray-700">
                  <strong>Selected file:</strong> {{ form.file.name }}
                  <span class="text-gray-500">({{ formatFileSize(form.file.size) }})</span>
                </p>
              </div>

              <!-- Submit Button -->
              <div class="flex items-center justify-between">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Importing...' : 'Import Deposits' }}
                </button>

                <Link
                  :href="route('members.index')"
                  class="text-gray-600 hover:text-gray-800"
                >
                  Cancel
                </Link>
              </div>
            </form>

            <!-- Important Notes -->
            <div class="mt-8 border-t pt-6">
              <h3 class="font-semibold text-gray-900 mb-3">Important Notes:</h3>
              <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                  <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  <span>Members must already exist in the system before importing deposits</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  <span>System will automatically skip duplicate transactions</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  <span>Name matching is fuzzy - "FRANCIS P. MBUQUA" will match "Francis Mbuqua"</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 mr-2 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  <span>Processing large files may take several minutes</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
  currentYear: Number,
  years: Array,
});

const form = useForm({
  file: null,
  year: props.currentYear,
});

const handleFileChange = (event) => {
  form.file = event.target.files[0];
};

const submitImport = () => {
  form.post(route('members.deposits.import'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
    },
  });
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>