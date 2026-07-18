<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue'
import {
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import type { User } from '@/types'
import { router, Link } from '@inertiajs/vue3'
import { LogOut, Settings } from 'lucide-vue-next'
import { ref } from 'vue'

interface Props {
  user: User
}

defineProps<Props>()

// Modal state
const showLogoutModal = ref(false)
const loggingOut = ref(false)

// Reference to the dropdown wrapper
const dropdownRef = ref<HTMLElement | null>(null)

// Logout
const handleLogout = async () => {
  loggingOut.value = true
  try {
    await router.post(route('logout'), {}, { preserveState: false })
    // Redirect handled by Inertia
  } finally {
    loggingOut.value = false
  }
}

// Open modal and close dropdown
const openLogoutModal = () => {
  showLogoutModal.value = true
  closeDropdown()
}

// Close dropdown by dispatching event
const closeDropdown = () => {
  if (dropdownRef.value) {
    const evt = new CustomEvent('close-dropdown', { bubbles: true })
    dropdownRef.value.dispatchEvent(evt)
  }
}

// Cancel logout: closes modal AND dropdown
const cancelLogout = () => {
  showLogoutModal.value = false
  closeDropdown()
}
</script>

<template>
  <div ref="dropdownRef">
    <!-- User Info -->
    <DropdownMenuLabel class="p-0 font-normal">
      <div class="flex items-center gap-2 px-2 py-2 text-sm">
        <UserInfo :user="user" :show-email="true" />
      </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator />

    <!-- Settings -->
    <DropdownMenuGroup>
      <DropdownMenuItem as-child>
      <Link href="/profile" class="flex items-center gap-2 w-full">
        <Settings class="h-4 w-4" />
        Settings
      </Link>
    </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator />

    <!-- Logout Trigger -->
    <DropdownMenuItem @select.prevent>
      <button
        type="button"
        class="flex items-center gap-2 w-full text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg px-2 py-1 transition"
        @click.stop="openLogoutModal"
      >
        <LogOut class="h-4 w-4" />
        Log out
      </button>
    </DropdownMenuItem>
  </div>

  <!-- Logout Modal -->
  <div>
    <transition name="fade">
      <div
        v-if="showLogoutModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      >
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            Confirm Logout
          </h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Are you sure you want to log out? You will need to log in again to access your account.
          </p>

          <div class="mt-6 flex justify-end gap-3">
            <Button variant="outline" @click="cancelLogout" :disabled="loggingOut">
              Cancel
            </Button>

            <Button
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg"
              @click.stop="handleLogout"
              :disabled="loggingOut"
            >
              <span v-if="loggingOut">Logging out...</span>
              <span v-else>Log out</span>
            </Button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

button:hover {
  cursor: pointer;
}
</style>