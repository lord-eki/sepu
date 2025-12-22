<template>
  <AppLayout :breadcrumbs="[
    { title: 'System Users', href: route('system-users.index') },
    { title: 'Add User' }
  ]">
    <Head title="Add System User" />

    <!-- CENTER TOAST -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-90"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-90"
    >
      <div
        v-if="toast.visible"
        class="fixed inset-0 z-50 flex items-start justify-center pt-20"
      >
        <div
          :class="toast.type === 'success'
            ? 'bg-emerald-600/90'
            : 'bg-rose-600/90'"
          class="backdrop-blur-xl text-white px-6 py-4 rounded-2xl shadow-xl max-w-md w-full text-center"
        >
          <p class="font-semibold text-lg">
            {{ toast.message }}
          </p>
        </div>
      </div>
    </transition>

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6 mx-6 mt-6">
      <h1 class="text-2xl font-bold text-[#0B1F3A] dark:text-white">
        Add System User
      </h1>

      <Link
        :href="route('system-users.index')"
        class="px-4 py-2 rounded-xl bg-[#0B1F3A] hover:bg-blue-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-white transition"
      >
        Back
      </Link>
    </div>

    <!-- FORM CARD -->
    <div class="max-w-3xl mx-auto bg-white dark:bg-[#0B1F3A] rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-8">
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- MEMBER SELECT -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
            Select Member
          </label>

          <Combobox v-model="form.user_id">
            <div class="relative">
              <ComboboxInput
                class="w-full rounded-xl border p-2 border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white px-4 py-2 focus:ring-orange-500 focus:border-orange-500"
                :displayValue="displayMember"
                placeholder="Search member by name or ID"
                @change="query = $event.target.value"
              />

              <ComboboxOptions
                class="absolute z-10 mt-2 max-h-60 w-full overflow-auto rounded-xl bg-white dark:bg-[#14294B] shadow-lg border dark:border-gray-700"
              >
                <ComboboxOption
                  v-for="member in filteredMembers"
                  :key="member.user_id"
                  :value="member.user_id"
                  class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  {{ member.full_name }} — {{ member.membership_id }}
                </ComboboxOption>

                <div
                  v-if="!filteredMembers.length"
                  class="px-4 py-2 text-sm text-gray-500"
                >
                  No member found
                </div>
              </ComboboxOptions>
            </div>
          </Combobox>

          <p v-if="form.errors.user_id" class="text-sm text-rose-500 mt-1">
            {{ form.errors.user_id }}
          </p>
        </div>

    
        <!-- READONLY INFO -->
          <div>
            <label class="label text-slate-800 font-bold mr-4">Full Name</label>
            <input
              :value="selectedMember?.full_name || ''"
              readonly
              class="input-readonly border p-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 w-full"
            />
          </div>

          <div>
            <label class="label text-slate-800 font-bold mr-4">Email</label>
            <input
              :value="selectedMember?.email || ''"
              readonly
              class="input-readonly border p-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 w-full"
            />
          </div>

          <div>
            <label class="label text-slate-800 font-bold mr-4">Phone Number</label>
            <input
              :value="selectedMember?.phone || ''"
              readonly
              class="input-readonly border p-3 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 w-full"
            />
          </div>

          <!-- ROLE -->
          <div>
            <label class="label text-slate-800 font-bold mr-4">User Role</label>
            <select
              v-model="form.role"
              class="input border p-3 rounded-xl w-full bg-white dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
            >
              <option disabled value="">Select role</option>
              <option v-for="(label, key) in roles" :key="key" :value="key">
                {{ label }}
              </option>
            </select>

            <p v-if="form.errors.role" class="text-sm text-rose-500 mt-1">
              {{ form.errors.role }}
            </p>
          </div>


        <!-- SUBMIT -->
        <div class="md:col-span-2 flex justify-end pt-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold transition disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Create User' }}
          </button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref, watch } from 'vue'
import {
  Combobox,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
} from '@headlessui/vue'

const props = defineProps({
  roles: Object,
  members: Array,
})

const page = usePage()

/* FORM */
const form = useForm({
  user_id: '',
  role: '',
  is_active: true,
})

/* SELECTED MEMBER */
const selectedMember = computed(() =>
  props.members.find(m => m.user_id === form.user_id)
)

/* COMBOBOX */
const query = ref('')

const filteredMembers = computed(() => {
  if (!query.value) return props.members
  return props.members.filter(m =>
    m.full_name.toLowerCase().includes(query.value.toLowerCase()) ||
    m.membership_id.toLowerCase().includes(query.value.toLowerCase())
  )
})

const displayMember = (id) => {
  const member = props.members.find(m => m.user_id === id)
  return member ? `${member.full_name} — ${member.membership_id}` : ''
}

/* TOAST */
const toast = ref({
  visible: false,
  message: '',
  type: 'success',
})

watch(
  () => page.props.flash,
  (flash) => {
    if (flash.success || flash.error) {
      toast.value = {
        visible: true,
        message: flash.success || flash.error,
        type: flash.success ? 'success' : 'error',
      }

      setTimeout(() => {
        toast.value.visible = false
      }, 4000)
    }
  },
  { deep: true }
)

/* SUBMIT */
const submit = () => {
  form.post(route('system-users.store'))
}
</script>


<style scoped>
.role-form input,
.role-form select {
  border: 1px solid gray;
  padding: 10px;
}
</style>
