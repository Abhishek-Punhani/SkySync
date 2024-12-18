<template>
    <AuthenticatedLayout>
        <table
            v-if="files.data.length"
            class="min-w-full border dark:border-gray-800"
        >
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
            <tbody>
                <tr
                    v-for="file of files.data"
                    :key="file.id"
                    @dblclick="openFolder(file)"
                    class="border-b bg-white transition duration-300 ease-in-out hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-700"
                >
                    <td
                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                    >
                        {{ file.name }}
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
        </table>
        <div v-else class="pu-8 text-center text-lg text-gray-400">
            There's no data to show in this folder
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { files } = defineProps({
    files: {
        type: Object,
        required: true,
    },
});
</script>
