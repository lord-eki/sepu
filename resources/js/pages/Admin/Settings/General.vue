<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  settings: Record<string, any>
}>()

const page         = usePage()
const flash        = computed(() => page.props.flash || {})
const flashMessage = ref(null)
const flashType    = ref('success')
const flashBox     = ref(null)


watch(flash, (val) => {
  if (val.success)      { flashMessage.value = val.success; flashType.value = 'success' }
  else if (val.error)   { flashMessage.value = val.error;   flashType.value = 'error'   }

  if (flashMessage.value) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    setTimeout(() => (flashMessage.value = null), 3000)
  }
}, { immediate: true, deep: true })

/**
 * SystemSetting → frontend form fields
 */
const form = useForm({
  sacco_name: props.settings.sacco_name?.value ?? '',
  sacco_email: props.settings.sacco_email?.value ?? '',
  sacco_phone: props.settings.sacco_phone?.value ?? '',
  sacco_address: props.settings.sacco_address?.value ?? '',
  sacco_registration_number: props.settings.sacco_registration_number?.value ?? '',
  currency: props.settings.currency?.value ?? 'KES',
})

/**
 * Global error checker
 */
const hasErrors = computed(() => Object.keys(form.errors).length > 0)

/**
 * Submit form
 */
function saveSettings() {
  form.post(route('admin.settings.update-general'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'Settings', href: route('admin.settings.index') },
    { title: 'General' }
  ]">

  <!-- Flash Messages -->
  <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" :class="[
          flashType === 'success'
            ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-700 dark:text-green-300'
            : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-700 dark:text-red-300',
          'mb-4 rounded-xl p-4 shadow-sm flex items-center border'
        ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" />
          <div class="flex gap-2 items-center">
            <p class="ml-3 text-sm">{{ flashMessage }}</p>
            <button @click="flashMessage = null"
              class="ml-auto text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
              x
            </button>
          </div>
        </div>
      </transition>
    </div>

    <Head title="General Settings" />

    <div class="bg-white rounded-2xl shadow-md p-6 space-y-6">

      <!-- HEADER -->
      <div>
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">
          General Settings
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          Manage your SACCO profile and system identity
        </p>
      </div>

      <!-- GLOBAL ERROR -->
      <transition
        enter-active-class="transition duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div
          v-if="hasErrors"
          class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"
        >
          Please correct the highlighted fields before saving.
        </div>
      </transition>

      <!-- FORM -->
      <form @submit.prevent="saveSettings" class="space-y-5">

        <!-- SACCO NAME -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Organization Name
          </label>

          <input
            v-model="form.sacco_name"
            type="text"
            placeholder="e.g., SEPU SACCO Society"
            :class="[
              'w-full mt-1 px-4 py-2 rounded-lg border transition outline-none',
              form.errors.sacco_name
                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                : 'border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'
            ]"
          />

          <p v-if="form.errors.sacco_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.sacco_name }}
          </p>
        </div>

        <!-- EMAIL -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Email Address
          </label>

          <input
            v-model="form.sacco_email"
            type="email"
            placeholder="e.g., info@sacco.co.ke"
            :class="[
              'w-full mt-1 px-4 py-2 rounded-lg border transition outline-none',
              form.errors.sacco_email
                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                : 'border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'
            ]"
          />

          <p v-if="form.errors.sacco_email" class="text-red-500 text-sm mt-1">
            {{ form.errors.sacco_email }}
          </p>
        </div>

        <!-- PHONE -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Phone Number
          </label>

          <input
            v-model="form.sacco_phone"
            type="text"
            placeholder="e.g., +254 700 000 000"
            :class="[
              'w-full mt-1 px-4 py-2 rounded-lg border transition outline-none',
              form.errors.sacco_phone
                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                : 'border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'
            ]"
          />

          <p v-if="form.errors.sacco_phone" class="text-red-500 text-sm mt-1">
            {{ form.errors.sacco_phone }}
          </p>
        </div>

        <!-- ADDRESS -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Physical Address
          </label>

          <textarea
            v-model="form.sacco_address"
            rows="3"
            placeholder="e.g., Nairobi CBD, Kenya"
            :class="[
              'w-full mt-1 px-4 py-2 rounded-lg border transition outline-none',
              form.errors.sacco_address
                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                : 'border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'
            ]"
          ></textarea>

          <p v-if="form.errors.sacco_address" class="text-red-500 text-sm mt-1">
            {{ form.errors.sacco_address }}
          </p>
        </div>

        <!-- REGISTRATION NUMBER -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Registration Number
          </label>

          <input
            v-model="form.sacco_registration_number"
            type="text"
            placeholder="e.g., SACCO/REG/2025/001"
            :class="[
              'w-full mt-1 px-4 py-2 rounded-lg border transition outline-none',
              form.errors.sacco_registration_number
                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                : 'border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'
            ]"
          />

          <p v-if="form.errors.sacco_registration_number" class="text-red-500 text-sm mt-1">
            {{ form.errors.sacco_registration_number }}
          </p>
        </div>

        <!-- CURRENCY -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Default Currency
          </label>

          <select
            v-model="form.currency"
            :class="[
              'w-full mt-1 px-4 py-2 rounded-lg border transition outline-none',
              form.errors.currency
                ? 'border-red-500 focus:ring-2 focus:ring-red-300'
                : 'border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400'
            ]"
          >
            <option value="KES">KES (Kenyan Shilling)</option>
            <option value="USD">USD (US Dollar)</option>
            <option value="EUR">EUR (Euro)</option>
          </select>

          <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">
            {{ form.errors.currency }}
          </p>
        </div>

        <!-- ACTION -->
        <div class="flex justify-end pt-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-gradient-to-r from-sky-600 to-orange-500 text-white rounded-lg shadow-md transition flex items-center gap-2
                   hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>