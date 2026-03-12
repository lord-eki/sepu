<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Separator } from '@/components/ui/separator'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'

const sidebarNavItems: NavItem[] = [
  { title: 'Profile', hrefs: ['/settings/profile', '/profile'] }, 
  { title: 'Password', hrefs: ['/settings/password'] },
  { title: 'Appearance', hrefs: ['/settings/appearance'] },
]

const page = usePage()

// Get the current pathname
const currentPath = new URL(page.props.ziggy.location).pathname

// Check if any of the hrefs matches the current URL
const isActive = (hrefs: string[]) => hrefs.includes(currentPath)
</script>

<template>
  <div class="px-4 py-6 sm:px-6">
    <Heading
      title="Settings"
      description="Manage your profile and account settings"
    />

    <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:gap-12">
      <!-- SIDEBAR -->
      <aside class="w-full lg:w-56">
        <nav class="flex gap-2 overflow-x-auto lg:flex-col lg:gap-1">
          <Link
            v-for="item in sidebarNavItems"
            :key="item.hrefs[0]"
            :href="item.hrefs[0]"
            class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-all
              lg:w-full lg:px-3 lg:py-2
              hover:bg-orange-50 hover:text-orange-700
              dark:hover:bg-orange-900/20 dark:hover:text-orange-400"
            :class="isActive(item.hrefs)
              ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
              : 'text-gray-700 dark:text-gray-300'"
          >
            {{ item.title }}
          </Link>
        </nav>
      </aside>

      <Separator class="lg:hidden" />

      <!-- CONTENT -->
      <div class="flex-1">
        <section class="max-w-2xl space-y-10">
          <slot />
        </section>
      </div>
    </div>
  </div>
</template>