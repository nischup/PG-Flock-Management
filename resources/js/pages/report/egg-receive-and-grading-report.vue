<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Hello World" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Home', href: '/' },
            { title: 'Hello World', href: '/hello-world' },
        ]"
    >
        <div class="p-6 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Hello World!</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">This is your first Vue + Inertia page.</p>
        </div>
    </AppLayout>
</template>
