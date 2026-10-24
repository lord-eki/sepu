<template>
    <!-- Loader Overlay -->
    <div v-if="form.processing" class="fixed inset-0 z-50 flex cursor-wait flex-col items-center justify-center bg-gray-800/50">
        <svg class="h-10 w-10 animate-spin text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="mt-3 text-sm font-medium text-white">Processing import...</p>
    </div>

    <AppLayout :breadcrumbs="[{ title: 'Members', href: route('members.index') }, { title: 'Import Members' }]">
        <!-- Header -->
        <div class="bg-darkBlue mb-4 flex items-center rounded-md p-4 text-white shadow max-sm:mx-2">
            <Link :href="route('members.index')" class="mr-4 text-orange-400 hover:text-orange-500">
                <ArrowLeft class="h-5 w-5" />
            </Link>
            <h2 class="text-lg font-semibold sm:text-xl">Import Members from Excel</h2>
        </div>

        <!-- Flash Messages -->
        <div ref="flashBox" class="mx-auto mt-4 max-w-4xl px-4">
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
                    class="flex gap-3"
                    :class="[
                        flashType === 'success'
                            ? 'border border-green-200 bg-green-50 text-green-700'
                            : 'border border-red-200 bg-red-50 text-red-700',
                        'mb-4 flex items-center rounded-md p-4 shadow',
                    ]"
                >
                    <component
                        :is="flashType === 'success' ? CheckCircle : AlertCircle"
                        class="h-5 w-5"
                        :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'"
                    />
                    <p class="ml-3 text-sm">{{ flashMessage }}</p>
                    <button type="button" class="ml-auto text-gray-500 hover:text-gray-700" @click="flashMessage = null">✕</button>
                </div>
            </transition>

            <!-- Import Errors -->
            <div v-if="importErrors && importErrors.length > 0" class="mb-4 rounded-md border-l-4 border-yellow-400 bg-yellow-50 p-4 shadow-sm">
                <div class="flex items-start">
                    <AlertCircle class="mt-0.5 h-6 w-6 flex-shrink-0 text-yellow-600" />
                    <div class="ml-3 flex-1">
                        <h3 class="text-base font-bold text-yellow-900">Import Errors - {{ importErrors.length }} row(s) failed</h3>
                        <p class="mt-1 text-sm text-yellow-700">Please review and fix the following errors, then re-import the file.</p>
                        <div class="mt-3">
                            <button
                                @click="showErrors = !showErrors"
                                class="inline-flex items-center rounded-md border border-yellow-300 bg-yellow-100 px-3 py-1.5 text-sm font-medium text-yellow-800 hover:bg-yellow-200 focus:ring-2 focus:ring-yellow-500 focus:outline-none"
                            >
                                {{ showErrors ? 'Hide' : 'Show' }} error details
                            </button>
                        </div>
                        <div v-if="showErrors" class="mt-4 max-h-96 overflow-y-auto">
                            <div class="space-y-3">
                                <div
                                    v-for="(error, index) in importErrors"
                                    :key="index"
                                    class="rounded-md border-l-4 border-red-400 bg-white p-4 shadow-sm"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-gray-900">
                                                <span
                                                    class="mr-2 inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800"
                                                >
                                                    Row {{ error.row }}
                                                </span>
                                                {{ error.name || 'Unknown Member' }}
                                            </p>
                                            <div class="mt-2 space-y-1">
                                                <div v-for="(msg, idx) in error.errors" :key="idx" class="flex items-start text-sm text-red-700">
                                                    <span class="mr-2 text-red-500">•</span>
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
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Instructions -->
                <div class="mb-6 rounded-md border-l-4 border-blue-400 bg-blue-50 p-4">
                    <div class="flex">
                        <Info class="mt-0.5 h-5 w-5 text-blue-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Import Instructions</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-inside list-disc space-y-1">
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
                <div class="border-darkBlue mb-6 border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-darkBlue text-lg font-bold">Step 1: Download Template</h3>
                            <p class="mt-1 text-sm text-gray-500">Get the Excel template with sample data</p>
                        </div>
                        <div class="mt-5 flex items-center md:col-span-2 md:mt-0">
                            <a
                                :href="route('members.import.template')"
                                class="bg-darkBlue inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-900 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                            >
                                <Download class="mr-2 h-5 w-5" />
                                Download Template
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upload File -->
                <div class="border-l-4 border-orange-500 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-darkBlue text-lg font-bold">Step 2: Upload File</h3>
                            <p class="mt-1 text-sm text-gray-500">Upload your completed Excel or CSV file</p>
                        </div>
                        <div class="mt-5 md:col-span-2 md:mt-0">
                            <form @submit.prevent="submitImport" class="space-y-4">
                                <!-- File Input -->
                                <div>
                                    <input type="file" ref="fileInput" accept=".csv,.xlsx,.xls" class="hidden" @change="handleFileSelect" />

                                    <div
                                        v-if="!selectedFile"
                                        @click="$refs.fileInput.click()"
                                        class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:cursor-pointer hover:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:outline-none"
                                    >
                                        <FileSpreadsheet class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-2 flex flex-col items-center">
                                            <span class="text-darkBlue block text-sm font-medium">Click to upload Excel/CSV file</span>
                                            <span class="mt-1 block text-xs text-gray-500">XLS, XLSX, or CSV up to 5MB</span>
                                        </div>
                                    </div>

                                    <!-- Selected File Display -->
                                    <div v-else class="rounded-lg border-2 border-green-300 bg-green-50 p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <FileSpreadsheet class="h-8 w-8 text-green-600" />
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeFile" class="text-red-600 hover:text-red-800 focus:outline-none">
                                                <X class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </div>

                                    <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
                                </div>

                                <!-- Preview Info -->
                                <div v-if="selectedFile" class="rounded-md bg-gray-50 p-4">
                                    <h4 class="mb-2 text-sm font-medium text-gray-900">What happens next?</h4>
                                    <ul class="ml-4 list-disc space-y-1 text-sm text-gray-600">
                                        <li>File will be validated for correct format</li>
                                        <li>Each row will be checked for required fields</li>
                                        <li>Valid members will be imported automatically</li>
                                        <li>Errors will be reported for invalid rows</li>
                                        <li>Default passwords will be generated for all members</li>
                                    </ul>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end space-x-3">
                                    <button
                                        type="button"
                                        @click="removeFile"
                                        class="text-darkBlue rounded-md bg-gray-200 px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-300"
                                    >
                                        Cancel
                                    </button>

                                    <button
                                        type="submit"
                                        :disabled="!selectedFile || form.processing"
                                        class="inline-flex items-center justify-center rounded-md bg-orange-500 px-6 py-2 text-sm font-medium text-white hover:bg-orange-600 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Upload class="mr-2 h-5 w-5" />
                                        <span v-if="form.processing">Importing...</span>
                                        <span v-else>Import Members</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <h4 class="mb-2 text-sm font-medium text-gray-900">Need Help?</h4>
                    <p class="text-sm text-gray-600">If you encounter any issues during import, please ensure:</p>
                    <ul class="mt-2 ml-4 list-disc space-y-1 text-sm text-gray-600">
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, CheckCircle, Download, FileSpreadsheet, Info, Upload, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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
const flashType = ref('success');
const flashBox = ref(null);
const showErrors = ref(false);

watch(
    flash,
    (val) => {
        if (val.success) {
            flashMessage.value = val.success;
            flashType.value = 'success';
        } else if (val.error) {
            flashMessage.value = val.error;
            flashType.value = 'error';
        }

        if (flashMessage.value) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => (flashMessage.value = null), 8000);
        }
    },
    { immediate: true, deep: true },
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
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const submitImport = () => {
    form.post(route('members.import'), {
        forceFormData: true,
        onSuccess: () => {
            selectedFile.value = null;
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
        onError: (errors) => {
            // Reset the input to allow re-selecting same file
            if (fileInput.value) fileInput.value.value = '';
            selectedFile.value = null;
            form.file = null;
            console.error('Import errors:', errors);
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
