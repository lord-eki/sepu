<script setup lang="ts">
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'
import { ChevronDown, ChevronRight } from 'lucide-vue-next'
import { ref, watch, onMounted } from 'vue'

const props = defineProps<{ items: NavItem[] }>()
const page = usePage()

// Track open dropdowns by title
const openMenus = ref<Record<string, boolean>>({})

// Toggle parent menu
const toggleMenu = (title: string) => {
  openMenus.value[title] = !openMenus.value[title]
}

// Keep parent menu open when child link is active
const setActiveParents = () => {
  const currentUrl = page.url
  for (const item of props.items) {
    if (item.children?.length) {
      const hasActiveChild = item.children.some(
        (child) => route().current(child.routeName) || currentUrl.startsWith(child.href || '')
      )
      if (hasActiveChild) {
        openMenus.value[item.title] = true
      }
    }
  }
}

// Run when page changes
watch(() => page.url, setActiveParents, { immediate: true })

// Ensure it runs once on mount (useful for refresh)
onMounted(setActiveParents)
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>Platform</SidebarGroupLabel>
    <SidebarMenu>
      <SidebarMenuItem v-for="item in props.items" :key="item.title">
        <!-- Parent with children -->
        <template v-if="item.children && item.children.length">
          <!-- Parent Button -->
          <SidebarMenuButton
            as-child
            :tooltip="item.title"
            @click.stop="toggleMenu(item.title)"
          >
            <div class="flex items-center justify-between w-full cursor-pointer select-none">
              <div class="flex items-center gap-2">
                <component :is="item.icon" class="h-4 w-4" />
                <span>{{ item.title }}</span>
              </div>
              <component
                :is="openMenus[item.title] ? ChevronDown : ChevronRight"
                class="h-4 w-4 transition-transform"
              />
            </div>
          </SidebarMenuButton>

          <!-- Children -->
          <transition name="fade">
            <div v-show="openMenus[item.title]" class="ml-6 mt-1 space-y-1">
              <SidebarMenuItem
                v-for="child in item.children"
                :key="child.title"
              >
                <SidebarMenuButton
                  as-child
                  :tooltip="child.title"
                  :class="[
                    route().current(child.routeName)
                      ? 'bg-orange-100 text-orange-600 font-semibold rounded-md'
                      : 'hover:bg-orange-50',
                  ]"
                >
                  <Link
                    :href="child.href || '#'"
                    preserve-scroll
                    preserve-state
                  >
                    <component :is="child.icon" class="h-4 w-4" />
                    <span>{{ child.title }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </div>
          </transition>
        </template>

        <!-- Normal single link -->
        <template v-else>
          <SidebarMenuButton
            as-child
            :tooltip="item.title"
            :class="[
              route().current(item.routeName)
                ? 'bg-orange-100 text-orange-600 font-semibold rounded-md'
                : 'hover:bg-orange-50',
            ]"
          >
            <Link :href="item.href">
              <component :is="item.icon" />
              <span>{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>
        </template>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>
