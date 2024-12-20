<template>
    <MenuItem as="div" v-slot="{ active }">
        <a
            @click="openInput"
            href="#"
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
        </a>
    </MenuItem>
    <input
        ref="inputref"
        @change="onChange"
        type="file"
        class="absolute bottom-0 left-0 right-0 top-0 hidden cursor-pointer"
        multiple
    />
</template>

<script setup>
import { MenuItem } from '@headlessui/vue';
import { ref } from 'vue';
import { emitter } from '@/event-bus.js';

const inputref = ref(null);

function openInput() {
    inputref.value.click();
}

function onChange(e) {
    emitter.emit('file-upload', e.target.files);
}
</script>

<style scoped></style>
