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
import { ref } from 'vue'

defineProps<{
    items: NavItem[]
}>()

const page = usePage()

// Track open dropdowns by title
const openMenus = ref<Record<string, boolean>>({})

const toggleMenu = (title: string) => {
    openMenus.value[title] = !openMenus.value[title]
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- Parent with children -->
                <template v-if="item.children && item.children.length">
            
                    <SidebarMenuButton as-child :tooltip="item.title" @click="toggleMenu(item.title)">
                        <div class="flex items-center justify-between w-full cursor-pointer">
                            <div class="flex items-center gap-2">
                                <component :is="item.icon" class="h-4 w-4" />
                                <span>{{ item.title }}</span>
                            </div>
                            <component
                                :is="openMenus[item.title] ? ChevronDown : ChevronRight"
                                class="h-4 w-4"
                            />
                        </div>
                    </SidebarMenuButton>

                    <!-- Children links -->
                    <div v-if="openMenus[item.title]" class="ml-6 mt-1 space-y-1">
                        <SidebarMenuItem v-for="child in item.children" :key="child.title">
                            <SidebarMenuButton
                                as-child
                                :is-active="child.href === page.url"
                                :tooltip="child.title"
                            >
                            <Link :href="child.href || '#'" >
                            <component :is="child.icon" class="h-4 w-4" />
                            <span>{{ child.title }}</span>
                            </Link>

                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </div>
                </template>

                <!-- Normal single link -->
                <template v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="item.href === page.url"
                        :tooltip="item.title"
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
