<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { CheckCircle, XCircle, Save } from 'lucide-vue-next'
import { route } from 'ziggy-js'


interface Member {
    id: number
    user_id: number
    membership_id: string
    first_name: string
    last_name: string
    email: string
    phone: string
    middle_name?: string | null
    id_number?: string | null
    id_type?: string | null
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

const page = usePage<{ member: Member }>()
const member = computed(() => page.props.member)
const user = computed(() => page.props.user)

// edit toggle
const isEditing = ref(false)

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('en-GB')
}

const form = useForm({
    first_name: member.value.first_name,
    last_name: member.value.last_name,
    middle_name: member.value.middle_name,
    email: user.value.email,
    phone: user.value.phone,
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
        : "",
    profile_photo: null as File | null,
})

const showToast = ref(false)
const toastMessage = ref("")
const toastType = ref<"success" | "error">("success")

function handlePhotoUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.profile_photo = target.files[0]
  }
}


function submit() {
    form.put(route('member.updateProfile'), {
        forceFormData: true,
        onSuccess: () => {
            toastMessage.value = "Profile updated successfully!"
            toastType.value = "success"
            showToast.value = true
            isEditing.value = false
        },
        onError: () => {
            toastMessage.value = "An error occurred. Please try again."
            toastType.value = "error"
            showToast.value = true
        }
    })
}

watch(showToast, (val) => {
    if (val) setTimeout(() => (showToast.value = false), 3000)
})
</script>

<template>

    <Head title="Member Profile" />
    <AppLayout :breadcrumbs="[{ title: 'Member Profile', href: '/member/profile' }]">
        <div class="p-4">
            <!-- Toast -->
            <transition name="slide">
                <div v-if="showToast"
                    class="fixed top-5 right-5 flex items-center px-5 py-3 rounded-xl shadow-lg text-white z-50"
                    :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'">
                    <CheckCircle v-if="toastType === 'success'" class="w-5 h-5 mr-2" />
                    <XCircle v-else class="w-5 h-5 mr-2" />
                    <span class="font-medium">{{ toastMessage }}</span>
                </div>
            </transition>

            <!-- Layout split -->
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 sm:gap-6">
                <!-- Left: Profile Summary -->
                <div class="col-span-1 flex bg-white dark:bg-gray-900 shadow-xl max-sm:mb-4 rounded-xl overflow-hidden">
                    <!-- Profile header -->
                    <div class="bg-blue-100 p-2 sm:p-4 flex flex-col items-center justify-center gap-3">
                        <img 
                            v-if="member.profile_photo" 
                            :src="`/storage/${member.profile_photo}`" 
                            alt="Profile"
                            class="w-24 h-24 rounded-full object-cover" 
                        />
                        <img 
                            v-else 
                            src="/default-profile.png" 
                            alt="Profile"
                            class="w-24 h-24 rounded-full object-cover" 
                        />

                        <!-- Upload input -->
                        <div class="flex flex-col items-center gap-2">
                        <label 
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow hover:bg-blue-700 cursor-pointer"
                        >
                            Change Photo
                            <input 
                            type="file" 
                            accept="image/*" 
                            @change="handlePhotoUpload" 
                            class="hidden"
                            />
                        </label>
                        </div>


                        <div class="text-center">
                            <h2 class="mt-2 text-xl font-semibold text-gray-700">
                            {{ member.first_name }} {{ member.last_name }}
                            </h2>
                            <p class="text-sm text-gray-800">Membership ID: {{ member.membership_id }}</p>
                        </div>
                        </div>


                    <!-- Static info -->
                    <div class="p-6 grid grid-cols-1 gap-4">
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                            <label class="text-xs font-medium text-gray-500">ID Number</label>
                            <p class="mt-1 font-medium">{{ member.id_number || '-' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                            <label class="text-xs font-medium text-gray-500">Date of Birth</label>
                            <p class="mt-1 font-medium">{{ formatDate(member.date_of_birth) }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                            <label class="text-xs font-medium text-gray-500">Gender</label>
                            <p class="mt-1 font-semibold">{{ member.gender || '-' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                            <label class="text-xs font-medium text-gray-500">Membership Status</label>
                            <p class="mt-1 font-medium capitalize">{{ member.membership_status }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
                            <label class="text-xs font-medium text-gray-500">Joined On</label>
                            <p class="mt-1 font-medium">{{ formatDate(member.membership_date) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Editable Form -->
                <div class="col-span-2 bg-white dark:bg-gray-900 shadow-xl rounded-2xl p-8 space-y-8">
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Personal Info</h3>
                        <!-- Buttons -->
                        <div class="flex gap-4">
                        <Button 
                            v-if="!isEditing" 
                            @click="isEditing = true" 
                            class="bg-blue-600 hover:bg-blue-700 hover:cursor-pointer text-white rounded-sm shadow-md"
                        >
                            ✏️ Edit
                        </Button>

                        <Button 
                            v-if="isEditing" 
                            @click="isEditing = false" 
                            class="bg-gray-500 hover:bg-gray-600 hover:cursor-pointer text-white rounded-sm shadow-md"
                        >
                            Cancel
                        </Button>

                        <Button 
                            v-if="isEditing" 
                            type="button" 
                            @click="submit" 
                            class="bg-green-600 hover:bg-green-700 hover:cursor-pointer text-white rounded-sm shadow-md flex items-center gap-2"
                        >
                            <Save class="w-4 h-4" /> <span>Save</span>
                        </Button>
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 inputsborder">
                        <div>
                            <label class="block text-sm font-medium mb-1">First Name</label>
                            <input v-model="form.first_name" type="text" class="w-full bg-gray-100  rounded-xl p-2.5" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Last Name</label>
                            <input v-model="form.last_name" type="text" class="w-full bg-gray-100 rounded-xl p-2.5" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Middle Name</label>
                            <input v-model="form.middle_name" type="text" class="w-full rounded-xl p-2.5"
                                :disabled="!isEditing" />
                        </div>
                       <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input v-model="form.email" type="text" class="w-full bg-gray-100 rounded-xl p-2.5" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phone</label>
                            <input v-model="form.phone" type="text" class="w-full bg-gray-100 rounded-xl p-2.5" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Marital Status</label>
                            <select v-model="form.marital_status" class="w-full rounded-xl p-2.5"
                                :disabled="!isEditing">
                                <option value="" disabled>Select status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Occupation</label>
                            <input v-model="form.occupation" type="text" class="w-full rounded-xl p-2.5"
                                :disabled="!isEditing" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Employer</label>
                            <input v-model="form.employer" type="text" class="w-full rounded-xl p-2.5"
                                :disabled="!isEditing" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Monthly Income</label>
                            <input v-model="form.monthly_income" type="number" class="w-full rounded-xl p-2.5"
                                :disabled="!isEditing" />
                        </div>
                    </div>

                    <!-- Address -->
                    <h3 class="text-lg font-semibold mb-2 pb-2">Address</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 inputsborder">
                    <div>
                        <label class="block text-sm font-medium mb-1">Physical Address</label>
                        <input v-model="form.physical_address" type="text"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Postal Address</label>
                        <input v-model="form.postal_address" type="text"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">City</label>
                        <input v-model="form.city" type="text"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    <div>
                    <label class="block text-sm font-medium mb-1">Country</label>
                    <select v-model="form.country" class="w-full rounded-xl p-2.5" :disabled="!isEditing">
                        <option value="" disabled>Select country</option>
                        <option value="Kenya">Kenya</option>
                        <option value="Uganda">Uganda</option>
                        <option value="Tanzania">Tanzania</option>
                        <option value="Rwanda">Rwanda</option>
                        <option value="Burundi">Burundi</option>
                        <option value="South Sudan">South Sudan</option>
                        <!-- add more countries as needed -->
                    </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Country</label>
                        <input v-model="form.country" type="text"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    </div>

                    <!-- Emergency -->
                    <h3 class="text-lg font-semibold mb-2 pb-2">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 inputsborder">
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Name</label>
                        <input v-model="form.emergency_contact_name" type="text"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Phone</label>
                        <input v-model="form.emergency_contact_phone" type="number"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Relationship</label>
                        <input v-model="form.emergency_contact_relationship" type="text"
                        class="w-full rounded-xl p-2.5" :disabled="!isEditing" />
                    </div>
                    </div>
                    <!-- Save Button -->
                    
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(50px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(50px);
}

.inputsborder input,
select {
    border: 1px solid #b5c4daff;
}
</style>
