<template>
    <button
        @click="download"
        class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:text-blue-700 focus:ring-2 focus:ring-blue-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:hover:text-white dark:focus:text-white dark:focus:ring-blue-500"
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
                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"
            />
        </svg>
        Download
    </button>
</template>

<script setup>
// Imports
import { useForm, usePage } from '@inertiajs/vue3';
import { getFiles } from '@/utils/http';

// Uses
const page = usePage();

// Refs

// Props & Emit
const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false,
    },
    ids: {
        type: Array,
        required: false,
    },
    sharedWithMe: false,
    sharedByMe: false,
});

// Computed

// Methods

function download() {
    if (!props.all && props.ids.length === 0) {
        return;
    }

    const p = new URLSearchParams();
    if (page.props.folder?.id) {
        p.append('parent_id', page.props.folder?.id);
    }

    if (props.all) {
        p.append('all', props.all ? 1 : 0);
    } else {
        for (let id of props.ids) {
            p.append('ids[]', id);
        }
    }

    let url = route('file.download');
    if (props.sharedWithMe) {
        url = route('file.downloadSharedWithMe');
    } else if (props.sharedByMe) {
        url = route('file.downloadSharedByMe');
    }

    getFiles(url + '?' + p.toString()).then((res) => {
        console.log(res);
        if (!res.data.url) return;

        const a = document.createElement('a');
        a.download = res.data.filename;
        a.href = res.data.url;
        a.click();
    });
}

// Hooks
</script>

<style scoped></style>
