<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <Card class="border-red-200 dark:border-red-800/50 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-950/20 dark:to-rose-950/20">
        <CardHeader class="pb-4">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <CardTitle class="text-lg text-red-800 dark:text-red-200">Danger Zone</CardTitle>
                    <CardDescription class="text-red-600 dark:text-red-300">Permanently delete your account and all of its data</CardDescription>
                </div>
            </div>
        </CardHeader>
        <CardContent>
            <div class="space-y-4">
                <div class="rounded-lg border border-red-200 bg-red-50/50 p-4 dark:border-red-800/30 dark:bg-red-900/10">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Warning</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                This action cannot be undone. This will permanently delete your account and remove all data from our servers.
                            </p>
                        </div>
                    </div>
                </div>

                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="destructive" class="w-full sm:w-auto">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Account
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <form class="space-y-6" @submit="deleteUser">
                            <DialogHeader class="space-y-3">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <DialogTitle class="text-lg">Delete Account</DialogTitle>
                                        <DialogDescription class="text-sm text-gray-600 dark:text-gray-400">
                                            This action cannot be undone
                                        </DialogDescription>
                                    </div>
                                </div>
                            </DialogHeader>

                            <div class="space-y-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Once your account is deleted, all of its resources and data will be permanently deleted. 
                                    Please enter your password to confirm you would like to permanently delete your account.
                                </p>

                                <div class="space-y-2">
                                    <Label for="password" class="text-sm font-medium">Password</Label>
                                    <Input 
                                        id="password" 
                                        type="password" 
                                        name="password" 
                                        ref="passwordInput" 
                                        v-model="form.password" 
                                        placeholder="Enter your password to confirm"
                                        class="h-11"
                                    />
                                    <InputError :message="form.errors.password" />
                                </div>
                            </div>

                            <DialogFooter class="gap-3">
                                <DialogClose as-child>
                                    <Button variant="outline" @click="closeModal" class="w-full sm:w-auto">
                                        Cancel
                                    </Button>
                                </DialogClose>

                                <Button 
                                    type="submit" 
                                    variant="destructive" 
                                    :disabled="form.processing"
                                    class="w-full sm:w-auto"
                                >
                                    <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Deleting...' : 'Delete Account' }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </CardContent>
    </Card>
</template>
