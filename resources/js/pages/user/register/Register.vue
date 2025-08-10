<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Registration',
        href: '/user/register',
    },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="User Registration" />
        <div class="px-4 py-6">
            <Heading title="Page 1 data heading" description="Laravel explore to new era" />
            <h1> welocme to register page </h1>
        </div>
    </AppLayout>
</template>
