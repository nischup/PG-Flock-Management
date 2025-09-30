<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ref } from 'vue';
interface BatchConfig {
    id: number;
    batch_assign: { id: number; transaction_no: string; batch: { id: number; name: string } };
    area_sqft: number;
    num_workers: number;
    density_per_sqft: number;
    feeders: number;
    drinkers: number;
    temperature_target: number;
    humidity_target: number;
    effective_from: string;
    effective_to?: string | null;
    note?: string | null;
}
const { confirmDelete, confirmUpdate } = useNotifier();
const props = defineProps<{ batchConfigs: BatchConfig[] }>();
const batchConfigs = ref([...props.batchConfigs]);
const breadcrumbs = [{ title: 'Batch Configuration', href: '/batch-config' }];
const goToCreate = () => router.get(route('batch-config.create'));
const goToEdit = (id: number) => router.get(route('batch-config.edit', id));

const { can } = usePermissions();
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Batch Configuration" />
        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Batch Configurations</h2>
                <Button v-if="can('batch-config.create')" class="bg-chicken text-white" @click="goToCreate">+ Add New</Button>
            </div>
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">#</th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">Company</th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">Project</th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">Shed</th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">Transaction No</th>
                            <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">Batch Name</th>
                            <th class="border-b px-4 py-2 bg-red-500 text-white font-semibold text-sm whitespace-nowrap">Area (sqft)</th>
                            <th class="border-b px-4 py-2 bg-teal-500 text-white font-semibold text-sm whitespace-nowrap">Workers</th>
                            <th class="border-b px-4 py-2 bg-yellow-500 text-black font-semibold text-sm whitespace-nowrap">Effective From</th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in batchConfigs" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border-b px-4 py-2">{{ index + 1 }}</td>
                            <td class="border-b px-4 py-2">{{ item.batch_assign.company.name }}</td>
                            <td class="border-b px-4 py-2">{{ item.batch_assign.project.name }}</td>
                            <td class="border-b px-4 py-2">{{ item.batch_assign.shed.name }}</td>
                            <td class="border-b px-4 py-2">{{ item.batch_assign.transaction_no }}</td>
                            <td class="border-b px-4 py-2">{{ item.batch_assign.batch.name }}</td>
                            <td class="border-b px-4 py-2">{{ item.area_sqft }}</td>
                            <td class="border-b px-4 py-2">{{ item.num_workers }}</td>
                            <td class="border-b px-4 py-2">
                                {{ dayjs(item.effective_from).format('YYYY-MM-DD') }}
                            </td>
                            <td class="border-b px-4 py-2">
                                <Button size="sm" class="bg-gray-500 text-white" @click="goToEdit(item.id)">Edit</Button>
                            </td>
                        </tr>
                        <tr v-if="batchConfigs.length === 0">
                            <td colspan="10" class="border-b px-4 py-6 text-center text-gray-500">No batch configurations found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
