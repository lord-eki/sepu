<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Separator } from '@/components/ui/separator'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'

const sidebarNavItems: NavItem[] = [
  { title: 'Profile', href: '/settings/profile' },
  { title: 'Password', href: '/settings/password' },
  { title: 'Appearance', href: '/settings/appearance' },
]

const page = usePage()

const currentPath = page.props.ziggy?.location
  ? new URL(page.props.ziggy.location).pathname
  : '/settings/profile'
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
        <!-- Mobile: horizontal pills | Desktop: vertical -->
        <nav
          class="flex gap-2 overflow-x-auto lg:flex-col lg:gap-1"
        >
          <Link
            v-for="item in sidebarNavItems"
            :key="item.href"
            :href="item.href"
            class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-all
              lg:w-full lg:px-3 lg:py-2
              hover:bg-orange-50 hover:text-orange-700
              dark:hover:bg-orange-900/20 dark:hover:text-orange-400"
            :class="
              currentPath === item.href
                ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'
                : 'text-gray-700 dark:text-gray-300'
            "
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
