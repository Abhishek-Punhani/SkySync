<template>
    <div class="flex h-[80px] w-[600px] items-center">
        <TextInput
            type="text"
            class="mr-2 block w-full"
            v-model="search"
            autocomplete
            placeholder="Search for files and folders"
        />
    </div>
</template>

<script setup>
// Imports
import TextInput from '@/Components/TextInput.vue';
</script>

<style scoped></style>
