<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

// Calculate profile completion percentage
const profileCompletion = computed(() => {
    let completed = 0;
    const total = 4; // name, email, company, shed
    
    if (user.name) completed++;
    if (user.email) completed++;
    if (user.company_id) completed++;
    if (user.shed_id) completed++;
    
    return Math.round((completed / total) * 100);
});

// Get user initials for avatar
const userInitials = computed(() => {
    return user.name
        ?.split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase() || 'U';
});

// Format join date
const joinDate = computed(() => {
    return new Date(user.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-8">
                <!-- Profile Hero Section -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-8 dark:from-blue-950/20 dark:via-indigo-950/20 dark:to-purple-950/20">
                    <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
                    <div class="relative flex flex-col items-center space-y-6 text-center">
                        <!-- Avatar -->
                        <div class="relative">
                            <div class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-2xl font-bold text-white shadow-lg ring-4 ring-white/20">
                                {{ userInitials }}
                            </div>
                            <div class="absolute -bottom-1 -right-1 h-6 w-6 rounded-full bg-green-500 border-2 border-white shadow-sm"></div>
                        </div>
                        
                        <!-- User Info -->
                        <div class="space-y-2">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ user.name || 'User' }}</h1>
                            <p class="text-lg text-gray-600 dark:text-gray-300">{{ user.email }}</p>
                            <div class="flex items-center justify-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                <span>Member since {{ joinDate }}</span>
                                <span>•</span>
                                <span>Profile {{ profileCompletion }}% complete</span>
                            </div>
                        </div>

                        <!-- Profile Completion Progress -->
                        <div class="w-full max-w-xs">
                            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300 mb-2">
                                <span>Profile completion</span>
                                <span>{{ profileCompletion }}%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div 
                                    class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-500 ease-out"
                                    :style="{ width: `${profileCompletion}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card class="border-0 shadow-sm bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-950/20 dark:to-emerald-950/20">
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Account Status</p>
                                    <p class="text-2xl font-bold text-green-700 dark:text-green-300">Active</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-0 shadow-sm bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/20 dark:to-cyan-950/20">
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Email Status</p>
                                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                        {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-0 shadow-sm bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-950/20 dark:to-pink-950/20">
                        <CardContent class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Profile Complete</p>
                                    <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ profileCompletion }}%</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Profile Information Form -->
                <Card class="border-0 shadow-sm">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-xl font-semibold">Profile Information</CardTitle>
                        <CardDescription>Update your personal information and account details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="name" class="text-sm font-medium">Full Name</Label>
                                    <Input 
                                        id="name" 
                                        v-model="form.name" 
                                        required 
                                        autocomplete="name" 
                                        placeholder="Enter your full name"
                                        class="h-11"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email" class="text-sm font-medium">Email Address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        autocomplete="username"
                                        placeholder="Enter your email address"
                                        class="h-11"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>

                            <!-- Email Verification Status -->
                            <div v-if="mustVerifyEmail && !user.email_verified_at" class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/20">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Email Verification Required</h3>
                                        <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                            Your email address is unverified. 
                                            <Link
                                                :href="route('verification.send')"
                                                method="post"
                                                as="button"
                                                class="font-medium underline hover:no-underline"
                                            >
                                                Click here to resend the verification email.
                                            </Link>
                                        </p>
                                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                                            ✓ A new verification link has been sent to your email address.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <Button type="submit" :disabled="form.processing" class="px-8">
                                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                    </Button>

                                    <Transition
                                        enter-active-class="transition ease-in-out duration-300"
                                        enter-from-class="opacity-0 scale-95"
                                        enter-to-class="opacity-100 scale-100"
                                        leave-active-class="transition ease-in-out duration-200"
                                        leave-from-class="opacity-100 scale-100"
                                        leave-to-class="opacity-0 scale-95"
                                    >
                                        <div v-show="form.recentlySuccessful" class="flex items-center space-x-2 text-sm text-green-600 dark:text-green-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span>Changes saved successfully</span>
                                        </div>
                                    </Transition>
                                </div>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Danger Zone -->
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
