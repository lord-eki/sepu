<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole, ShieldCheck } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const showPassword = ref(false);
const showConfirm = ref(false);

const form = useForm({
    password: '',
    password_confirmation: '',
});

const passwordStrength = computed(() => {
    const pass = form.password || '';

    if (pass.length < 6) return { text: 'Weak', width: 'w-1/3', color: 'bg-red-500' };
    if (pass.length < 10) return { text: 'Medium', width: 'w-2/3', color: 'bg-yellow-500' };

    return { text: 'Strong', width: 'w-full', color: 'bg-green-500' };
});

const passwordsMatch = computed(() => {
    return form.password && form.password_confirmation && form.password === form.password_confirmation;
});

const logout = () => {
    router.post(route('logout'));
};

const submit = () => {
    form.post(route('password.change.update'));
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 via-white to-blue-100 px-4">
        <div class="w-full max-w-md rounded-3xl border border-white/60 bg-white/90 p-8 shadow-2xl backdrop-blur">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-950 shadow-lg">
                    <ShieldCheck class="h-8 w-8 text-white" />
                </div>

                <h1 class="mt-4 text-2xl font-bold text-gray-900">Change Password</h1>

                <p class="mt-2 text-sm leading-relaxed text-gray-500">
                    For security reasons, you must create a new password before accessing your account.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Password -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"> New Password </label>

                    <div class="relative">
                        <LockKeyhole class="absolute top-3.5 left-4 h-5 w-5 text-gray-400" />

                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Enter new password"
                            class="w-full rounded-xl border border-gray-300 py-3 pr-12 pl-12 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-950"
                        />

                        <button type="button" @click="showPassword = !showPassword" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Strength -->
                    <div class="mt-3">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                            <div class="h-full transition-all duration-300" :class="[passwordStrength.width, passwordStrength.color]" />
                        </div>

                        <p class="mt-1 text-xs text-gray-500">Strength: {{ passwordStrength.text }}</p>
                    </div>

                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Confirm Password </label>

                    <div class="relative">
                        <LockKeyhole class="absolute top-3.5 left-4 h-5 w-5 text-gray-400" />

                        <input
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            placeholder="Confirm password"
                            class="w-full rounded-xl border border-gray-300 py-3 pr-12 pl-12 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-950"
                        />

                        <button type="button" @click="showConfirm = !showConfirm" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600">
                            <Eye v-if="!showConfirm" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>

                    <p v-if="form.password_confirmation" class="mt-2 text-xs" :class="passwordsMatch ? 'text-green-600' : 'text-red-600'">
                        {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
                    </p>
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-950 py-3 font-semibold text-white transition-all hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg v-if="form.processing" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>

                    {{ form.processing ? 'Updating...' : 'Update Password' }}
                </button>
            </form>
            <button
                type="button"
                @click="logout"
                class="mt-3 w-full rounded-xl border border-gray-300 py-3 font-semibold text-gray-700 transition-all hover:bg-gray-100"
            >
                Logout
            </button>
        </div>
    </div>
</template>
