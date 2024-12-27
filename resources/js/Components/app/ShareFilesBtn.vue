<template>
    <button
        @click="onClick"
        class="mr-3 inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:text-blue-700 focus:ring-2 focus:ring-blue-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:hover:text-white dark:focus:text-white dark:focus:ring-blue-500"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="mr-2 h-4 w-4"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"
            />
        </svg>

        Share
    </button>
    <ShareFilesModal v-model="showModal" :all-selected="allSelected" :selected-ids="selectedIds" />
</template>

<script setup>
// Imports
import { ref } from 'vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { emitter } from '@/event-bus';
import ShareFilesModal from '@/Components/app/ShareFilesModal.vue';
// Uses
const page = usePage();
const form = useForm({
    all: null,
    ids: [],
    parent_id: null,
});

// Refs

const showModal = ref(false);

// Props & Emit

const props = defineProps({
    allSelected: {
        type: Boolean,
        required: false,
        default: false,
    },
    selectedIds: {
        type: Array,
        required: false,
    },
});
const emit = defineEmits(['restore']);

// Computed

// Methods

function onClick() {
    if (!props.allSelected && !props.selectedIds.length) {
        emitter.emit('show-error', 'Please select files to share');
        return;
    }
    showModal.value = true;
}

// Hooks
</script>

<style scoped></style>
