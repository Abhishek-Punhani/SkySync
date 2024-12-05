<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <MenuButton
                class="flex w-full justify-center gap-x-1.5 rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 ring-1 ring-inset ring-gray-800 hover:bg-opacity-30 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-300 dark:hover:bg-opacity-30"
            >
                Create New
            </MenuButton>
        </div>

        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems
                class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:divide-gray-700 dark:bg-gray-800 dark:ring-white dark:ring-opacity-10"
            >
                <div class="px-1 py-1">
                    <MenuItem v-slot="{ active }">
                        <ResponsiveNavLink
                            href="/"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                                    : 'text-gray-700 dark:text-gray-300',
                                'block px-4 py-2 text-sm',
                            ]"
                        >
                            New Folder
                        </ResponsiveNavLink>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                        <ResponsiveNavLink
                            href="/"
                            method="post"
                            as="button"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                                    : 'text-gray-700 dark:text-gray-300',
                                'block px-4 py-2 text-sm',
                            ]"
                        >
                            Upload Files
                        </ResponsiveNavLink>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                        <ResponsiveNavLink
                            href="/"
                            method="post"
                            as="button"
                            :class="[
                                active
                                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                                    : 'text-gray-700 dark:text-gray-300',
                                'block px-4 py-2 text-sm',
                            ]"
                        >
                            Upload Folder
                        </ResponsiveNavLink>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>

<script setup>
// Imports
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
</script>
<style scoped></style>
