<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <MenuButton ref="menuButtonRef"
                class="flex w-full justify-center gap-x-1.5 rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 ring-1 ring-inset ring-gray-800 hover:bg-opacity-30 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-300 dark:hover:bg-opacity-30">
                Create New
            </MenuButton>
        </div>

        <transition enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <MenuItems
                class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:divide-gray-700 dark:bg-gray-800 dark:ring-white dark:ring-opacity-10">
                <div class="px-1 py-1">
                    <MenuItem v-slot="{ active }">
                    <a href="#" :class="[
                        active
                            ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                            : 'text-gray-700 dark:text-gray-300',
                        'block px-4 py-2 text-sm',
                    ]" @click.prevent="showCreateFolderModal">
                        New Folder
                    </a>
                    </MenuItem>
                </div>
                <div class="px-1 py-1">
                    <FileUploadMenuItem />
                    <FolderUploadMenuItem />
                </div>
            </MenuItems>
        </transition>
    </Menu>
    <CreateFolderModal v-model="createFolderModel" :closeDropdown="clickMenuButton" />
</template>

<script setup>
// Imports
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import FileUploadMenuItem from './FileUploadMenuItem.vue';
import FolderUploadMenuItem from './FolderUploadMenuItem.vue';
import CreateFolderModal from './CreateFolderModal.vue';
import { ref } from 'vue';

const createFolderModel = ref(false);

function showCreateFolderModal() {
    createFolderModel.value = true;
}
const menuButtonRef = ref(null);

function clickMenuButton() {
    if (menuButtonRef.value) {
        menuButtonRef.value.$el.click();
    }
}
</script>
<style scoped></style>
