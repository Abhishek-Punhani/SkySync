<script setup>
import Navigation from '@/Components/app/Navigation.vue';
import SearchForm from '@/Components/app/SearchForm.vue';
import UserSettingsComponent from '@/Components/app/UserSettingsComponent.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue'; // Import the toggle button
</script>

<template>
    <div class="flex h-screen w-full gap-4 bg-gray-100 dark:bg-gray-900">
        <Navigation />
        <main class="flex flex-1 flex-col overflow-hidden px-4">
            <div class="flex w-full items-center justify-between">
                <SearchForm />
                <div class="flex items-center gap-4">
                    <DarkModeToggle />
                    <UserSettingsComponent />
                </div>
            </div>
            <div class="flex flex-1 flex-col overflow-hidden">
                <slot />
            </div>
        </main>
    </div>
</template>
