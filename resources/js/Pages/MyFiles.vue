<template>
    <AuthenticatedLayout>
        <nav class="mb-3 flex items-center justify-between p-1">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li
                    v-for="ans of ancestors.data"
                    :key="ans.id"
                    class="inline-flex items-center"
                >
                    <Link
                        v-if="!ans.parent_id"
                        :href="route('myFiles')"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
                    >
                        <HomeIcon
                            class="h-6 w-6 px-1 text-gray-700 dark:text-gray-400"
                        />
                        My Files
                    </Link>
                    <div v-else class="flex items-center">
                        <svg
                            aria-hidden="true"
                            class="h-5 w-5 text-gray-400 dark:text-gray-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <Link
                            :href="route('myFiles', { folder: ans.path })"
                            class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white md:ml-2"
                        >
                            {{ ans.name }}
                        </Link>
                    </div>
                </li>
            </ol>
            <div>
                <ShareFilesBtn
                    :all-selected="allselected"
                    :selected-ids="selectedIds"
                />
                <DownloadFilesButton
                    :all="allselected"
                    :ids="selectedIds"
                    class="mr-2"
                />
                <DeleteFilesButton
                    :delete-all="allselected"
                    :delete-ids="selectedIds"
                    @delete="onDelete"
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
                            class="w-[40px] max-w-[40px] px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        ></th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Name
                        </th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Owner
                        </th>
                        <th
                            class="px-6 py-4 text-left text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Last Modified
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
                        @dblclick="openFolder(file)"
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
                            class="max-w-[40px] gap-x-3 px-6 py-4 text-sm font-medium text-gray-900 text-yellow-500 dark:text-gray-100"
                        >
                            <div @click.stop.prevent="addRemoveFav(file)">
                                <svg
                                    v-if="!file.is_favorite"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="h-6 w-6"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
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
                            {{ file.owner }}
                        </td>
                        <td
                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ file.updated_at }}
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
import { Link, router, useForm } from '@inertiajs/vue3';
import { HomeIcon } from '@heroicons/vue/20/solid';
import FileIcon from '@/Components/app/FileIcon.vue';
import { computed, onMounted, onUpdated, ref } from 'vue';
import { defineProps } from 'vue';
import { getFiles } from '@/utils/http';
import Checkbox from '@/Components/Checkbox.vue';
import { all } from 'axios';
import DeleteFilesButton from '@/Components/app/DeleteFilesButton.vue';
import DownloadFilesButton from '@/Components/app/DownloadFilesButton.vue';
import { emitter } from '@/event-bus';
import ShareFilesBtn from '@/Components/app/ShareFilesBtn.vue';
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
function onDelete() {
    allselected.value = false;
    selectedFiles.value = {};
}
const openFolder = (file) => {
    if (!file.is_folder) {
        return;
    }

    router.visit(`/my-files/${file.path}`);
};

function addRemoveFav(file) {
    const form = useForm({
        id: file.id,
    });
    const is_favorite = file.is_favorite ? 0 : 1;

    form.post(route('file.addtoFavorites'), {
        onSuccess: () => {
            if (is_favorite) {
                emitter.emit('show-notif', {
                    type: 'success',
                    message: 'File added to favorites',
                });
            } else {
                emitter.emit('show-notif', {
                    type: 'warning',
                    message: 'File removed from favorites',
                });
            }
        },
    });
}
function clearSelection(event) {
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
