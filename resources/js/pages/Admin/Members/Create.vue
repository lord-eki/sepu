<template>
    <!-- Loader Overlay -->
    <div v-if="form.processing" class="fixed inset-0 z-50 flex cursor-wait flex-col items-center justify-center bg-gray-800/50">
        <svg class="h-10 w-10 animate-spin text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="mt-3 text-sm font-medium text-white">Processing...</p>
    </div>

    <AppLayout :breadcrumbs="[{ title: 'Members', href: route('members.index') }, { title: 'Create Member' }]">
        <!-- Header -->
        <div class="bg-darkBlue mb-4 flex items-center rounded-md p-4 text-white shadow max-sm:mx-2">
            <Link :href="route('members.index')" class="mr-4 text-orange-400 hover:text-orange-500">
                <ArrowLeft class="h-5 w-5" />
            </Link>
            <h2 class="text-lg font-semibold sm:text-xl">Create New Member</h2>
        </div>

        <!-- Flash Messages -->
        <div ref="flashBox" class="mx-auto mt-4 max-w-3xl px-4">
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
        </div>

        <!-- Form -->
        <div class="pb-10 max-sm:px-4">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Personal Information -->
                    <div class="border-darkBlue border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Personal Information</h3>
                                <p class="mt-1 text-sm text-gray-500">Basic personal details of the member.</p>
                            </div>
                            <div class="mt-5 md:col-span-2 md:mt-0">
                                <div class="grid grid-cols-6 gap-6">
                                    <!-- Profile Photo -->
                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                                        <div class="mt-1 flex items-center space-x-5">
                                            <div class="flex-shrink-0">
                                                <img v-if="photoPreview" :src="photoPreview" class="h-20 w-20 rounded-full object-cover" />
                                                <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-300">
                                                    <User class="h-12 w-12 text-gray-400" />
                                                </div>
                                            </div>
                                            <input type="file" ref="photoInput" accept="image/*" class="hidden" @change="handlePhotoUpload" />
                                            <button
                                                type="button"
                                                @click="$refs.photoInput.click()"
                                                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium shadow-sm hover:bg-gray-50"
                                            >
                                                Change photo
                                            </button>
                                        </div>
                                    </div>

                                    <!-- First Name -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">First Name *</label>
                                        <input
                                            v-model="form.first_name"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.first_name }"
                                        />
                                        <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
                                    </div>

                                    <!-- Last Name -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Last Name *</label>
                                        <input
                                            v-model="form.last_name"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.last_name }"
                                        />
                                        <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
                                    </div>

                                    <!-- Middle Name -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Middle Name</label>
                                        <input
                                            v-model="form.middle_name"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.middle_name }"
                                        />
                                        <p v-if="errors.middle_name" class="mt-1 text-sm text-red-600">{{ errors.middle_name }}</p>
                                    </div>

                                    <!-- Date of Birth -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Date of Birth *</label>
                                        <input
                                            v-model="form.date_of_birth"
                                            type="date"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
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
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm sm:text-sm"
                                            :class="{ 'border-red-300': errors.gender }"
                                        >
                                            <option value="">Select Gender</option>
                                            <option v-for="(label, value) in genders" :key="value" :value="value">{{ label }}</option>
                                        </select>
                                        <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
                                    </div>

                                    <!-- Marital Status -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700">Marital Status *</label>
                                        <select
                                            v-model="form.marital_status"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm sm:text-sm"
                                            :class="{ 'border-red-300': errors.marital_status }"
                                        >
                                            <option value="">Select Status</option>
                                            <option v-for="(label, value) in maritalStatuses" :key="value" :value="value">{{ label }}</option>
                                        </select>
                                        <p v-if="errors.marital_status" class="mt-1 text-sm text-red-600">{{ errors.marital_status }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="border-darkBlue border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Contact Information</h3>
                                <p class="mt-1 text-sm text-gray-500">Contact details and address information.</p>
                            </div>
                            <div class="mt-5 md:col-span-2 md:mt-0">
                                <div class="grid grid-cols-6 gap-6">
                                    <!-- Email -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Email Address *</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.email }"
                                        />
                                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Phone Number *</label>
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.phone }"
                                        />
                                        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                                    </div>

                                    <!-- Physical Address -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Physical Address *</label>
                                        <input
                                            v-model="form.physical_address"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.physical }"
                                        />
                                        <p v-if="errors.physical_address" class="mt-1 text-sm text-red-600">{{ errors.physical_address }}</p>
                                    </div>

                                    <!-- Postal Address -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Postal Address *</label>
                                        <input
                                            v-model="form.postal_address"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.postal_address }"
                                        />
                                        <p v-if="errors.postal_address" class="mt-1 text-sm text-red-600">{{ errors.postal_address }}</p>
                                    </div>

                                    <!-- City -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">City *</label>
                                        <input
                                            v-model="form.city"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.city }"
                                        />
                                        <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city }}</p>
                                    </div>

                                    <!-- County -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">County *</label>
                                        <input
                                            v-model="form.county"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.county }"
                                        />
                                        <p v-if="errors.county" class="mt-1 text-sm text-red-600">{{ errors.county }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Identification -->
                    <div class="border-darkBlue mt-6 border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Identification</h3>
                                <p class="mt-1 text-sm text-gray-500">Official identification documents and numbers.</p>
                            </div>
                            <div class="mt-5 md:col-span-2 md:mt-0">
                                <div class="grid grid-cols-6 gap-6">
                                    <!-- ID Type -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">ID Type *</label>
                                        <select
                                            v-model="form.id_type"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.id_type }"
                                        >
                                            <option value="">Select ID Type</option>
                                            <option v-for="(label, value) in idTypes" :key="value" :value="value">{{ label }}</option>
                                        </select>
                                        <p v-if="errors.id_type" class="mt-1 text-sm text-red-600">{{ errors.id_type }}</p>
                                    </div>

                                    <!-- ID Number -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">ID Number *</label>
                                        <input
                                            v-model="form.id_number"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                            :class="{ 'border-red-300': errors.id_number }"
                                        />
                                        <p v-if="errors.id_number" class="mt-1 text-sm text-red-600">{{ errors.id_number }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="border-darkBlue mt-6 border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Employment Information</h3>
                                <p class="mt-1 text-sm text-gray-500">Work and employment details.</p>
                            </div>
                            <div class="mt-5 md:col-span-2 md:mt-0">
                                <div class="grid grid-cols-6 gap-6">
                                    <!-- Occupation -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Occupation *</label>
                                        <input
                                            v-model="form.occupation"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Employer -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Employer *</label>
                                        <input
                                            v-model="form.employer"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Monthly Income -->
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="text-darkBlue block text-sm font-medium">Monthly Income *</label>
                                        <input
                                            v-model="form.monthly_income"
                                            type="number"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Upload -->
                    <div class="border-darkBlue mt-6 border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Documents</h3>
                                <p class="mt-1 text-sm text-gray-500">Upload supporting documents (ID copy, passport photo, etc.)</p>
                            </div>
                            <div class="mt-5 md:col-span-2 md:mt-0">
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
                                        class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:cursor-pointer hover:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:outline-none"
                                    >
                                        <File class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="flex flex-col items-center">
                                            <span class="text-darkBlue mt-2 block text-sm font-medium">Upload documents</span>
                                            <CloudUpload class="h-5 w-5" />
                                        </div>
                                    </button>

                                    <!-- Document List -->
                                    <div v-if="selectedDocuments.length" class="mt-4">
                                        <h4 class="text-darkBlue text-sm font-medium">Selected Files:</h4>
                                        <ul class="mt-2 divide-y divide-gray-200 rounded-md border border-gray-200">
                                            <li
                                                v-for="(doc, index) in selectedDocuments"
                                                :key="index"
                                                class="flex items-center justify-between p-2 py-3 pr-4 pl-3 text-sm"
                                            >
                                                <div class="flex w-0 flex-1 items-center">
                                                    <FileIcon class="h-5 w-5 flex-shrink-0 text-gray-400" />
                                                    <span class="ml-2 w-0 flex-1 truncate">{{ doc.name }}</span>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="removeDocument(index)"
                                                    class="ml-4 flex-shrink-0 text-orange-600 hover:text-orange-500"
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

                    <!-- Emergency Contact -->
                    <div class="border-darkBlue mt-6 border-l-4 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Emergency Contact</h3>
                                <p class="mt-1 text-sm text-gray-500">Emergency contact details in case of urgent situations.</p>
                            </div>
                            <div class="mt-5 md:col-span-2 md:mt-0">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <!-- Contact Name -->
                                    <div>
                                        <label class="text-darkBlue block text-sm font-medium">Name *</label>
                                        <input
                                            v-model="form.emergency_contact_name"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Contact Phone -->
                                    <div>
                                        <label class="text-darkBlue block text-sm font-medium">Phone *</label>
                                        <input
                                            v-model="form.emergency_contact_phone"
                                            type="tel"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Relationship -->
                                    <div>
                                        <label class="text-darkBlue block text-sm font-medium">Relationship *</label>
                                        <input
                                            v-model="form.emergency_contact_relationship"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Setup -->
                    <div class="border-l-4 border-orange-500 bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1">
                                <h3 class="text-darkBlue text-lg font-bold">Account Setup</h3>
                                <p class="mt-1 text-sm text-gray-500">Choose how member password will be created.</p>
                            </div>

                            <div class="mt-5 space-y-4 md:col-span-2 md:mt-0">
                                <!-- Password Type -->
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label
                                        class="cursor-pointer rounded-xl border p-4 hover:border-orange-500"
                                        :class="form.password_type === 'id_number' ? 'border-orange-500 bg-orange-50' : 'border-gray-300'"
                                    >
                                        <input type="radio" value="id_number" v-model="form.password_type" class="mr-2" />

                                        <span class="text-darkBlue font-semibold">Use ID Number</span>

                                        <p class="mt-1 text-xs text-gray-500">Password becomes member ID number</p>
                                    </label>

                                    <label
                                        class="cursor-pointer rounded-xl border p-4 hover:border-orange-500"
                                        :class="form.password_type === 'custom' ? 'border-orange-500 bg-orange-50' : 'border-gray-300'"
                                    >
                                        <input type="radio" value="custom" v-model="form.password_type" class="mr-2" />

                                        <span class="text-darkBlue font-semibold">Custom Password</span>

                                        <p class="mt-1 text-xs text-gray-500">Manually create password</p>
                                    </label>
                                </div>

                                <!-- Preview -->
                                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                                    <p class="text-sm font-medium text-blue-700">Selected Password</p>

                                    <p class="mt-1 text-xl font-bold text-[#0a2342]">
                                        {{
                                            form.password_type === 'id_number'
                                                ? form.id_number || 'Enter ID Number first'
                                                : form.custom_password || 'Enter custom password'
                                        }}
                                    </p>
                                </div>

                                <!-- Custom Password -->
                                <div v-if="form.password_type === 'custom'">
                                    <label class="text-darkBlue mb-1 block text-sm font-medium"> Custom Password </label>

                                    <input
                                        v-model="form.custom_password"
                                        type="text"
                                        class="w-full rounded-md border border-gray-300 p-2"
                                        placeholder="Enter custom password"
                                    />
                                </div>

                                <!-- Force Change -->
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="form.must_change_password" />
                                    <span class="text-sm text-gray-700"> Require password change on first login </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('members.index')"
                            class="text-darkBlue rounded-md bg-gray-200 px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-300"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="!formValid"
                            class="inline-flex justify-center rounded-md bg-[#0a2342] px-6 py-2 text-sm font-medium text-white transition hover:cursor-pointer hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{ 'processing-disabled': form.processing }"
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, CheckCircle, CloudUpload, User } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Props
const props = defineProps({
    membershipId: String,
    idTypes: Object,
    genders: Object,
    maritalStatuses: Object,
    errors: Object,
});

// Flash handling
const page = usePage();
const flash = computed(() => page.value?.props?.flash || {});
const flashMessage = ref(null);
const flashType = ref('success');
const flashBox = ref(null);

watch(
    flash,
    (val) => {
        if (val?.success) {
            flashMessage.value = val.success;
            flashType.value = 'success';
        } else if (val?.error) {
            flashMessage.value = val.error;
            flashType.value = 'error';
        }

        if (flashMessage.value) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => (flashMessage.value = null), 5000);
        }
    },
    { immediate: true, deep: true },
);

// form state
const form = useForm({
    first_name: '',
    last_name: '',
    middle_name: '',
    date_of_birth: '',
    gender: '',
    marital_status: '',
    email: '',
    phone: '',
    physical_address: '',
    postal_address: '',
    city: '',
    county: '',
    id_type: '',
    id_number: '',
    occupation: '',
    employer: '',
    monthly_income: '',

    // password setup options
    password_type: 'id_number', // id_number | custom
    custom_password: '',
    must_change_password: true,

    profile_photo: null,
    documents: [],
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: '',
});

// File upload state
const selectedDocuments = ref([]);
const photoPreview = ref(null);

// Confirm Password Check
const passwordsMatch = computed(() => form.password === form.password_confirmation);

watch(
    () => form.id_number,
    (value) => {
        if (form.password_type === 'id_number') {
            form.custom_password = '';
        }
    },
);

// Required Fields Check
const formValid = computed(() => {
    return (
        form.first_name &&
        form.last_name &&
        form.date_of_birth &&
        form.gender &&
        form.marital_status &&
        form.email &&
        form.phone &&
        form.physical_address &&
        form.postal_address &&
        form.city &&
        form.county &&
        form.id_type &&
        form.id_number &&
        form.emergency_contact_name &&
        form.emergency_contact_phone &&
        form.emergency_contact_relationship &&
        form.occupation &&
        form.employer &&
        form.monthly_income &&
        (form.password_type === 'id_number' || (form.password_type === 'custom' && form.custom_password))
    );
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
    form.post(route('members.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            photoPreview.value = null;
            selectedDocuments.value = [];
        },
    });
};
</script>

<style scoped>
.bg-darkBlue {
    background-color: #0a2342;
    /* deep dark blue */
}

.text-darkBlue {
    color: #0a2342;
}

/* Disable interaction and cursor when processing */
.processing-disabled {
    pointer-events: none;
    cursor: wait !important;
}
</style>
