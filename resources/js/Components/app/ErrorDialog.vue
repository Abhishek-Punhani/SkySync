<template>
    <Modal :show="show" max-width="md">
        <div
            class="p-6"
            :class="{
                'bg-white text-black dark:bg-gray-800 dark:text-white': true,
            }"
        >
            <h2 class="mb-2 text-2xl font-semibold text-red-600">Error</h2>
            <p class="dark:text-gray-300">{{ message }}</p>
            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="close">OK</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
// Imports
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { onMounted, ref } from 'vue';
import { emitter } from '@/event-bus.js';

// Uses

// Refs
const show = ref(false);
const message = ref('');

// Props & Emit
// eslint-disable-next-line no-unused-vars
const emit = defineEmits(['close']);

// Computed

// Methods
function close() {
    show.value = false;
    message.value = '';
}

// Hooks
onMounted(() => {
    emitter.on('show-error', (msg) => {
        show.value = true;
        message.value = msg;
    });
});
</script>

<style scoped></style>
