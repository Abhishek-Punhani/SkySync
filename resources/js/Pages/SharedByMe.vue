<template>
    <AuthenticatedLayout>
        <nav class="mb-3 flex items-center justify-end p-1">
                <div>
                   <DownloadFilesButton
                    :all="allselected"
                    :ids="selectedIds"
                    class="mr-2"
                    :shared-by-me="true"
                />
            </div>
        </nav>
        <div class="flex-1 overflow-auto">
            <table class="min-w-full border dark:border-gray-800">
                <thead
                    class="border-b bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
                >
                    <tr>
                        <th
                            class="w-[30px] max-w-[30px] px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            <Checkbox
                                @change="toggleSelectAll"
                                v-model:checked="allselected"
                            />
                        </th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Name
                        </th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Path
                        </th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Created At
                        </th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Size
                        </th>
                    </tr>
                </thead>
                <tbody v-if="allFiles.data.length">
                    <tr
                        v-for="file of allFiles.data"
                        :key="file.id"
                        @click="toggleFileSelect(file)"
                        class="cursor-pointer border-b transition duration-300 ease-in-out hover:bg-blue-100 dark:border-gray-700 dark:hover:bg-gray-700"
                        :class="
                            selectedFiles[file.id] || allselected
                                ? 'bg-blue-100 dark:bg-gray-700'
                                : 'bg-white dark:bg-gray-900'
                        "
                    >
                        <td
                            class="w-[30px] max-w-[30px] gap-x-3 whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            <Checkbox
                                @change="($event) => ToggleCheckbox(file)"
                                v-model="selectedFiles[file.id]"
                                :checked="selectedFiles[file.id] || allselected"
                            />
                        </td>
                        <td
                            class="flex items-center justify-start gap-x-3 whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            <FileIcon :file="file" />
                            <span class="p-1">{{ file.name }}</span>
                        </td>
                        <td
                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ file.path }}
                        </td>
                        <td
                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ file.created_at }}
                        </td>
                        <td
                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ file.size }}
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td
                            class="text-md px-6 py-4 text-center font-medium text-gray-900 dark:text-gray-100"
                            colspan="6"
                        >
                            No files found
                        </td>
                    </tr>
                </tbody>
            </table>
            <div ref="loadMoreIntersect"></div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { HomeIcon } from '@heroicons/vue/20/solid';
import FileIcon from '@/Components/app/FileIcon.vue';
import { computed, onMounted, onUpdated, ref } from 'vue';
import { defineProps } from 'vue';
import { getFiles } from '@/utils/http';
import Checkbox from '@/Components/Checkbox.vue';
import { all } from 'axios';
import DeleteFilesButton from '@/Components/app/DeleteFilesButton.vue';
import DownloadFilesButton from '@/Components/app/DownloadFilesButton.vue';
import formatDate from '@/utils/date.js';
import RestoreFilesButton from '@/Components/app/RestoreFilesButton.vue';
import deleteForeverBtn from '@/Components/app/DeleteForeverBtn.vue';

const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
    folder: Object,
    ancestors: Object,
});

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next,
});
const loadMoreIntersect = ref(null);
const allselected = ref(false);
const selectedFiles = ref({});

const selectedIds = computed(() =>
    Object.entries(selectedFiles.value)
        .filter((a) => a[1])
        .map((a) => a[0]),
);

function toggleSelectAll() {
    allFiles.value.data.forEach((file) => {
        selectedFiles.value[file.id] = allselected.value;
    });
}

function toggleFileSelect(file) {
    if (selectedFiles.value[file.id])
        selectedFiles.value[file.id] = !selectedFiles.value[file.id];
    else selectedFiles.value[file.id] = true;

    ToggleCheckbox(file);
}

function ToggleCheckbox(file) {
    if (!selectedFiles.value[file.id]) {
        allselected.value = false;
    } else {
        let checked = true;
        for (const file of allFiles.value.data) {
            if (!selectedFiles.value[file.id]) {
                checked = false;
                break;
            }
        }
        allselected.value = checked;
    }
}

function loadMore() {
    console.log('load more');
    if (allFiles.value.next == null) return;

    getFiles(allFiles.value.next).then((res) => {
        allFiles.value.data = [...allFiles.value.data, ...res.data];
        allFiles.value.next = res.links.next;
    });
}
function resetForm() {
    allselected.value = false;
    selectedFiles.value = {};
}

onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next,
    };
});
onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) =>
            entries.forEach((entry) => entry.isIntersecting && loadMore()),
        {
            rootMargin: '-250px 0px 0px 0px',
        },
    );

    observer.observe(loadMoreIntersect.value);
});
</script>
