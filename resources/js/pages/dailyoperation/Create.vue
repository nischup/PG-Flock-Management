<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDropdownOptions } from '@/composables/dropdownOptions';
import { useNotifier } from '@/composables/useNotifier';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { AlertCircle, AlertTriangle, Calendar, CheckCircle2, ChevronDown, ChevronLeft, ChevronRight, Save, Search, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Flock Management', href: '/flocks' },
    { title: 'Daily Operation', href: '' },
];

// Props
const props = defineProps<{
    flocks: Array<any>;
    feeds?: Array<any>;
    vaccines?: Array<any>;
    medicines?: Array<any>;
    waters?: Array<any>;
    units?: Array<any>;
    stage?: string;
    todayVaccineSchedules?: Array<any>;
}>();

const { showInfo, showError } = useNotifier(); // auto-shows flash messages

// Tabs (keys must match below validations) - filter out egg collection for brooding
const tabs = computed(() => {
    const allTabs = [
        { key: 'daily_mortality', label: 'Mortality' },
        { key: 'destroy', label: 'Destroy' },
        { key: 'sexing_error', label: 'Sexing Error' },
        { key: 'cull', label: 'Cull' },
        { key: 'feed_consumption', label: 'Feed' },
        { key: 'water_consumption', label: 'Water' },
        { key: 'light_hour', label: 'Light' },
        { key: 'medicine', label: 'Medicine' }, // optional in this form model
        { key: 'vaccine', label: 'Vaccine' }, // uses `cull` (number)
        // uses `sexing_error` (number)
        { key: 'weight', label: 'Weight' }, // uses `weight` (number)
        { key: 'temperature', label: 'Temperature' }, // uses `temperature` (number)
        { key: 'feedingprogram', label: 'Feeding Program' }, // uses `temperature` (number)
        { key: 'feedFinishingtime', label: 'Feed Finishing Time' }, // uses `temperature` (number)
        { key: 'humidity', label: 'Humidity' }, // uses `humidity` (number)
        { key: 'egg_collection', label: 'Egg collection' }, // optional in this form model
    ];

    if (props.stage === 'brooding') {
        return allTabs.filter((tab) => tab.key !== 'egg_collection');
    }
    return allTabs;
});

const { batchOptions } = useDropdownOptions();

const batchWithLabel = computed(
    () =>
        props.flocks?.map((flock) => {
            return {
                ...flock,
                display_label: flock.label, // Use the label directly from controller
            };
        }) || [],
);

// Active Tab + progress
const activeTabIndex = ref(0);
const totalTabs = computed(() => tabs.value.length);
const currentStep = computed(() => activeTabIndex.value + 1);
const progress = computed(() => (currentStep.value / totalTabs.value) * 100);
// Compute gradient for full progress bar

const progressBarBackground = computed(() => {
    const segmentPercent = 100 / tabs.value.length;
    const segments: string[] = [];

    tabs.value.forEach((tab, index) => {
        const key = tab.key;
        const mainFields = mainFieldsByTab[key] || [];
        const noteField = noteFieldByTab[key];

        const allMainEmpty = mainFields.every((f) => {
            const val = form[f];
            if (typeof val === 'number') return val === 0;
            if (typeof val === 'string') return val.trim() === '';
            return !val;
        });

        const noteHasValue = noteField && (form[noteField] || '').toString().trim() !== '';

        // Determine segment color
        let color = '#facc15'; // default yellow

        if (activeTabIndex.value === index) {
            // Active tab - use glossy green
            color = '#22c55e'; // green-500
        } else if (allMainEmpty && noteHasValue) {
            // Has note but no main fields - use red
            color = '#ef4444'; // red-500
        }

        const start = index * segmentPercent;
        const end = (index + 1) * segmentPercent;

        // Each segment as "color start% end%"
        segments.push(`${color} ${start}% ${end}%`);
    });

    // Join segments and return a proper linear-gradient
    return `linear-gradient(to right, ${segments.join(', ')})`;
});

const activeTab = computed(() => tabs.value[activeTabIndex.value].key);

// Form (exactly as you stated)
const form = useForm({
    batchassign_id: '',
    operation_date: new Date().toISOString().substr(0, 10),
    female_mortality: 0,
    male_mortality: 0,
    water_type: 2,
    water_quantity: 0,
    water_unit: 0,
    female_reason: '',
    male_reason: '',
    mortalitynote: '',
    feed_type_id: '',
    feed_quantity: 0,
    feed_unit: '',
    feed_note: '',
    light_hour: 0,
    light_minute: 0,
    destroy_male: 0,
    destroy_female: 0,
    destroy_male_reason: '',
    destroy_female_reason: '',
    cull_male_qty: 0,
    cull_female_qty: 0,
    cull_male_reason: '',
    cull_female_reason: '',
    sexing_error_male: 0,
    sexing_error_female: 0,
    egg_collection: 0,
    weight_male: 0,
    weight_female: 0,
    temp_inside: 0,
    temp_inside_std: 0,
    temp_outside: 0,
    temp_outside_std: 0,
    humidity_today: 0,
    humidity_std: 0,
    water_note: '',
    light_note: '',
    destroy_note: '',
    culling_note: '',
    serror_note: '',
    weight_note: '',
    temperature_note: '',
    humidity_note: '',
    medicine_id: 0,
    medicine_qty: 0,
    medicine_unit: 0,
    medicine_dose: 0,
    medicine_note: '',
    vaccine_schedule_detail_id: '',
    vaccine_id: '',
    vaccine_dose: '',
    vaccine_unit: '',
    vaccine_note: '',
    male_program: 0,
    female_program: 0,
    feeding_program_note: '',
    finishtime_male: 0,
    finishtime_female: 0,
    finishtime_note: 0,
    eggcollection_note: '',
    vaccine_file: null as File | null,
});

// Auto-set egg collection note for brooding stage
watch(
    () => props.stage,
    (newStage) => {
        if (newStage === 'brooding') {
            form.eggcollection_note = 'No egg collection - Brooding stage';
        }
    },
    { immediate: true },
);

// Errors
const errors = ref<Record<string, string>>({});

// Shed & flock info (real data)
const shedQty = ref({ opening: 0, current: 0, mortality: 0 });
const flockInfo = ref<{ age: string }>({ age: '0 weeks 0 days' });

// Modern dropdown states
const showFlockDropdown = ref(false);
const flockSearchQuery = ref('');

// Date picker overlay states
const showDateOverlay = ref(false);

// Feed dropdown states
const showFeedTypeDropdown = ref(false);
const feedTypeSearchQuery = ref('');
const showUnitDropdown = ref(false);
const showWaterUnitDropdown = ref(false);

const unitSearchQuery = ref('');
const waterUnitSearchQuery = ref('');
// Selected vaccine schedule
const selectedVaccineSchedule = computed(() => {
    if (!form.vaccine_schedule_detail_id) return null;
    return props.todayVaccineSchedules?.find((schedule) => schedule.id == form.vaccine_schedule_detail_id);
});

// Filtered flock options
const filteredFlocks = computed(() => {
    if (!flockSearchQuery.value) return batchWithLabel.value;
    return batchWithLabel.value.filter(
        (flock) =>
            flock.label?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
            flock.company?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
            flock.project?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
            flock.flock?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
            flock.shed?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
            flock.batch?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()),
    );
});

// Selected flock display
const selectedFlock = computed(() => {
    return batchWithLabel.value.find((flock) => flock.id === Number(form.batchassign_id));
});

// Filtered feed types
const filteredFeedTypes = computed(() => {
    if (!feedTypeSearchQuery.value) return props.feeds || [];
    return (props.feeds || []).filter((feed) => feed.feed_name.toLowerCase().includes(feedTypeSearchQuery.value.toLowerCase()));
});

// Filtered units
const filteredUnits = computed(() => {
    if (!unitSearchQuery.value) return props.units || [];
    return (props.units || []).filter((unit) => unit.name.toLowerCase().includes(unitSearchQuery.value.toLowerCase()));
});
const filteredWaterUnits = computed(() => {
    if (!waterUnitSearchQuery.value) return props.units || [];
    return (props.units || []).filter((u) => u.name.toLowerCase().includes(waterUnitSearchQuery.value.toLowerCase()));
});

// Selected feed type display
const selectedFeedType = computed(() => {
    return (props.feeds || []).find((feed) => feed.id === form.feed_type_id) || null;
});

// Selected unit display
const selectedUnit = computed(() => {
    return (props.units || []).find((unit) => unit.id === form.feed_unit) || null;
});
const selectedWaterUnit = computed(() => {
    return (props.units || []).find((u) => u.id === form.water_unit) || null;
});
// Close dropdown on outside click
const handleClickOutside = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.flock-dropdown')) {
        showFlockDropdown.value = false;
    }
    if (!(e.target as HTMLElement).closest('.date-overlay')) {
        showDateOverlay.value = false;
    }
    if (!(e.target as HTMLElement).closest('.feed-type-dropdown')) {
        showFeedTypeDropdown.value = false;
    }
    if (!(e.target as HTMLElement).closest('.unit-dropdown')) {
        showUnitDropdown.value = false;
    }
};

// Keyboard event handler
const handleKeydown = (event: KeyboardEvent) => {
    // Only handle arrow keys when not in input fields or textareas
    const target = event.target as HTMLElement;
    const isInputField = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT';

    if (isInputField) return;

    if (event.key === 'ArrowRight') {
        event.preventDefault();
        nextTab();
    } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        prevTab();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});

const counts = ref<Record<string, number | string>>({});

// Watch flock change (real data)
watch(
    () => form.batchassign_id,
    async (id) => {
        if (!id) {
            shedQty.value = { opening: 0, current: 0, mortality: 0 };
            flockInfo.value.age = '0 weeks 0 days';
            counts.value = {};
            return;
        }

        // Find the selected flock from the props
        const selectedFlock = batchWithLabel.value.find((flock) => flock.id === Number(id));
        if (selectedFlock) {
            shedQty.value = {
                opening: selectedFlock.total_birds || 0,
                current: selectedFlock.current_birds || 0,
                mortality: selectedFlock.batch_mortality || 0,
            };
            flockInfo.value.age = selectedFlock.age || '0 weeks 0 days';

            // Fetch real tab data from the API
            try {
                const response = await fetch(`/daily-operation/batch/${id}/data`);
                const data = await response.json();

                if (data.tabData) {
                    counts.value = {
                        daily_mortality: data.tabData.daily_mortality || 0,
                        feed_consumption: data.tabData.feed_consumption || '0 Kg',
                        water_consumption: data.tabData.water_consumption || '0 L',
                        light_hour: data.tabData.light_hour || '0 H',
                        destroy: data.tabData.destroy || 0,
                        cull: data.tabData.cull || 0,
                        sexing_error: data.tabData.sexing_error || 0,
                        weight: data.tabData.weight || '0 gm',
                        temperature: data.tabData.temperature || 0,
                        humidity: data.tabData.humidity || 0,
                        egg_collection: data.tabData.egg_collection || 0,
                        medicine: data.tabData.medicine || 0,
                        vaccine: data.tabData.vaccine || 0,
                    };

                    // 🐔 Now update shedQty.current
                    const totalBirds = selectedFlock.total_birds || 0;
                    const mortality = Number(data.tabData.daily_mortality) + Number(selectedFlock.batch_mortality) || 0;
                    const cull = Number(data.tabData.cull) || 0;
                    const destroy = Number(data.tabData.destroy) || 0;
                    const sexingError = Number(data.tabData.sexing_error) || 0;

                    shedQty.value.current = totalBirds - (mortality + cull + destroy + sexingError);
                    shedQty.value.mortality = Number(selectedFlock.batch_mortality) || 0;
                } else {
                    // Fallback to basic counts if no data
                    counts.value = {
                        daily_mortality: selectedFlock.batch_female_mortality + selectedFlock.batch_male_mortality,
                        feed_consumption: '0 Kg',
                        water_consumption: '0 L',
                        light_hour: '0 H',
                        destroy: 0,
                        cull: 0,
                        sexing_error: 0,
                        weight: '0 gm',
                        temperature: 0,
                        humidity: 0,
                        egg_collection: 0,
                        medicine: 0,
                        vaccine: 0,
                    };
                }
            } catch (error) {
                console.error('Error fetching batch data:', error);
                // Fallback to basic counts on error
                counts.value = {
                    daily_mortality: selectedFlock.batch_female_mortality + selectedFlock.batch_male_mortality,
                    feed_consumption: '0 Kg',
                    water_consumption: '0 L',
                    light_hour: '0 H',
                    destroy: 0,
                    cull: 0,
                    sexing_error: 0,
                    weight: '0 gm',
                    temperature: 0,
                    humidity: 0,
                    egg_collection: 0,
                    medicine: 0,
                    vaccine: 0,
                };
            }
        }
    },
);

// ---------- Validation Setup ----------
type Rule = { field: keyof typeof form; label: string; kind: 'string' | 'number'; min?: number };

// Basic note field rules for each tab
const rulesByTab: Record<string, Rule[]> = {
    daily_mortality: [{ field: 'mortalitynote', label: 'Mortality note', kind: 'string' }],
    feed_consumption: [{ field: 'feed_note', label: 'Feed note', kind: 'string' }],
    water_consumption: [{ field: 'water_note', label: 'Water note', kind: 'string' }],
    light_hour: [{ field: 'light_note', label: 'Light note', kind: 'string' }],
    destroy: [{ field: 'destroy_note', label: 'Destroy note', kind: 'string' }],
    cull: [{ field: 'culling_note', label: 'Culling note', kind: 'string' }],
    sexing_error: [{ field: 'serror_note', label: 'Sexing note', kind: 'string' }],
    weight: [{ field: 'weight_note', label: 'Weight note', kind: 'string' }],
    temperature: [{ field: 'temperature_note', label: 'Temperature note', kind: 'string' }],
    humidity: [{ field: 'humidity_note', label: 'Humidity note', kind: 'string' }],
    medicine: [{ field: 'medicine_note', label: 'Medicine note', kind: 'string' }],
    vaccine: [{ field: 'vaccine_note', label: 'Vaccine note', kind: 'string' }],
    feedingprogram: [{ field: 'feeding_program_note', label: 'Feeding program note', kind: 'string' }],
};

// ---------- Main Fields Mapping ----------
const mainFieldsByTab: Record<string, (keyof typeof form)[]> = {
    daily_mortality: ['female_mortality', 'male_mortality', 'female_reason', 'male_reason'],
    feed_consumption: ['feed_type_id', 'feed_quantity', 'feed_unit'],
    water_consumption: ['water_quantity'],
    light_hour: ['light_hour'],
    destroy: ['destroy_male'],
    cull: ['cull_male_qty'],
    sexing_error: ['sexing_error_male'],
    weight: ['weight_male'],
    temperature: ['temp_inside'],
    humidity: ['humidity_today'],
    medicine: ['medicine_id', 'medicine_qty', 'medicine_unit'],
    vaccine: ['vaccine_schedule_detail_id', 'vaccine_dose', 'vaccine_unit'],
    feedingprogram: ['male_program', 'female_program'],
};

// ---------- Note Field Mapping ----------
const noteFieldByTab: Record<string, keyof typeof form> = {
    daily_mortality: 'mortalitynote',
    feed_consumption: 'feed_note',
    water_consumption: 'water_note',
    light_hour: 'light_note',
    destroy: 'destroy_note',
    cull: 'culling_note',
    sexing_error: 'serror_note',
    weight: 'weight_note',
    temperature: 'temperature_note',
    humidity: 'humidity_note',
    medicine: 'medicine_note',
    vaccine: 'vaccine_note',
    feedingprogram: 'feeding_program_note',
};

// ---------- Clear Errors ----------
function clearErrorsForTab(tabKey: string) {
    const rules = rulesByTab[tabKey] || [];
    for (const r of rules) {
        delete errors.value[r.field as string];
    }
}

// ---------- Dynamic Validation ----------
function validateTab(tabKey: string): boolean {
    clearErrorsForTab(tabKey);
    const rules = rulesByTab[tabKey] || [];
    let ok = true;

    for (const r of rules) {
        const v = (form as any)[r.field];

        // Check dynamic note requirement
        if (noteFieldByTab[tabKey] && r.field === noteFieldByTab[tabKey]) {
            const mainFields = mainFieldsByTab[tabKey] || [];

            const allEmpty = mainFields.every((f) => {
                const val = form[f];
                if (typeof val === 'number') return val === 0;
                if (typeof val === 'string') return val.trim() === '';
                return !val;
            });

            if (allEmpty && (!v || v.trim() === '')) {
                errors.value[r.field as string] = `${r.label} is required`;
                showInfo('Please provide a brief explanation for any fields that are intentionally left blank or not applicable.');
                ok = false;
            }

            continue;
        }

        // Normal validation
        if (r.kind === 'string') {
            if (!v || (typeof v === 'string' && v.trim() === '')) {
                errors.value[r.field as string] = `${r.label} is required`;
                ok = false;
            }
        } else if (r.kind === 'number') {
            const n = Number(v);
            const needsMin = typeof r.min === 'number';
            if (Number.isNaN(n) || (needsMin ? n < r.min! : v === null || v === undefined)) {
                errors.value[r.field as string] = `${r.label} must be greater than ${needsMin ? r.min : 'or equal to 0'}`;
                ok = false;
            }
        }
    }

    return ok;
}

const completedTabs = ref<number[]>([]);

// Navigation (guarded)
function nextTab() {
    const key = activeTab.value;
    if (!validateTab(key)) return;

    // mark current tab as completed
    if (!completedTabs.value.includes(activeTabIndex.value)) {
        completedTabs.value.push(activeTabIndex.value);
    }

    if (activeTabIndex.value < tabs.value.length - 1) activeTabIndex.value++;
}

function prevTab() {
    if (activeTabIndex.value > 0) activeTabIndex.value--;
}

function goToTab(index: number) {
    if (index <= activeTabIndex.value) {
        activeTabIndex.value = index;
        return;
    }
    const key = activeTab.value;
    if (!validateTab(key)) return;
    activeTabIndex.value = index;
}

// Handle vaccine schedule selection
function onVaccineScheduleChange() {
    const selectedScheduleId = form.vaccine_schedule_detail_id;
    if (!selectedScheduleId) {
        // Clear vaccine fields if no schedule selected
        form.vaccine_id = '';
        form.vaccine_dose = '';
        form.vaccine_unit = '';
        return;
    }

    // Find the selected schedule
    const selectedSchedule = props.todayVaccineSchedules?.find((schedule) => schedule.id == selectedScheduleId);
    if (selectedSchedule) {
        // Auto-populate vaccine fields
        form.vaccine_id = selectedSchedule.vaccine_id;
        form.vaccine_dose = ''; // Let user enter dose
        form.vaccine_unit = ''; // Let user select unit
        form.vaccine_note = selectedSchedule.notes || '';
    }
}
// Submit
function submit() {
    // Validate last active tab before submit (optional)
    if (!validateTab(activeTab.value)) return;

    // Optionally, validate all tabs before post:
    // for (const t of tabs.value) { if (!validateTab(t.key)) { activeTabIndex.value = tabs.value.findIndex(x => x.key === t.key); return } }

    form.post(route('daily-operation.store'), {
        onSuccess: () => form.reset(),
        onError: (errors) => {
            // Handle duplicate entry error
            if (errors.duplicate_entry) {
                showError(errors.duplicate_entry, 'Duplicate Entry');
            }
        },
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Daily Operation" />

        <!-- Back to List Button -->
        <div class="mt-2 flex justify-end px-6">
            <a
                :href="props.stage ? `/daily-operation/stage/${props.stage}` : '/overview'"
                class="inline-flex items-center space-x-2 rounded-lg bg-gradient-to-r from-gray-900 to-black px-4 py-2 text-sm font-medium text-white shadow-lg transition-all duration-200 hover:from-gray-800 hover:to-gray-900 hover:shadow-xl"
                @click="console.log('Stage:', props.stage, 'Navigating to:', props.stage ? `/daily-operation/stage/${props.stage}` : '/overview')"
            >
                <ChevronLeft class="h-4 w-4" />
                <span>Back to List</span>
            </a>
        </div>

        <form @submit.prevent="submit" class="space-y-4 p-6">
            <!-- Flock Info -->
            <div class="overflow-hidden rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 shadow-sm">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-black px-3 py-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded bg-white/20">
                                <span class="text-xs font-bold text-white">🐔</span>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-white">Flock Information</h2>
                                <p class="text-xs text-blue-100">Select flock and record daily operations</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-white/80">Progress</div>
                            <div class="text-xs font-bold text-white">{{ currentStep }} / {{ totalTabs }}</div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="space-y-4 p-4">
                    <!-- Progress Bar -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Overall Progress</span>
                            <span>{{ Math.round(progress) }}% Complete</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                            <div
                                class="flex h-full items-center justify-center rounded-full text-xs font-bold text-white transition-all duration-500"
                                :style="{ width: progress + '%', background: progressBarBackground }"
                            ></div>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <!-- Flock Select -->
                        <div class="space-y-1">
                            <Label class="flex items-center text-xs font-semibold text-gray-700">
                                <div class="mr-1.5 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                Select Batch
                            </Label>
                            <div class="flock-dropdown relative">
                                <button
                                    type="button"
                                    @click.stop="showFlockDropdown = !showFlockDropdown"
                                    class="flex h-10 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                >
                                    <span class="flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                        {{ selectedFlock ? selectedFlock.display_label : 'Select Batch' }}
                                    </span>
                                    <ChevronDown
                                        class="h-3 w-3 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': showFlockDropdown }"
                                    />
                                </button>

                                <!-- Flock Dropdown -->
                                <div
                                    v-if="showFlockDropdown"
                                    class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                                    @click="showFlockDropdown = false"
                                >
                                    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl" @click.stop>
                                        <!-- Header -->
                                        <div class="border-b border-gray-200 p-3">
                                            <h3 class="text-sm font-semibold text-gray-900">Select Batch</h3>
                                            <div class="relative mt-2">
                                                <Search class="absolute top-1/2 left-2 h-3 w-3 -translate-y-1/2 text-gray-400" />
                                                <input
                                                    v-model="flockSearchQuery"
                                                    type="text"
                                                    placeholder="Search batches..."
                                                    class="w-full rounded border border-gray-300 bg-gray-50 py-1.5 pr-3 pl-7 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                                    @click.stop
                                                />
                                            </div>
                                        </div>

                                        <!-- Flock List -->
                                        <div class="max-h-80 overflow-y-auto">
                                            <div v-if="(batchWithLabel?.length || 0) === 0" class="px-4 py-6 text-center">
                                                <AlertCircle class="mx-auto h-6 w-6 text-red-500" />
                                                <div class="mt-2 text-sm font-medium text-red-600">No Batches Available</div>
                                                <div class="text-xs text-gray-500">Please create batches first</div>
                                            </div>
                                            <button
                                                v-for="flock in filteredFlocks"
                                                :key="flock.id"
                                                type="button"
                                                @click.stop="
                                                    form.batchassign_id = flock.id;
                                                    showFlockDropdown = false;
                                                "
                                                class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-3 text-left transition-colors duration-200 last:border-b-0 hover:bg-blue-50"
                                                :class="{ 'bg-blue-100': form.batchassign_id == flock.id }"
                                            >
                                                <div class="h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900">{{ flock.label }}</div>
                                                    <div class="text-xs text-gray-500">{{ flock.company }} • {{ flock.project }}</div>
                                                </div>
                                                <CheckCircle2 v-if="form.batchassign_id == flock.id" class="h-3 w-3 flex-shrink-0 text-blue-500" />
                                            </button>
                                            <div
                                                v-if="filteredFlocks.length === 0 && (batchWithLabel?.length || 0) > 0"
                                                class="px-4 py-6 text-center text-gray-500"
                                            >
                                                <Search class="mx-auto h-5 w-5 text-gray-400" />
                                                <div class="mt-2 text-xs">No results found for "{{ flockSearchQuery }}"</div>
                                            </div>
                                        </div>

                                        <!-- Close Button -->
                                        <div class="border-t border-gray-200 p-3">
                                            <Button
                                                type="button"
                                                @click="showFlockDropdown = false"
                                                class="w-full rounded bg-gray-100 py-2 text-xs text-gray-700 hover:bg-gray-200"
                                            >
                                                Close
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Picker -->
                        <div class="space-y-1">
                            <Label class="flex items-center text-xs font-semibold text-gray-700">
                                <div class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                Operation Date
                            </Label>
                            <div class="date-overlay relative">
                                <button
                                    type="button"
                                    @click.stop="showDateOverlay = !showDateOverlay"
                                    class="flex h-10 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition-all duration-200 hover:border-green-500 hover:shadow-md focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none"
                                >
                                    <span class="flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                        {{ form.operation_date ? new Date(form.operation_date).toLocaleDateString() : 'Select Date' }}
                                    </span>
                                    <Calendar
                                        class="h-3 w-3 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': showDateOverlay }"
                                    />
                                </button>

                                <!-- Date Overlay -->
                                <div
                                    v-if="showDateOverlay"
                                    class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                                    @click="showDateOverlay = false"
                                >
                                    <div class="w-full max-w-sm rounded-lg border border-gray-200 bg-white shadow-2xl" @click.stop>
                                        <!-- Header -->
                                        <div class="border-b border-gray-200 p-3">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-semibold text-gray-900">Select Operation Date</h3>
                                                <button
                                                    type="button"
                                                    @click="showDateOverlay = false"
                                                    class="rounded p-1 transition-colors duration-200 hover:bg-gray-100"
                                                >
                                                    <X class="h-4 w-4 text-gray-400" />
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Date Picker -->
                                        <div class="p-4">
                                            <Datepicker
                                                v-model="form.operation_date"
                                                format="yyyy-MM-dd"
                                                :input-class="'hidden'"
                                                placeholder="Select Date"
                                                :auto-apply="true"
                                                @update:model-value="showDateOverlay = false"
                                                inline
                                            />
                                        </div>

                                        <!-- Close Button -->
                                        <div class="border-t border-gray-200 p-3">
                                            <Button
                                                type="button"
                                                @click="showDateOverlay = false"
                                                class="w-full rounded bg-gray-100 py-2 text-xs text-gray-700 hover:bg-gray-200"
                                            >
                                                Close
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shed Info Cards -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50 px-2 py-1.5">
                            <h3 class="flex items-center text-xs font-semibold text-gray-700">
                                <div class="mr-1.5 h-1 w-1 rounded-full bg-purple-500"></div>
                                Flock Statistics
                            </h3>
                        </div>
                        <div class="grid grid-cols-4 gap-0">
                            <!-- Total -->
                            <div class="border-r border-gray-200 p-2 text-center last:border-r-0">
                                <div
                                    class="mx-auto mb-1 flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-yellow-400 to-orange-500"
                                >
                                    <span class="text-xs text-white">📊</span>
                                </div>
                                <p class="mb-0.5 text-xs font-medium text-gray-600">Total</p>
                                <p class="text-xs font-bold text-gray-900">{{ shedQty.opening.toLocaleString() }}</p>
                            </div>
                            <!-- Batch Mortality -->
                            <div class="border-r border-gray-200 p-2 text-center last:border-r-0">
                                <div
                                    class="mx-auto mb-1 flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-red-400 to-pink-500"
                                >
                                    <span class="text-xs text-white">☠️</span>
                                </div>
                                <p class="mb-0.5 text-xs font-medium text-gray-600">Assign Mortality</p>
                                <p class="text-xs font-bold text-gray-900">{{ shedQty.mortality?.toLocaleString() || 0 }}</p>
                            </div>

                            <!-- Current -->
                            <div class="border-r border-gray-200 p-2 text-center last:border-r-0">
                                <div
                                    class="mx-auto mb-1 flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-emerald-500"
                                >
                                    <span class="text-xs text-white">🐣</span>
                                </div>
                                <p class="mb-0.5 text-xs font-medium text-gray-600">Current</p>
                                <p class="text-xs font-bold text-gray-900">{{ shedQty.current.toLocaleString() }}</p>
                            </div>

                            <!-- Age -->
                            <div class="p-2 text-center">
                                <div
                                    class="mx-auto mb-1 flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-indigo-500"
                                >
                                    <span class="text-xs text-white">⏰</span>
                                </div>
                                <p class="mb-0.5 text-xs font-medium text-gray-600">Age</p>
                                <p class="text-xs font-bold text-gray-900">{{ flockInfo.age }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Left Column - Tab Cards -->
                <div class="space-y-4">
                    <div class="rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-4">
                        <h3 class="mb-1 flex items-center text-lg font-bold text-gray-800">
                            <div class="mr-2 flex h-6 w-6 items-center justify-center rounded bg-blue-600">
                                <span class="text-xs font-bold text-white">📊</span>
                            </div>
                            Operation Categories
                        </h3>
                        <p class="text-xs text-gray-600">Select a category to record daily operations</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 lg:grid-cols-5">
                        <div
                            v-for="(tab, index) in tabs"
                            :key="tab.key"
                            @click="goToTab(index)"
                            class="group cursor-pointer rounded-lg border-2 p-2 text-center transition-all duration-300 hover:scale-[1.01] hover:shadow-md"
                            :class="[
                                activeTabIndex === index
                                    ? 'scale-[1.01] border-orange-500 bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-md'
                                    : completedTabs.includes(index)
                                      ? 'border-green-500 bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-sm'
                                      : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300',
                            ]"
                        >
                            <!-- Number badge -->
                            <div
                                class="mx-auto mb-1.5 flex h-6 w-6 items-center justify-center rounded text-xs font-bold"
                                :class="[
                                    activeTabIndex === index
                                        ? 'bg-white/20 text-white'
                                        : completedTabs.includes(index)
                                          ? 'bg-white/20 text-white'
                                          : 'bg-gray-100 text-gray-600',
                                ]"
                            >
                                {{ index + 1 }}
                            </div>

                            <!-- Tab label -->
                            <div class="mb-1 text-xs leading-tight font-semibold">{{ tab.label }}</div>

                            <!-- Status -->
                            <div class="mb-1 text-xs opacity-75">
                                {{ completedTabs.includes(index) ? '✓' : activeTabIndex === index ? '●' : '○' }}
                            </div>

                            <!-- Count value -->
                            <div v-if="counts[tab.key] !== undefined" class="text-xs font-bold">
                                {{ counts[tab.key] }}
                            </div>
                            <div v-else class="text-xs opacity-75">---</div>

                            <!-- Progress indicator -->
                            <div class="mt-1.5 h-0.5 w-full rounded-full bg-black/10">
                                <div
                                    class="h-0.5 rounded-full transition-all duration-300"
                                    :class="[
                                        activeTabIndex === index
                                            ? 'w-full bg-white'
                                            : completedTabs.includes(index)
                                              ? 'w-full bg-white'
                                              : 'w-0 bg-gray-300',
                                    ]"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Input Forms -->
                <div class="space-y-4">
                    <!-- Tab Content Header -->
                    <div class="rounded-lg border border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="flex h-6 w-6 items-center justify-center rounded bg-blue-600">
                                    <span class="text-xs font-bold text-white">{{ activeTabIndex + 1 }}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ tabs[activeTabIndex]?.label }}</h3>
                                    <p class="text-xs text-gray-600">Fill in the details below</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Step {{ currentStep }} of {{ totalTabs }}</div>
                                <div class="mt-1 h-1.5 w-20 rounded-full bg-gray-200">
                                    <div
                                        class="h-1.5 rounded-full bg-blue-500 transition-all duration-300"
                                        :style="{ width: (currentStep / totalTabs) * 100 + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <!-- Mortality -->
                        <div v-if="activeTab === 'daily_mortality'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Female Chick Mortality
                                        </Label>
                                        <Input
                                            v-model.number="form.female_mortality"
                                            type="number"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Male Chick Mortality
                                        </Label>
                                        <Input
                                            v-model.number="form.male_mortality"
                                            type="number"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="text-xs font-semibold text-gray-700">Female Reason</Label>
                                        <Input
                                            v-model="form.female_reason"
                                            type="text"
                                            placeholder="Enter reason..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="text-xs font-semibold text-gray-700">Male Reason</Label>
                                        <Input
                                            v-model="form.male_reason"
                                            type="text"
                                            placeholder="Enter reason..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.mortalitynote"
                                        placeholder="Add any additional notes about mortality..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-red-500"
                                        :class="errors.mortalitynote ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.mortalitynote" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.mortalitynote }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feed Consumption -->
                        <div v-if="activeTab === 'feed_consumption'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                            Feed Type
                                        </Label>
                                        <div class="feed-type-dropdown relative">
                                            <button
                                                type="button"
                                                @click.stop="showFeedTypeDropdown = !showFeedTypeDropdown"
                                                class="flex h-10 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition-all duration-200 hover:border-green-500 hover:shadow-md focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none"
                                                :class="errors.feed_type_id ? 'border-red-500 ring-2 ring-red-200' : ''"
                                            >
                                                <span class="flex items-center gap-2">
                                                    <div class="h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                                    {{ selectedFeedType ? selectedFeedType.feed_name : 'Select Feed Type' }}
                                                </span>
                                                <ChevronDown
                                                    class="h-3 w-3 text-gray-400 transition-transform duration-200"
                                                    :class="{ 'rotate-180': showFeedTypeDropdown }"
                                                />
                                            </button>

                                            <!-- Feed Type Dropdown -->
                                            <div
                                                v-if="showFeedTypeDropdown"
                                                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                                                @click="showFeedTypeDropdown = false"
                                            >
                                                <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl" @click.stop>
                                                    <!-- Header -->
                                                    <div class="flex items-center justify-between border-b border-gray-200 p-4">
                                                        <h3 class="text-sm font-semibold text-gray-900">Select Feed Type</h3>
                                                        <button
                                                            type="button"
                                                            @click="showFeedTypeDropdown = false"
                                                            class="rounded p-1 transition-colors duration-200 hover:bg-gray-100"
                                                        >
                                                            <X class="h-4 w-4 text-gray-400" />
                                                        </button>
                                                    </div>

                                                    <!-- Search -->
                                                    <div class="border-b border-gray-200 p-4">
                                                        <div class="relative">
                                                            <Search
                                                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400"
                                                            />
                                                            <input
                                                                v-model="feedTypeSearchQuery"
                                                                type="text"
                                                                placeholder="Search feed types..."
                                                                class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-transparent focus:ring-2 focus:ring-green-500"
                                                            />
                                                        </div>
                                                    </div>

                                                    <!-- Options -->
                                                    <div class="max-h-60 overflow-y-auto">
                                                        <button
                                                            v-for="feed in filteredFeedTypes"
                                                            :key="feed.id"
                                                            type="button"
                                                            @click.stop="
                                                                form.feed_type_id = feed.id;
                                                                showFeedTypeDropdown = false;
                                                            "
                                                            class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-3 text-left transition-colors duration-200 last:border-b-0 hover:bg-green-50"
                                                            :class="{ 'bg-green-100': form.feed_type_id == feed.id }"
                                                        >
                                                            <div class="flex-shrink-0">
                                                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="text-sm font-medium text-gray-900">{{ feed.feed_name }}</div>
                                                            </div>
                                                            <div v-if="form.feed_type_id == feed.id" class="flex-shrink-0">
                                                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                                                            </div>
                                                        </button>
                                                    </div>

                                                    <!-- Footer -->
                                                    <div class="border-t border-gray-200 p-3">
                                                        <Button
                                                            type="button"
                                                            @click="showFeedTypeDropdown = false"
                                                            class="w-full rounded bg-gray-100 py-2 text-xs text-gray-700 hover:bg-gray-200"
                                                        >
                                                            Close
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="errors.feed_type_id" class="mt-1 flex items-center text-xs text-red-600">
                                            <AlertTriangle class="mr-1 h-4 w-4" />
                                            {{ errors.feed_type_id }}
                                        </p>
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-orange-500"></div>
                                            Quantity
                                        </Label>
                                        <Input
                                            v-model.number="form.feed_quantity"
                                            type="number"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                            :class="errors.feed_quantity ? 'border-red-500 ring-2 ring-red-200' : ''"
                                        />
                                        <p v-if="errors.feed_quantity" class="mt-1 flex items-center text-xs text-red-600">
                                            <AlertTriangle class="mr-1 h-4 w-4" />
                                            {{ errors.feed_quantity }}
                                        </p>
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-purple-500"></div>
                                            Unit
                                        </Label>
                                        <div class="unit-dropdown relative">
                                            <button
                                                type="button"
                                                @click.stop="showUnitDropdown = !showUnitDropdown"
                                                class="flex h-10 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition-all duration-200 hover:border-purple-500 hover:shadow-md focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 focus:outline-none"
                                            >
                                                <span class="flex items-center gap-2">
                                                    <div class="h-1.5 w-1.5 rounded-full bg-purple-500"></div>
                                                    {{ selectedUnit ? selectedUnit.name : 'Select Unit' }}
                                                </span>
                                                <ChevronDown
                                                    class="h-3 w-3 text-gray-400 transition-transform duration-200"
                                                    :class="{ 'rotate-180': showUnitDropdown }"
                                                />
                                            </button>

                                            <!-- Unit Dropdown -->
                                            <div
                                                v-if="showUnitDropdown"
                                                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                                                @click="showUnitDropdown = false"
                                            >
                                                <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl" @click.stop>
                                                    <!-- Header -->
                                                    <div class="flex items-center justify-between border-b border-gray-200 p-4">
                                                        <h3 class="text-sm font-semibold text-gray-900">Select Unit</h3>
                                                        <button
                                                            type="button"
                                                            @click="showUnitDropdown = false"
                                                            class="rounded p-1 transition-colors duration-200 hover:bg-gray-100"
                                                        >
                                                            <X class="h-4 w-4 text-gray-400" />
                                                        </button>
                                                    </div>

                                                    <!-- Search -->
                                                    <div class="border-b border-gray-200 p-4">
                                                        <div class="relative">
                                                            <Search
                                                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400"
                                                            />
                                                            <input
                                                                v-model="unitSearchQuery"
                                                                type="text"
                                                                placeholder="Search units..."
                                                                class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                                            />
                                                        </div>
                                                    </div>

                                                    <!-- Options -->
                                                    <div class="max-h-60 overflow-y-auto">
                                                        <button
                                                            v-for="unit in filteredUnits"
                                                            :key="unit.id"
                                                            type="button"
                                                            @click.stop="
                                                                form.feed_unit = unit.id;
                                                                showUnitDropdown = false;
                                                            "
                                                            class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-3 text-left transition-colors duration-200 last:border-b-0 hover:bg-purple-50"
                                                            :class="{ 'bg-purple-100': form.feed_unit == unit.id }"
                                                        >
                                                            <div class="flex-shrink-0">
                                                                <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="text-sm font-medium text-gray-900">{{ unit.name }}</div>
                                                            </div>
                                                            <div v-if="form.feed_unit == unit.id" class="flex-shrink-0">
                                                                <CheckCircle2 class="h-4 w-4 text-purple-600" />
                                                            </div>
                                                        </button>
                                                    </div>

                                                    <!-- Footer -->
                                                    <div class="border-t border-gray-200 p-3">
                                                        <Button
                                                            type="button"
                                                            @click="showUnitDropdown = false"
                                                            class="w-full rounded bg-gray-100 py-2 text-xs text-gray-700 hover:bg-gray-200"
                                                        >
                                                            Close
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.feed_note"
                                        placeholder="Add any additional notes about feed consumption..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        :class="errors.feed_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.feed_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.feed_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Water Consumption -->
                        <div v-if="activeTab === 'water_consumption'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Water Type
                                        </Label>
                                        <select
                                            v-model="form.water_type"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        >
                                            <!-- <option value="">Select Water Type</option> -->
                                            <option v-for="water in props.waters" :key="water.id" :value="water.id">
                                                {{ water.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-cyan-500"></div>
                                            Quantity
                                        </Label>
                                        <Input
                                            v-model.number="form.water_quantity"
                                            type="number"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-cyan-500"
                                        />
                                    </div>
                                </div>
                                <!-- Water Unit -->
                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                        Water Unit
                                    </Label>

                                    <div class="unit-dropdown relative">
                                        <button
                                            type="button"
                                            @click.stop="showWaterUnitDropdown = !showWaterUnitDropdown"
                                            class="flex h-10 w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                                        >
                                            <span class="flex items-center gap-2">
                                                <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                                {{ selectedWaterUnit ? selectedWaterUnit.name : 'Select Water Unit' }}
                                            </span>
                                            <ChevronDown
                                                class="h-3 w-3 text-gray-400 transition-transform duration-200"
                                                :class="{ 'rotate-180': showWaterUnitDropdown }"
                                            />
                                        </button>

                                        <!-- Water Unit Dropdown -->
                                        <div
                                            v-if="showWaterUnitDropdown"
                                            class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                                            @click="showWaterUnitDropdown = false"
                                        >
                                            <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl" @click.stop>
                                                <!-- Header -->
                                                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                                                    <h3 class="text-sm font-semibold text-gray-900">Select Water Unit</h3>
                                                    <button
                                                        type="button"
                                                        @click="showWaterUnitDropdown = false"
                                                        class="rounded p-1 transition-colors duration-200 hover:bg-gray-100"
                                                    >
                                                        <X class="h-4 w-4 text-gray-400" />
                                                    </button>
                                                </div>

                                                <!-- Search -->
                                                <div class="border-b border-gray-200 p-4">
                                                    <div class="relative">
                                                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                                        <input
                                                            v-model="waterUnitSearchQuery"
                                                            type="text"
                                                            placeholder="Search water units..."
                                                            class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                                        />
                                                    </div>
                                                </div>

                                                <!-- Options -->
                                                <div class="max-h-60 overflow-y-auto">
                                                    <button
                                                        v-for="unit in filteredWaterUnits"
                                                        :key="unit.id"
                                                        type="button"
                                                        @click.stop="
                                                            form.water_unit = unit.id;
                                                            showWaterUnitDropdown = false;
                                                        "
                                                        class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-3 text-left transition-colors duration-200 last:border-b-0 hover:bg-blue-50"
                                                        :class="{ 'bg-blue-100': form.water_unit == unit.id }"
                                                    >
                                                        <div class="flex-shrink-0">
                                                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="text-sm font-medium text-gray-900">{{ unit.name }}</div>
                                                        </div>
                                                        <div v-if="form.water_unit == unit.id" class="flex-shrink-0">
                                                            <CheckCircle2 class="h-4 w-4 text-blue-600" />
                                                        </div>
                                                    </button>
                                                </div>

                                                <!-- Footer -->
                                                <div class="border-t border-gray-200 p-3">
                                                    <Button
                                                        type="button"
                                                        @click="showWaterUnitDropdown = false"
                                                        class="w-full rounded bg-gray-100 py-2 text-xs text-gray-700 hover:bg-gray-200"
                                                    >
                                                        Close
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.water_note"
                                        placeholder="Add any additional notes about water consumption..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        :class="errors.water_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.water_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.water_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Light Hour -->
                        <div v-if="activeTab === 'light_hour'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-yellow-500"></div>
                                            Hours
                                        </Label>
                                        <Input
                                            v-model.number="form.light_hour"
                                            type="number"
                                            placeholder="Enter hours..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-yellow-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                                            Minutes
                                        </Label>
                                        <Input
                                            v-model.number="form.light_minute"
                                            type="number"
                                            placeholder="Enter minutes..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-amber-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.light_note"
                                        placeholder="Add any additional notes about light hours..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-yellow-500"
                                        :class="errors.light_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.light_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.light_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Destroy -->
                        <div v-if="activeTab === 'destroy'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Destroy Female Qty
                                        </Label>
                                        <Input
                                            v-model.number="form.destroy_female"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Destroy Male Qty
                                        </Label>
                                        <Input
                                            v-model.number="form.destroy_male"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="text-xs font-semibold text-gray-700">Destroy Female Reason</Label>
                                        <Input
                                            v-model="form.destroy_female_reason"
                                            type="text"
                                            placeholder="Enter reason..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="text-xs font-semibold text-gray-700">Destroy Male Reason</Label>
                                        <Input
                                            v-model="form.destroy_male_reason"
                                            type="text"
                                            placeholder="Enter reason..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.destroy_note"
                                        placeholder="Add any additional notes about destroyed chicks..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-red-500"
                                        :class="errors.destroy_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.destroy_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.destroy_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Cull -->
                        <div v-if="activeTab === 'cull'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Cull Female Qty
                                        </Label>
                                        <Input
                                            v-model.number="form.cull_female_qty"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Cull Male Qty
                                        </Label>
                                        <Input
                                            v-model.number="form.cull_male_qty"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="text-xs font-semibold text-gray-700">Cull Female Reason</Label>
                                        <Input
                                            v-model="form.cull_female_reason"
                                            type="text"
                                            placeholder="Enter reason..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="text-xs font-semibold text-gray-700">Cull Male Reason</Label>
                                        <Input
                                            v-model="form.cull_male_reason"
                                            type="text"
                                            placeholder="Enter reason..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.culling_note"
                                        placeholder="Add any additional notes about culled chicks..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="errors.culling_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.culling_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.culling_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Sexing Error -->
                        <div v-if="activeTab === 'sexing_error'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Sexing Error Female Qty
                                        </Label>
                                        <Input
                                            v-model.number="form.sexing_error_female"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Sexing Error Male Qty
                                        </Label>
                                        <Input
                                            v-model.number="form.sexing_error_male"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.serror_note"
                                        placeholder="Add any additional notes about sexing errors..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="errors.serror_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.serror_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.serror_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Weight -->
                        <div v-if="activeTab === 'weight'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Weight Male (g)
                                        </Label>
                                        <Input
                                            v-model.number="form.weight_male"
                                            type="number"
                                            min="0"
                                            step="0.1"
                                            placeholder="Enter weight..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Weight Female (g)
                                        </Label>
                                        <Input
                                            v-model.number="form.weight_female"
                                            type="number"
                                            min="0"
                                            step="0.1"
                                            placeholder="Enter weight..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.weight_note"
                                        placeholder="Add any additional notes about weight measurements..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-gray-500"
                                        :class="errors.weight_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.weight_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.weight_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Temperature -->
                        <div v-if="activeTab === 'temperature'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-red-500"></div>
                                            Inside Temperature (°C)
                                        </Label>
                                        <Input
                                            v-model.number="form.temp_inside"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter temperature..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-red-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-orange-500"></div>
                                            Std Inside Temperature (°C)
                                        </Label>
                                        <Input
                                            v-model.number="form.temp_inside_std"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter standard temp..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Outside Temperature (°C)
                                        </Label>
                                        <Input
                                            v-model.number="form.temp_outside"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter temperature..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-purple-500"></div>
                                            Std Outside Temperature (°C)
                                        </Label>
                                        <Input
                                            v-model.number="form.temp_outside_std"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter standard temp..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.temperature_note"
                                        placeholder="Add any additional notes about temperature readings..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-red-500"
                                        :class="errors.temperature_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.temperature_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.temperature_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feeding Program -->
                        <div v-if="activeTab === 'feedingprogram'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Feeding Program Female
                                        </Label>
                                        <Input
                                            v-model.number="form.female_program"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Feeding Program Male
                                        </Label>
                                        <Input
                                            v-model.number="form.male_program"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.feeding_program_note"
                                        placeholder="Add any additional notes about feeding program..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        :class="errors.feeding_program_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.feeding_program_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.feeding_program_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feed Finishing Time -->
                        <div v-if="activeTab === 'feedFinishingtime'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-pink-500"></div>
                                            Feed Finishing Time Female
                                        </Label>
                                        <Input
                                            v-model.number="form.finishtime_female"
                                            type="number"
                                            min="0"
                                            placeholder="Enter time..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Feed Finishing Time Male
                                        </Label>
                                        <Input
                                            v-model.number="form.finishtime_male"
                                            type="number"
                                            min="0"
                                            placeholder="Enter time..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.finishtime_note"
                                        placeholder="Add any additional notes about finishing time..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="errors.finishtime_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.finishtime_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.finishtime_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Humidity -->
                        <div v-if="activeTab === 'humidity'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-sky-500"></div>
                                            Today Humidity (%)
                                        </Label>
                                        <Input
                                            v-model.number="form.humidity_today"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter humidity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-sky-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Std Humidity (%)
                                        </Label>
                                        <Input
                                            v-model.number="form.humidity_std"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter standard humidity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.humidity_note"
                                        placeholder="Add any additional notes about humidity readings..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-sky-500"
                                        :class="errors.humidity_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                                    ></textarea>
                                    <p v-if="errors.humidity_note" class="mt-1 flex items-center text-xs text-red-600">
                                        <AlertTriangle class="mr-1 h-4 w-4" />
                                        {{ errors.humidity_note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Egg Collection -->
                        <div v-if="activeTab === 'egg_collection'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                                            Egg Quantity
                                        </Label>
                                        <Input
                                            v-model.number="form.egg_collection"
                                            type="number"
                                            step="0.1"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-amber-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.eggcollection_note"
                                        placeholder="Add any additional notes about egg collection..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-amber-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Medicine -->
                        <div v-if="activeTab === 'medicine'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-purple-500"></div>
                                            Medicine
                                        </Label>
                                        <select
                                            v-model="form.medicine_id"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                        >
                                            <option value="0">Select medicine...</option>
                                            <option v-for="medicine in props.medicines" :key="medicine.id" :value="medicine.id">
                                                {{ medicine.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                            Quantity
                                        </Label>
                                        <Input
                                            v-model.number="form.medicine_qty"
                                            type="number"
                                            min="0"
                                            placeholder="Enter quantity..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Unit
                                        </Label>
                                        <select
                                            v-model="form.medicine_unit"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="0">Select unit...</option>
                                            <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                                {{ unit.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-orange-500"></div>
                                            Dose
                                        </Label>
                                        <Input
                                            v-model="form.medicine_dose"
                                            type="text"
                                            placeholder="Enter dose..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.medicine_note"
                                        placeholder="Add any additional notes about medicine administration..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Vaccine -->
                        <div v-if="activeTab === 'vaccine'" class="p-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                                            Today's Vaccine Schedule
                                        </Label>
                                        <select
                                            v-model="form.vaccine_schedule_detail_id"
                                            @change="onVaccineScheduleChange"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                        >
                                            <option value="">Select today's vaccine schedule...</option>
                                            <option v-for="schedule in props.todayVaccineSchedules" :key="schedule.id" :value="schedule.id">
                                                {{ schedule.display_name }} - {{ schedule.flock_name }} ({{ schedule.shed_name }})
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Selected Schedule Info -->
                                    <div v-if="form.vaccine_schedule_detail_id" class="col-span-2 rounded-lg border border-blue-200 bg-blue-50 p-3">
                                        <div class="text-sm text-blue-800">
                                            <div class="mb-1 font-semibold">Selected Schedule Details:</div>
                                            <div v-if="selectedVaccineSchedule" class="space-y-1">
                                                <div><span class="font-medium">Vaccine:</span> {{ selectedVaccineSchedule.vaccine_name }}</div>
                                                <div><span class="font-medium">Disease:</span> {{ selectedVaccineSchedule.disease_name }}</div>
                                                <div><span class="font-medium">Age:</span> {{ selectedVaccineSchedule.age }}</div>
                                                <div><span class="font-medium">Flock:</span> {{ selectedVaccineSchedule.flock_name }}</div>
                                                <div><span class="font-medium">Shed:</span> {{ selectedVaccineSchedule.shed_name }}</div>
                                                <div v-if="selectedVaccineSchedule.notes">
                                                    <span class="font-medium">Notes:</span> {{ selectedVaccineSchedule.notes }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                            Dose
                                        </Label>
                                        <Input
                                            v-model.number="form.vaccine_dose"
                                            type="number"
                                            min="0"
                                            placeholder="Enter dose..."
                                            class="h-10 border-gray-300 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                            Unit
                                        </Label>
                                        <select
                                            v-model="form.vaccine_unit"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="">Select unit...</option>
                                            <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                                {{ unit.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <Label class="flex items-center text-xs font-semibold text-gray-700">
                                            <div class="mr-2 h-1.5 w-1.5 rounded-full bg-purple-500"></div>
                                            Upload File
                                        </Label>
                                        <input
                                            type="file"
                                            @change="
                                                (event) => {
                                                    const target = event.target as HTMLInputElement;
                                                    form.vaccine_file = target.files?.[0] || null;
                                                }
                                            "
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 transition-all duration-200 file:mr-4 file:rounded-full file:border-0 file:bg-purple-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-purple-700 hover:file:bg-purple-100 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <Label class="flex items-center text-xs font-semibold text-gray-700">
                                        <span class="mr-1 text-red-500">*</span>
                                        Additional Notes
                                    </Label>
                                    <textarea
                                        v-model="form.vaccine_note"
                                        placeholder="Add any additional notes about vaccine administration..."
                                        rows="4"
                                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="rounded-xl border border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50 p-6">
                        <div class="flex items-center justify-between">
                            <Button
                                type="button"
                                @click="prevTab"
                                :disabled="activeTabIndex === 0"
                                variant="outline"
                                class="flex h-10 items-center space-x-2 rounded-lg border-2 px-6 py-3 transition-all duration-200"
                                :class="
                                    activeTabIndex === 0
                                        ? 'cursor-not-allowed border-gray-300 opacity-50'
                                        : 'border-gray-400 hover:border-gray-500 hover:bg-gray-50'
                                "
                            >
                                <ChevronLeft class="h-4 w-4" />
                                <span>Previous</span>
                            </Button>

                            <div class="flex items-center space-x-4">
                                <div class="text-xs font-medium text-gray-600">Step {{ currentStep }} of {{ totalTabs }}</div>
                                <div class="h-2 w-32 rounded-full bg-gray-200">
                                    <div
                                        class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-300"
                                        :style="{ width: progress + '%' }"
                                    ></div>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <Button
                                    v-if="activeTabIndex < tabs.length - 1"
                                    type="button"
                                    @click="nextTab"
                                    class="flex h-10 items-center space-x-2 rounded-lg bg-gradient-to-r from-gray-900 to-black px-6 py-3 text-white shadow-lg transition-all duration-200 hover:from-gray-800 hover:to-gray-900 hover:shadow-xl"
                                >
                                    <span>Next</span>
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                                <Button
                                    v-else
                                    type="submit"
                                    class="flex h-10 transform items-center space-x-2 rounded-lg bg-gradient-to-r from-gray-900 to-black px-6 py-3 text-white shadow-lg transition-all duration-200 hover:scale-105 hover:from-gray-800 hover:to-gray-900 hover:shadow-xl"
                                    style="
                                        background: linear-gradient(135deg, #1f2937 0%, #000000 100%);
                                        box-shadow:
                                            0 4px 15px rgba(0, 0, 0, 0.3),
                                            inset 0 1px 0 rgba(255, 255, 255, 0.1);
                                    "
                                >
                                    <Save class="h-4 w-4" />
                                    <span>Submit</span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
