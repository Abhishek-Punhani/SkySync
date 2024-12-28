<template>
    <div class="flex h-[80px] w-[600px] items-center">
        <TextInput
            type="text"
            class="mr-2 block w-full"
            v-model="search"
            autocomplete
            @input.prevent="searchFiles"
            ref="inputRef"
            placeholder="Search for files and folders"
        />
    </div>
</template>

<script setup>
// Imports
import TextInput from '@/Components/TextInput.vue';
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, onUpdated, ref } from 'vue';

const search = ref('');
let params = '';
const inputRef = ref(null);

const searchFiles = () => {
    if (search.value.trim() != '') {
        params.set('search', search.value);
        router.get(window.location.pathname + '?' + params.toString());
    }
};

onMounted(() => {
    params = new URLSearchParams(window.location.search);
    search.value = params.get('search') || '';
    if (search.value != '') inputRef.value.focus();
});

onUpdated(() => {
    if (search.value.trim() === '') {
        params.delete('search');

        router.get(window.location.pathname);
    }
});
</script>

<style scoped></style>
