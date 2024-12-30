route_name
<template>
    <nav class="min-w-[200px] p-5 dark:text-gray-200">
        <Link :href="route('dashboard')">
            <ApplicationLogo
                class="block h-9 w-auto fill-current text-gray-800 dark:text-white"
            />
            LaraStorage
        </Link>

        <div class="px-3 py-8">
            <CreateNewDropdown />

            <div class="mt-5 flex flex-col gap-y-2 py-3">
                <NavLink
                    :href="route('myFiles')"
                    :active="$page.props.route_name === 'myFiles'"
                    >My Files</NavLink
                >
                <NavLink
                    :href="route('sharedWithMe')"
                    :active="$page.props.route_name == 'sharedWithMe'"
                    >Shared with me</NavLink
                >
                <NavLink
                    :href="route('sharedByMe')"
                    :active="$page.props.route_name == 'sharedByMe'"
                    >Shared by me</NavLink
                >
                <NavLink
                    href="/starred-files"
                    :active="$page.props.route_name == 'starredFiles'"
                    >Starred Files
                </NavLink>
                <NavLink
                    :href="route('trash')"
                    :active="$page.props.route_name === 'trash'"
                    >Trash</NavLink
                >
            </div>
        </div>
    </nav>
</template>

<script setup>
// Imports
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CreateNewDropdown from '@/Components/app/CreateNewDropdown.vue';
import NavLink from '@/Components/NavLink.vue';

// Uses

// Refs
</script>
