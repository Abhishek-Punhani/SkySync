<template>
    <button
        @click="toggleDarkMode"
        class="rounded-md bg-gray-200 p-2 dark:bg-gray-800"
    >
        <MoonIcon v-if="isDarkMode" class="h-5 w-5 text-white" />
        <SunIcon v-else class="h-5 w-5 text-black" />
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { SunIcon, MoonIcon } from '@heroicons/vue/20/solid';

const isDarkMode = ref(false);

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    if (localStorage.getItem('theme') === 'dark') {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
});
</script>

<style scoped>
button {
    transition: background-color 0.3s;
}
</style>
