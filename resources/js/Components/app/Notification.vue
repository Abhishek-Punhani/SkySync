<template>
    <transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div
            v-if="show"
            class="fixed bottom-4 left-4 w-[200px] rounded-lg px-4 py-2 text-white shadow-md"
            :class="{
                'bg-emerald-500 dark:bg-emerald-700': type === 'success',
                'bg-red-500 dark:bg-red-700': type === 'error',
                'bg-yellow-500 dark:bg-yellow-700': type === 'warning',
            }"
        >
            {{ message }}
        </div>
    </transition>
</template>
<script setup>
// Imports
import { onMounted, ref } from 'vue';
import { emitter } from '@/event-bus.js';

// Uses

// Refs

const show = ref(false);
const type = ref('success');
const message = ref('');

// Props & Emit

// Computed

// Methods

function close() {
    show.value = false;
    type.value = '';
    message.value = '';
}

// Hooks
onMounted(() => {
    let timeout;
    emitter.on('show-notif', ({ type: t, message: msg }) => {
        show.value = true;
        type.value = t;
        message.value = msg;

        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => {
            close();
        }, 5000);
    });
});
</script>

<style scoped></style>
