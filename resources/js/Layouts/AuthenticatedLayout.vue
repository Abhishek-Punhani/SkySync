<template>
    <div class="flex h-screen w-full gap-4 bg-gray-100 dark:bg-gray-900">
        <Navigation />
        <main
            class="flex flex-1 flex-col overflow-hidden px-4"
            :class="dragOver ? 'dropzone' : ''"
        >
            <template v-if="dragOver">
                <div class="py-8 text-center text-sm text-gray-500">
                    Drop files here to upload
                </div>
            </template>
            <template v-else>
                <div class="flex w-full items-center justify-between">
                    <SearchForm />
                    <div class="flex items-center gap-4">
                        <DarkModeToggle />
                        <UserSettingsComponent />
                    </div>
                </div>
                <div
                    @drop.prevent="handleDrop"
                    @dragover.prevent="onDragOver"
                    @dragleave.prevent="onDragLeave"
                    class="flex flex-1 flex-col overflow-hidden"
                >
                    <slot />
                </div>
            </template>
        </main>
    </div>
</template>

<script setup>
import Navigation from '@/Components/app/Navigation.vue';
import SearchForm from '@/Components/app/SearchForm.vue';
import UserSettingsComponent from '@/Components/app/UserSettingsComponent.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import { emitter } from '@/event-bus.js';
import { ref, onMounted } from 'vue';

const dragOver = ref(false);

const uploadFiles = (files) => {
    console.log(files);
};
const handleDrop = (e) => {
    dragOver.value = false;

    const files = e.dataTransfer.files;
    if (!files.length) return;
    uploadFiles(files);
};
const onDragOver = () => {
    dragOver.value = true;
};

const onDragLeave = () => {
    dragOver.value = false;
};

// Debounce function to limit how often the dragOver state changes
const debounce = (func, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
};
debounce(onDragOver, 500);
debounce(onDragLeave, 500);

onMounted(() => {
    emitter.on('file-upload', uploadFiles);
});
</script>

<style scoped>
.dropzone {
    width: 100%;
    height: 100%;
    background-color: #8d8d8d;
    border: 2px dashed gray;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.dark .dropzone {
    background-color: #4b5563;
    border-color: #6b6b6b;
}
</style>
