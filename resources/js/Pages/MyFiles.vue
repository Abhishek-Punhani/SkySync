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
        </nav>
        <div class="flex-1 overflow-auto">
            <table class="min-w-full border dark:border-gray-800">
                <thead
                    class="border-b bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
                >
                    <tr>
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
                        @dblclick="openFolder(file)"
                        class="cursor-pointer border-b bg-white transition duration-300 ease-in-out hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-700"
                    >
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
                            colspan="4"
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
import { onMounted, ref } from 'vue';
import { defineProps } from 'vue';
import { getFiles } from '@/utils/http';

const loadMoreIntersect=ref(null)


function loadMore(){
    console.log('load more')
    if(allFiles.value.next==null) return;

    getFiles(allFiles.value.next).then(res => {
        allFiles.value.data = [...allFiles.value.data, ...res.data];
        allFiles.value.next = res.links.next;
    });
}

const props= defineProps({
    files: {
        type: Object,
        required: true,
    },
    folder: Object,
    ancestors: Object,
});


const allFiles = ref({
    data:props.files.data,
    next:props.files.links.next,
});
const openFolder = (file) => {
    if (!file.is_folder) {
        return;
    }

    router.visit(`/my-files/${file.path}`);
};
onMounted(() => {
    const observer = new IntersectionObserver((entries) => entries.forEach(entry => entry.isIntersecting && loadMore()), {
        rootMargin: '-250px 0px 0px 0px'
    })

    observer.observe(loadMoreIntersect.value)
})

</script>
