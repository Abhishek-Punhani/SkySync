<template>
    <MenuItem as="div" v-slot="{ active }">
        <a
            href="#"
            @click="openInput"
            class="block px-4 py-2 text-sm"
            :class="[
                active
                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                    : 'text-gray-700 dark:text-gray-300',
            ]"
        >
            Upload Folder
        </a>
    </MenuItem>
    <input
        ref="inputref"
        @change="onChange"
        type="file"
        class="absolute bottom-0 left-0 right-0 top-0 hidden cursor-pointer"
        multiple
        directory
        webkitdirectory
    />
</template>

<script setup>
// Imports
import { emitter } from '@/event-bus';
import { MenuItem } from '@headlessui/vue';
import { ref } from 'vue';
const inputref = ref(null);

function openInput() {
    inputref.value.click();
}

function onChange(e) {
    console.log(e.target.files);
    emitter.emit('file-upload', e.target.files);
}
</script>

<style scoped></style>
