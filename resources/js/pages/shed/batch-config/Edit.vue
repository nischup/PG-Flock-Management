<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

// Props
const props = defineProps<{
    batchConfiguration: any; // can use Array<any> style
    batchAssigns: any[];
}>();

// Make sure batch_assign_id is number
const form = useForm({
    batch_assign_id: Number(props.batchConfiguration.batch_assign_id || ''),
    area_sqft: props.batchConfiguration.area_sqft || '',
    num_workers: props.batchConfiguration.num_workers || 0,
    density_per_sqft: props.batchConfiguration.density_per_sqft || '',
    feeders: props.batchConfiguration.feeders || '',
    drinkers: props.batchConfiguration.drinkers || '',
    temperature_target: props.batchConfiguration.temperature_target || '',
    humidity_target: props.batchConfiguration.humidity_target || '',
    note: props.batchConfiguration.note || '',
    effective_from: props.batchConfiguration.effective_from || '',
    effective_to: props.batchConfiguration.effective_to || '',
});

// Submit update
const submit = () => {
    router.put(route('batch-config.update', props.batchConfiguration.id), form);
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Batch Configuration', href: route('batch-config.index') },
    { title: 'Edit', href: '' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Batch Configuration" />

        <div class="p-6 bg-white rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Edit Batch Configuration</h2>

            <div class="grid grid-cols-1 gap-4">
                <!-- Select BatchAssign -->
                <div>
                    <label for="batch_assign_id" class="block mb-1 font-medium">Select Batch</label>
                    <select v-model="form.batch_assign_id" id="batch_assign_id" class="w-full border rounded p-2">
                        <option value="">-- Select Batch --</option>
                        <option v-for="b in batchAssigns" :key="b.id" :value="Number(b.id)">
                            {{ b.transaction_no }} - {{ b.batch_name }}
                        </option>
                    </select>
                    <span class="text-red-600 text-sm">{{ form.errors.batch_assign_id }}</span>
                </div>

                <!-- Other fields -->
                <div>
                    <label for="area_sqft">Area (sqft)</label>
                    <input v-model="form.area_sqft" type="number" id="area_sqft" class="w-full border rounded p-2" />
                    <span class="text-red-600 text-sm">{{ form.errors.area_sqft }}</span>
                </div>

                <div>
                    <label for="num_workers">Number of Workers</label>
                    <input v-model="form.num_workers" type="number" id="num_workers" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="density_per_sqft">Density per sqft</label>
                    <input v-model="form.density_per_sqft" type="number" id="density_per_sqft" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="feeders">Feeders</label>
                    <input v-model="form.feeders" type="number" id="feeders" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="drinkers">Drinkers</label>
                    <input v-model="form.drinkers" type="number" id="drinkers" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="temperature_target">Target Temperature</label>
                    <input v-model="form.temperature_target" type="number" id="temperature_target" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="humidity_target">Target Humidity</label>
                    <input v-model="form.humidity_target" type="number" id="humidity_target" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="note">Note</label>
                    <textarea v-model="form.note" id="note" class="w-full border rounded p-2"></textarea>
                </div>

                <div>
                    <label for="effective_from">Effective From</label>
                    <input v-model="form.effective_from" type="date" id="effective_from" class="w-full border rounded p-2" />
                </div>

                <div>
                    <label for="effective_to">Effective To</label>
                    <input v-model="form.effective_to" type="date" id="effective_to" class="w-full border rounded p-2" />
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <Button class="bg-gray-300 text-black" @click="$router.back()">Cancel</Button>
                <Button class="bg-green-600 text-white" @click="submit">Update</Button>
            </div>
        </div>
    </AppLayout>
</template>
