<template>
<Modal :show="show" max-width="md">
    <div class="p-6 bg-white dark:bg-gray-800">
        <h2 class="text-2xl mb-2 font-semibold text-gray-900 dark:text-gray-100">Please Confirm</h2>
        <p class="text-gray-700 dark:text-gray-300">{{message}}</p>

        <div class="mt-6 flex justify-end">
            <SecondaryButton @click="$event => emit('cancel')">Cancel</SecondaryButton>

            <DangerButton class="ml-3" @click="$event => emit('confirm')">
                Confirm
            </DangerButton>
        </div>
    </div>
</Modal>
</template>

<script setup>
// Imports
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

// Uses

// Refs

// Props & Emit
const props = defineProps({
    show: Boolean,
    message: String
})
const emit = defineEmits(['cancel', 'confirm'])

// Computed

// Methods

// Hooks

</script>

<style scoped>

</style>
