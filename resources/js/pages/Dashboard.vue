<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import ChartCard from "../components/ChartCard.vue";
import { ref } from "vue";
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Labels: last 7 days
const chartLabels = Array.from({ length: 7 }, (_, i) => {
  const d = new Date();
  d.setDate(d.getDate() - (6 - i));
  return d.toISOString().split('T')[0]; // YYYY-MM-DD
});

// Dummy data for charts
const eggCollection = [{ name: 'Egg Collection', data: [5520, 8950, 5420, 7966, 2550, 4120, 6340] }];
const Hatching = [{ name: 'Hatching', data: [5420, 4120, 6340, 5520, 5520, 8950, 2550] }];
const Revenue = [{ name: 'Revenue', data: [17000000, 120000000, 1100000000, 160000000, 1300000000, 1700000000, 120000000] }];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <!-- Bar Chart -->
                    <ChartCard
                    :series="eggCollection"
                    :labels="chartLabels"
                    type="bar"
                    title="Daily Egg Collection"
                    />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <!-- Line Chart -->
                    <ChartCard
                    :series="Hatching"
                    :labels="chartLabels"
                    type="line"
                    title="Daily Hatchable Egg"
                    />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <!-- Area Chart -->
                    <ChartCard
                    :series="Revenue"
                    :labels="chartLabels"
                    type="area"
                    title="Daily Revenue"
                    />
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                        class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-5
                                bg-gradient-to-r from-yellow-300 to-yellow-500 dark:from-yellow-600 dark:to-yellow-500 text-gray-900 dark:text-white flex items-center justify-between"
                        >
                        <!-- Text -->
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                            Running Flock Chicken
                            </p>
                            <p class="text-3xl font-extrabold mt-1">12,51,478.00 Pcs</p>
                        </div>

                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-16 h-16 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center shadow-inner"
                        >
                        </div>

                        <!-- Optional Background Pattern -->
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-yellow-400 opacity-20 rounded-full -translate-x-1/3 -translate-y-1/3 pointer-events-none"
                        ></div>
                </div>
                <div class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-5
                            bg-gradient-to-r from-green-300 to-green-500 dark:from-green-600 dark:to-green-500 text-gray-900 dark:text-white flex items-center justify-between h-full"
                    >
                        <!-- Text -->
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                            Daily Consumption Cost
                            </p>
                            <p class="text-3xl font-extrabold mt-1">14,50,365.00 BDT </p>
                        </div>

                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-16 h-16 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center shadow-inner"
                        >
                            <!-- Food & Water Icon (plate and water droplet) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-green-600 dark:text-green-200" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 3h18v2H3V3zm2 6h14v2H5V9zm-2 6h18v2H3v-2zM5 21h14v2H5v-2z"/>
                            </svg>
                        </div>

                        <!-- Optional Background Pattern -->
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-green-400 opacity-20 rounded-full -translate-x-1/3 -translate-y-1/3 pointer-events-none"
                        >
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-5
                            bg-gradient-to-r from-red-200 to-red-400 dark:from-red-600 dark:to-red-500 text-gray-900 dark:text-white flex items-center justify-between h-full"
                    >
                    <!-- Text -->
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        Average Revenue Earning
                        </p>
                        <p class="text-3xl font-extrabold mt-1">৳ 25,14,780.00</p>
                    </div>

                    <!-- Icon -->
                    <div
                        class="flex-shrink-0 w-16 h-16 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center shadow-inner"
                    >
                        <!-- Revenue / Money Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-red-600 dark:text-red-200" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 1C5.92 1 1 5.92 1 12s4.92 11 11 11 11-4.92 11-11S18.08 1 12 1zm1 17.93c-3.95.49-7.43-2.99-6.94-6.94.28-2.28 2.05-4.11 4.33-4.36V7h2v.63c2.28.25 4.05 2.08 4.33 4.36.49 3.95-2.99 7.43-6.72 6.94z"/>
                        </svg>
                    </div>

                    <!-- Optional Background Pattern -->
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-red-300 opacity-20 rounded-full -translate-x-1/3 -translate-y-1/3 pointer-events-none"
                    ></div>
                </div>

            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
