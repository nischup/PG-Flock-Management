<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

// Props
interface Props {
    vaccineTypes: Array<{
        id: number;
        name: string;
    }>;
    companies: Array<{
        id: number;
        name: string;
    }>;
    projects: Array<{
        id: number;
        company_id: number;
        name: string;
        code: string;
    }>;
    sheds: Array<{
        id: number;
        name: string;
    }>;
    breedTypes: Array<{
        id: number;
        name: string;
    }>;
    diseases: Array<{
        id: number;
        name: string;
    }>;
    vaccines: Array<{
        id: number;
        name: string;
    }>;
    flocks: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    batches: Array<{
        id: number;
        name: string;
    }>;
    vaccineSchedules: Array<{
        id: number;
        company_id: number;
        company_name: string;
        project_id: number;
        project_name: string;
        project_code: string;
        flock_id: number;
        flock_name: string;
        flock_code: string;
        flock_no: string;
        shed_id: number;
        shed_name: string;
        batch_id: number;
        batch_name: string;
        batch_no: string;
        breed_type_id: number;
        breed_type_name: string;
        status: number;
        created_at: string;
        updated_at: string;
        details: Array<{
            id: number;
            disease_id: number;
            disease_name: string;
            vaccine_id: number;
            vaccine_name: string;
            age: string;
            vaccination_date: string;
            next_vaccination_date: string | null;
            status: string;
            notes: string | null;
            administered_by: string | null;
            is_active: number;
        }>;
    }>;
}
const { can } = usePermissions();

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vaccine', href: '/flock' },
    { title: 'Vaccine-Schedule', href: '/flock/assign' },
];

// Table data
const flocks = ref<any[]>([]);

// Use vaccine types from props

// Modal states
const showScheduleModal = ref(false);
const showEditScheduleModal = ref(false);
const showVaccineModal = ref(false);
const showRoutingModal = ref(false);
const showLastVaccineModal = ref(false);

// Table state
const expandedRows = ref<Set<number>>(new Set());

// Toggle row expansion
const toggleRow = (scheduleId: number) => {
    if (expandedRows.value.has(scheduleId)) {
        expandedRows.value.delete(scheduleId);
    } else {
        expandedRows.value.add(scheduleId);
    }
};

// Check if row is expanded
const isRowExpanded = (scheduleId: number) => {
    return expandedRows.value.has(scheduleId);
};

// Edit schedule function
const editSchedule = (schedule: any) => {
    // Set flag to prevent watcher from clearing project_id during initialization
    isInitializingEditForm.value = true;

    // Populate edit form with schedule data
    editScheduleForm.id = schedule.id;
    editScheduleForm.company_id = schedule.company_id.toString();
    editScheduleForm.project_id = schedule.project_id.toString();
    editScheduleForm.flock_id = schedule.flock_id ? schedule.flock_id.toString() : '';
    editScheduleForm.shed_id = schedule.shed_id.toString();
    editScheduleForm.batch_id = schedule.batch_id ? schedule.batch_id.toString() : '';
    editScheduleForm.breed_type_id = schedule.breed_type_id.toString();

    // Set flock search query for edit modal
    if (schedule.flock_id) {
        const flock = props.flocks.find((f) => f.id === schedule.flock_id);
        if (flock) {
            selectedEditFlock.value = flock;
            editFlockSearchQuery.value = `${flock.name} (${flock.code})`;
        }
    }

    // Populate stages data
    editScheduleForm.stages = schedule.details.map((detail: any) => ({
        id: detail.id,
        disease_id: detail.disease_id.toString(),
        vaccine_id: detail.vaccine_id.toString(),
        age: detail.age,
        vaccination_date: detail.vaccination_date,
        next_vaccination_date: detail.next_vaccination_date || '',
        notes: detail.notes || '',
        administered_by: detail.administered_by || '',
    }));

    // Set disease and vaccine search queries for edit modal
    schedule.details.forEach((detail: any, index: number) => {
        const disease = props.diseases.find((d) => d.id === detail.disease_id);
        const vaccine = props.vaccines.find((v) => v.id === detail.vaccine_id);

        if (disease) {
            selectedEditDiseases.value[index] = disease;
            editDiseaseSearchQueries.value[index] = disease.name;
        }

        if (vaccine) {
            selectedEditVaccines.value[index] = vaccine;
            editVaccineSearchQueries.value[index] = vaccine.name;
        }
    });

    // If no stages, add one empty stage
    if (editScheduleForm.stages.length === 0) {
        editScheduleForm.stages = [
            {
                id: null,
                disease_id: '',
                vaccine_id: '',
                age: '',
                vaccination_date: '',
                next_vaccination_date: '',
                notes: '',
                administered_by: '',
            },
        ];
    }

    editScheduleForm.clearErrors();
    showEditScheduleModal.value = true;

    // Reset flag after initialization is complete
    isInitializingEditForm.value = false;
};

// Add stage for edit form
const addEditStage = () => {
    editScheduleForm.stages.push({
        id: null,
        disease_id: '',
        vaccine_id: '',
        age: '',
        vaccination_date: '',
        next_vaccination_date: '',
        notes: '',
        administered_by: '',
    });
};

// Remove stage for edit form
const removeEditStage = (index: number) => {
    editScheduleForm.stages.splice(index, 1);
};

// Update schedule function
const updateSchedule = () => {
    editScheduleForm.clearErrors();

    // Client-side validation
    const clientErrors = validateEditScheduleForm();

    if (Object.keys(clientErrors).length > 0) {
        Object.keys(clientErrors).forEach((key) => {
            editScheduleForm.setError(key as keyof typeof editScheduleForm.data, clientErrors[key]);
        });
        return;
    }

    editScheduleForm.put(`/vaccine-schedule/${editScheduleForm.id}`, {
        onSuccess: () => {
            editScheduleForm.reset();
            showEditScheduleModal.value = false;
        },
        onError: (errors) => {
            console.error('Schedule update failed:', errors);
        },
    });
};

// Client-side validation for edit schedule form
const validateEditScheduleForm = () => {
    const errors: Record<string, string> = {};

    // Basic information validation
    if (!editScheduleForm.company_id) {
        errors.company_id = 'Please select a company.';
    }
    if (!editScheduleForm.project_id) {
        errors.project_id = 'Please select a project.';
    }
    if (!editScheduleForm.flock_id) {
        errors.flock_id = 'Please select a flock.';
    }
    if (!editScheduleForm.shed_id) {
        errors.shed_id = 'Please select a shed.';
    }
    if (!editScheduleForm.batch_id) {
        errors.batch_id = 'Please select a batch.';
    }
    if (!editScheduleForm.breed_type_id) {
        errors.breed_type_id = 'Please select a breed type.';
    }

    // Stages validation
    if (!editScheduleForm.stages || editScheduleForm.stages.length === 0) {
        errors.stages = 'At least one vaccination stage is required.';
    } else {
        editScheduleForm.stages.forEach((stage: any, index: number) => {
            if (!stage.disease_id) {
                errors[`stages.${index}.disease_id`] = 'Please select a disease for this stage.';
            }
            if (!stage.vaccine_id) {
                errors[`stages.${index}.vaccine_id`] = 'Please select a vaccine for this stage.';
            }
            if (!stage.age || stage.age.trim() === '') {
                errors[`stages.${index}.age`] = 'Age is required for this stage.';
            }
            if (!stage.vaccination_date) {
                errors[`stages.${index}.vaccination_date`] = 'Vaccination date is required for this stage.';
            }
        });
    }

    return errors;
};

// Delete schedule function
const deleteSchedule = (scheduleId: number) => {
    if (confirm('Are you sure you want to delete this vaccine schedule?')) {
        scheduleForm.delete(`/vaccine-schedule/${scheduleId}`);
    }
};

// Open routing modal and clear errors
const openRoutingModal = () => {
    routingForm.clearErrors();
    showRoutingModal.value = true;
};

// Save new routing
const saveRouting = () => {
    routingForm.clearErrors();

    // Client-side validation
    const clientErrors = validateRoutingForm();

    if (Object.keys(clientErrors).length > 0) {
        Object.keys(clientErrors).forEach((key) => {
            routingForm.setError(key as keyof typeof routingForm.data, clientErrors[key]);
        });
        return;
    }

    routingForm.post('/vaccine-routing', {
        onSuccess: () => {
            routingForm.reset();
            showRoutingModal.value = false;
        },
        onError: (errors) => {
            console.error('Routing creation failed:', errors);
        },
    });
};

// Client-side validation for routing form
const validateRoutingForm = () => {
    const errors: Record<string, string> = {};

    if (!routingForm.name || routingForm.name.trim() === '') {
        errors.name = 'Route name is required.';
    } else if (routingForm.name.length > 255) {
        errors.name = 'Route name cannot exceed 255 characters.';
    }

    if (!routingForm.status) {
        errors.status = 'Status is required.';
    }

    if (routingForm.description && routingForm.description.length > 1000) {
        errors.description = 'Description cannot exceed 1000 characters.';
    }

    return errors;
};

// Open vaccine modal and clear any previous errors
const openVaccineModal = () => {
    vaccineForm.clearErrors();
    showVaccineModal.value = true;
};

// Form data (schedule) using Inertia useForm
const scheduleForm = useForm({
    company_id: '',
    project_id: '',
    flock_id: '',
    shed_id: '',
    batch_id: '',
    breed_type_id: '',
    stages: [
        {
            disease_id: '',
            vaccine_id: '',
            age: '',
            vaccination_date: '',
            next_vaccination_date: '',
            notes: '',
            administered_by: '',
        },
    ],
});

// Edit form data
const editScheduleForm = useForm({
    id: null,
    company_id: '',
    project_id: '',
    flock_id: '',
    shed_id: '',
    batch_id: '',
    breed_type_id: '',
    stages: [
        {
            id: null,
            disease_id: '',
            vaccine_id: '',
            age: '',
            vaccination_date: '',
            next_vaccination_date: '',
            notes: '',
            administered_by: '',
        },
    ],
});

// Form data (vaccine) using Inertia useForm
const vaccineForm = useForm({
    vaccine_type_id: '',
    name: '',
    applicator: '',
    dose: '',
    note: '',
    status: 1,
});

// Form data (routing) using Inertia useForm
const routingForm = useForm({
    name: '',
    description: '',
    status: 'active',
});

// Flock search functionality
const showFlockDropdown = ref(false);
const showEditFlockDropdown = ref(false);
const flockSearchQuery = ref('');
const editFlockSearchQuery = ref('');
const selectedFlock = ref<any>(null);
const selectedEditFlock = ref<any>(null);
const highlightedFlockIndex = ref(-1);
const highlightedEditFlockIndex = ref(-1);
const flockDropdownRef = ref<HTMLElement | null>(null);
const editFlockDropdownRef = ref<HTMLElement | null>(null);

// Flag to track when we're initializing edit form
const isInitializingEditForm = ref(false);

// Computed property for searchable flocks
const searchableFlocks = computed(() => {
    if (!flockSearchQuery.value) {
        return props.flocks;
    }
    return props.flocks.filter(
        (flock) =>
            flock.name.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
            flock.code.toLowerCase().includes(flockSearchQuery.value.toLowerCase()),
    );
});

// Computed property for searchable flocks in edit modal
const searchableEditFlocks = computed(() => {
    if (!editFlockSearchQuery.value) {
        return props.flocks;
    }
    return props.flocks.filter(
        (flock) =>
            flock.name.toLowerCase().includes(editFlockSearchQuery.value.toLowerCase()) ||
            flock.code.toLowerCase().includes(editFlockSearchQuery.value.toLowerCase()),
    );
});

// Disease and Vaccine search functionality
const diseaseSearchQueries = ref<Record<number, string>>({});
const vaccineSearchQueries = ref<Record<number, string>>({});
const editDiseaseSearchQueries = ref<Record<number, string>>({});
const editVaccineSearchQueries = ref<Record<number, string>>({});
const showDiseaseDropdowns = ref<Record<number, boolean>>({});
const showVaccineDropdowns = ref<Record<number, boolean>>({});
const showEditDiseaseDropdowns = ref<Record<number, boolean>>({});
const showEditVaccineDropdowns = ref<Record<number, boolean>>({});
const selectedDiseases = ref<Record<number, any>>({});
const selectedVaccines = ref<Record<number, any>>({});
const selectedEditDiseases = ref<Record<number, any>>({});
const selectedEditVaccines = ref<Record<number, any>>({});
const highlightedDiseaseIndex = ref<Record<number, number>>({});
const highlightedVaccineIndex = ref<Record<number, number>>({});
const highlightedEditDiseaseIndex = ref<Record<number, number>>({});
const highlightedEditVaccineIndex = ref<Record<number, number>>({});

// Computed properties for searchable diseases and vaccines
const searchableDiseases = computed(() => (stageIndex: number) => {
    const query = diseaseSearchQueries.value[stageIndex] || '';
    if (!query) {
        return props.diseases;
    }
    return props.diseases.filter((disease) => disease.name.toLowerCase().includes(query.toLowerCase()));
});

const searchableVaccines = computed(() => (stageIndex: number) => {
    const query = vaccineSearchQueries.value[stageIndex] || '';
    if (!query) {
        return props.vaccines;
    }
    return props.vaccines.filter((vaccine) => vaccine.name.toLowerCase().includes(query.toLowerCase()));
});

const searchableEditDiseases = computed(() => (stageIndex: number) => {
    const query = editDiseaseSearchQueries.value[stageIndex] || '';
    if (!query) {
        return props.diseases;
    }
    return props.diseases.filter((disease) => disease.name.toLowerCase().includes(query.toLowerCase()));
});

const searchableEditVaccines = computed(() => (stageIndex: number) => {
    const query = editVaccineSearchQueries.value[stageIndex] || '';
    if (!query) {
        return props.vaccines;
    }
    return props.vaccines.filter((vaccine) => vaccine.name.toLowerCase().includes(query.toLowerCase()));
});

// Add stage
const addStage = () => {
    const newStageIndex = scheduleForm.stages.length;
    scheduleForm.stages.push({
        disease_id: '',
        vaccine_id: '',
        age: '',
        vaccination_date: '',
        next_vaccination_date: '',
        notes: '',
        administered_by: '',
    });

    // Initialize search queries for the new stage
    diseaseSearchQueries.value[newStageIndex] = '';
    vaccineSearchQueries.value[newStageIndex] = '';
    showDiseaseDropdowns.value[newStageIndex] = false;
    showVaccineDropdowns.value[newStageIndex] = false;
    highlightedDiseaseIndex.value[newStageIndex] = -1;
    highlightedVaccineIndex.value[newStageIndex] = -1;
};

// Remove stage
const removeStage = (index: number) => {
    scheduleForm.stages.splice(index, 1);

    // Clean up search queries for the removed stage
    delete diseaseSearchQueries.value[index];
    delete vaccineSearchQueries.value[index];
    delete showDiseaseDropdowns.value[index];
    delete showVaccineDropdowns.value[index];
    delete selectedDiseases.value[index];
    delete selectedVaccines.value[index];
    delete highlightedDiseaseIndex.value[index];
    delete highlightedVaccineIndex.value[index];

    // Reindex remaining stages
    const newDiseaseQueries: Record<number, string> = {};
    const newVaccineQueries: Record<number, string> = {};
    const newShowDiseaseDropdowns: Record<number, boolean> = {};
    const newShowVaccineDropdowns: Record<number, boolean> = {};
    const newSelectedDiseases: Record<number, any> = {};
    const newSelectedVaccines: Record<number, any> = {};
    const newHighlightedDiseaseIndex: Record<number, number> = {};
    const newHighlightedVaccineIndex: Record<number, number> = {};

    Object.keys(diseaseSearchQueries.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newDiseaseQueries[oldIndex - 1] = diseaseSearchQueries.value[oldIndex];
        } else if (oldIndex < index) {
            newDiseaseQueries[oldIndex] = diseaseSearchQueries.value[oldIndex];
        }
    });

    Object.keys(vaccineSearchQueries.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newVaccineQueries[oldIndex - 1] = vaccineSearchQueries.value[oldIndex];
        } else if (oldIndex < index) {
            newVaccineQueries[oldIndex] = vaccineSearchQueries.value[oldIndex];
        }
    });

    Object.keys(showDiseaseDropdowns.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newShowDiseaseDropdowns[oldIndex - 1] = showDiseaseDropdowns.value[oldIndex];
        } else if (oldIndex < index) {
            newShowDiseaseDropdowns[oldIndex] = showDiseaseDropdowns.value[oldIndex];
        }
    });

    Object.keys(showVaccineDropdowns.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newShowVaccineDropdowns[oldIndex - 1] = showVaccineDropdowns.value[oldIndex];
        } else if (oldIndex < index) {
            newShowVaccineDropdowns[oldIndex] = showVaccineDropdowns.value[oldIndex];
        }
    });

    Object.keys(selectedDiseases.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newSelectedDiseases[oldIndex - 1] = selectedDiseases.value[oldIndex];
        } else if (oldIndex < index) {
            newSelectedDiseases[oldIndex] = selectedDiseases.value[oldIndex];
        }
    });

    Object.keys(selectedVaccines.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newSelectedVaccines[oldIndex - 1] = selectedVaccines.value[oldIndex];
        } else if (oldIndex < index) {
            newSelectedVaccines[oldIndex] = selectedVaccines.value[oldIndex];
        }
    });

    Object.keys(highlightedDiseaseIndex.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newHighlightedDiseaseIndex[oldIndex - 1] = highlightedDiseaseIndex.value[oldIndex];
        } else if (oldIndex < index) {
            newHighlightedDiseaseIndex[oldIndex] = highlightedDiseaseIndex.value[oldIndex];
        }
    });

    Object.keys(highlightedVaccineIndex.value).forEach((key) => {
        const oldIndex = parseInt(key);
        if (oldIndex > index) {
            newHighlightedVaccineIndex[oldIndex - 1] = highlightedVaccineIndex.value[oldIndex];
        } else if (oldIndex < index) {
            newHighlightedVaccineIndex[oldIndex] = highlightedVaccineIndex.value[oldIndex];
        }
    });

    diseaseSearchQueries.value = newDiseaseQueries;
    vaccineSearchQueries.value = newVaccineQueries;
    showDiseaseDropdowns.value = newShowDiseaseDropdowns;
    showVaccineDropdowns.value = newShowVaccineDropdowns;
    selectedDiseases.value = newSelectedDiseases;
    selectedVaccines.value = newSelectedVaccines;
    highlightedDiseaseIndex.value = newHighlightedDiseaseIndex;
    highlightedVaccineIndex.value = newHighlightedVaccineIndex;
};

// Open schedule modal and clear errors
const openScheduleModal = () => {
    scheduleForm.clearErrors();
    showScheduleModal.value = true;
};

// Save new schedule
const saveSchedule = () => {
    // Clear previous errors
    scheduleForm.clearErrors();

    // Client-side validation
    const clientErrors = validateScheduleForm();

    if (Object.keys(clientErrors).length > 0) {
        // Set client-side errors
        Object.keys(clientErrors).forEach((key) => {
            scheduleForm.setError(key as keyof typeof scheduleForm.data, clientErrors[key]);
        });
        return;
    }

    scheduleForm.post('/vaccine-schedule', {
        onSuccess: () => {
            // Reset form
            scheduleForm.reset();
            scheduleForm.stages = [
                {
                    disease_id: '',
                    vaccine_id: '',
                    age: '',
                    vaccination_date: '',
                    next_vaccination_date: '',
                    notes: '',
                    administered_by: '',
                },
            ];
            showScheduleModal.value = false;
        },
        onError: (errors) => {
            console.error('Schedule creation failed:', errors);
            // Modal stays open to display validation errors
        },
    });
};

// Client-side validation
const validateForm = () => {
    const errors: Record<string, string> = {};

    if (!vaccineForm.name || vaccineForm.name.trim() === '') {
        errors.name = 'Vaccine name is required';
    }

    if (!vaccineForm.vaccine_type_id || vaccineForm.vaccine_type_id === '') {
        errors.vaccine_type_id = 'Vaccine type is required';
    }

    return errors;
};

// Computed properties for filtered data
const filteredProjects = computed(() => {
    if (!scheduleForm.company_id) return [];
    return props.projects.filter((project) => project.company_id === parseInt(scheduleForm.company_id));
});

const filteredEditProjects = computed(() => {
    if (!editScheduleForm.company_id) return [];
    return props.projects.filter((project) => project.company_id === parseInt(editScheduleForm.company_id));
});

// Watchers to reset dependent fields when company changes
watch(
    () => scheduleForm.company_id,
    (newCompanyId) => {
        if (newCompanyId) {
            scheduleForm.project_id = '';
            scheduleForm.clearErrors('project_id');
        }
    },
);

watch(
    () => editScheduleForm.company_id,
    (newCompanyId) => {
        if (newCompanyId && !isInitializingEditForm.value) {
            editScheduleForm.project_id = '';
            editScheduleForm.clearErrors('project_id');
        }
    },
);

// Flock selection methods
const selectFlock = (flock: { id: number; name: string; code: string }) => {
    selectedFlock.value = flock;
    scheduleForm.flock_id = String(flock.id);
    flockSearchQuery.value = `${flock.name} (${flock.code})`;
    showFlockDropdown.value = false;
    highlightedFlockIndex.value = -1;
};

const clearFlockSelection = () => {
    selectedFlock.value = null;
    scheduleForm.flock_id = '';
    flockSearchQuery.value = '';
    showFlockDropdown.value = false;
    highlightedFlockIndex.value = -1;
};

const toggleFlockDropdown = () => {
    showFlockDropdown.value = !showFlockDropdown.value;
    if (showFlockDropdown.value) {
        highlightedFlockIndex.value = -1;
    }
};

// Edit flock selection methods
const selectEditFlock = (flock: { id: number; name: string; code: string }) => {
    selectedEditFlock.value = flock;
    editScheduleForm.flock_id = String(flock.id);
    editFlockSearchQuery.value = `${flock.name} (${flock.code})`;
    showEditFlockDropdown.value = false;
    highlightedEditFlockIndex.value = -1;
};

const clearEditFlockSelection = () => {
    selectedEditFlock.value = null;
    editScheduleForm.flock_id = '';
    editFlockSearchQuery.value = '';
    showEditFlockDropdown.value = false;
    highlightedEditFlockIndex.value = -1;
};

const toggleEditFlockDropdown = () => {
    showEditFlockDropdown.value = !showEditFlockDropdown.value;
    if (showEditFlockDropdown.value) {
        highlightedEditFlockIndex.value = -1;
    }
};

// Keyboard navigation for flock dropdown
const handleFlockKeydown = (event: KeyboardEvent) => {
    if (!showFlockDropdown.value) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedFlockIndex.value = Math.min(highlightedFlockIndex.value + 1, searchableFlocks.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedFlockIndex.value = Math.max(highlightedFlockIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedFlockIndex.value >= 0 && searchableFlocks.value[highlightedFlockIndex.value]) {
                selectFlock(searchableFlocks.value[highlightedFlockIndex.value]);
            }
            break;
        case 'Escape':
            showFlockDropdown.value = false;
            highlightedFlockIndex.value = -1;
            break;
    }
};

// Keyboard navigation for edit flock dropdown
const handleEditFlockKeydown = (event: KeyboardEvent) => {
    if (!showEditFlockDropdown.value) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedEditFlockIndex.value = Math.min(highlightedEditFlockIndex.value + 1, searchableEditFlocks.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedEditFlockIndex.value = Math.max(highlightedEditFlockIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedEditFlockIndex.value >= 0 && searchableEditFlocks.value[highlightedEditFlockIndex.value]) {
                selectEditFlock(searchableEditFlocks.value[highlightedEditFlockIndex.value]);
            }
            break;
        case 'Escape':
            showEditFlockDropdown.value = false;
            highlightedEditFlockIndex.value = -1;
            break;
    }
};

// Click outside to close dropdowns
const handleClickOutside = (event: Event) => {
    if (flockDropdownRef.value && !flockDropdownRef.value.contains(event.target as Node)) {
        showFlockDropdown.value = false;
        highlightedFlockIndex.value = -1;
    }
    if (editFlockDropdownRef.value && !editFlockDropdownRef.value.contains(event.target as Node)) {
        showEditFlockDropdown.value = false;
        highlightedEditFlockIndex.value = -1;
    }

    // Close all disease and vaccine dropdowns
    Object.keys(showDiseaseDropdowns.value).forEach((index) => {
        showDiseaseDropdowns.value[parseInt(index)] = false;
        highlightedDiseaseIndex.value[parseInt(index)] = -1;
    });

    Object.keys(showVaccineDropdowns.value).forEach((index) => {
        showVaccineDropdowns.value[parseInt(index)] = false;
        highlightedVaccineIndex.value[parseInt(index)] = -1;
    });

    Object.keys(showEditDiseaseDropdowns.value).forEach((index) => {
        showEditDiseaseDropdowns.value[parseInt(index)] = false;
        highlightedEditDiseaseIndex.value[parseInt(index)] = -1;
    });

    Object.keys(showEditVaccineDropdowns.value).forEach((index) => {
        showEditVaccineDropdowns.value[parseInt(index)] = false;
        highlightedEditVaccineIndex.value[parseInt(index)] = -1;
    });
};

// Add event listeners
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Disease selection methods
const selectDisease = (stageIndex: number, disease: { id: number; name: string }) => {
    selectedDiseases.value[stageIndex] = disease;
    scheduleForm.stages[stageIndex].disease_id = String(disease.id);
    diseaseSearchQueries.value[stageIndex] = disease.name;
    showDiseaseDropdowns.value[stageIndex] = false;
    highlightedDiseaseIndex.value[stageIndex] = -1;
};

const clearDiseaseSelection = (stageIndex: number) => {
    delete selectedDiseases.value[stageIndex];
    scheduleForm.stages[stageIndex].disease_id = '';
    diseaseSearchQueries.value[stageIndex] = '';
    showDiseaseDropdowns.value[stageIndex] = false;
    highlightedDiseaseIndex.value[stageIndex] = -1;
};

const toggleDiseaseDropdown = (stageIndex: number) => {
    showDiseaseDropdowns.value[stageIndex] = !showDiseaseDropdowns.value[stageIndex];
    if (showDiseaseDropdowns.value[stageIndex]) {
        highlightedDiseaseIndex.value[stageIndex] = -1;
    }
};

// Vaccine selection methods
const selectVaccine = (stageIndex: number, vaccine: { id: number; name: string }) => {
    selectedVaccines.value[stageIndex] = vaccine;
    scheduleForm.stages[stageIndex].vaccine_id = String(vaccine.id);
    vaccineSearchQueries.value[stageIndex] = vaccine.name;
    showVaccineDropdowns.value[stageIndex] = false;
    highlightedVaccineIndex.value[stageIndex] = -1;
};

const clearVaccineSelection = (stageIndex: number) => {
    delete selectedVaccines.value[stageIndex];
    scheduleForm.stages[stageIndex].vaccine_id = '';
    vaccineSearchQueries.value[stageIndex] = '';
    showVaccineDropdowns.value[stageIndex] = false;
    highlightedVaccineIndex.value[stageIndex] = -1;
};

const toggleVaccineDropdown = (stageIndex: number) => {
    showVaccineDropdowns.value[stageIndex] = !showVaccineDropdowns.value[stageIndex];
    if (showVaccineDropdowns.value[stageIndex]) {
        highlightedVaccineIndex.value[stageIndex] = -1;
    }
};

// Edit disease selection methods
const selectEditDisease = (stageIndex: number, disease: { id: number; name: string }) => {
    selectedEditDiseases.value[stageIndex] = disease;
    editScheduleForm.stages[stageIndex].disease_id = String(disease.id);
    editDiseaseSearchQueries.value[stageIndex] = disease.name;
    showEditDiseaseDropdowns.value[stageIndex] = false;
    highlightedEditDiseaseIndex.value[stageIndex] = -1;
};

const clearEditDiseaseSelection = (stageIndex: number) => {
    delete selectedEditDiseases.value[stageIndex];
    editScheduleForm.stages[stageIndex].disease_id = '';
    editDiseaseSearchQueries.value[stageIndex] = '';
    showEditDiseaseDropdowns.value[stageIndex] = false;
    highlightedEditDiseaseIndex.value[stageIndex] = -1;
};

const toggleEditDiseaseDropdown = (stageIndex: number) => {
    showEditDiseaseDropdowns.value[stageIndex] = !showEditDiseaseDropdowns.value[stageIndex];
    if (showEditDiseaseDropdowns.value[stageIndex]) {
        highlightedEditDiseaseIndex.value[stageIndex] = -1;
    }
};

// Edit vaccine selection methods
const selectEditVaccine = (stageIndex: number, vaccine: { id: number; name: string }) => {
    selectedEditVaccines.value[stageIndex] = vaccine;
    editScheduleForm.stages[stageIndex].vaccine_id = String(vaccine.id);
    editVaccineSearchQueries.value[stageIndex] = vaccine.name;
    showEditVaccineDropdowns.value[stageIndex] = false;
    highlightedEditVaccineIndex.value[stageIndex] = -1;
};

const clearEditVaccineSelection = (stageIndex: number) => {
    delete selectedEditVaccines.value[stageIndex];
    editScheduleForm.stages[stageIndex].vaccine_id = '';
    editVaccineSearchQueries.value[stageIndex] = '';
    showEditVaccineDropdowns.value[stageIndex] = false;
    highlightedEditVaccineIndex.value[stageIndex] = -1;
};

const toggleEditVaccineDropdown = (stageIndex: number) => {
    showEditVaccineDropdowns.value[stageIndex] = !showEditVaccineDropdowns.value[stageIndex];
    if (showEditVaccineDropdowns.value[stageIndex]) {
        highlightedEditVaccineIndex.value[stageIndex] = -1;
    }
};

// Keyboard navigation for disease dropdown
const handleDiseaseKeydown = (event: KeyboardEvent, stageIndex: number) => {
    if (!showDiseaseDropdowns.value[stageIndex]) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedDiseaseIndex.value[stageIndex] = Math.min(
                highlightedDiseaseIndex.value[stageIndex] + 1,
                searchableDiseases.value(stageIndex).length - 1,
            );
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedDiseaseIndex.value[stageIndex] = Math.max(highlightedDiseaseIndex.value[stageIndex] - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedDiseaseIndex.value[stageIndex] >= 0 && searchableDiseases.value(stageIndex)[highlightedDiseaseIndex.value[stageIndex]]) {
                selectDisease(stageIndex, searchableDiseases.value(stageIndex)[highlightedDiseaseIndex.value[stageIndex]]);
            }
            break;
        case 'Escape':
            showDiseaseDropdowns.value[stageIndex] = false;
            highlightedDiseaseIndex.value[stageIndex] = -1;
            break;
    }
};

// Keyboard navigation for vaccine dropdown
const handleVaccineKeydown = (event: KeyboardEvent, stageIndex: number) => {
    if (!showVaccineDropdowns.value[stageIndex]) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedVaccineIndex.value[stageIndex] = Math.min(
                highlightedVaccineIndex.value[stageIndex] + 1,
                searchableVaccines.value(stageIndex).length - 1,
            );
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedVaccineIndex.value[stageIndex] = Math.max(highlightedVaccineIndex.value[stageIndex] - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedVaccineIndex.value[stageIndex] >= 0 && searchableVaccines.value(stageIndex)[highlightedVaccineIndex.value[stageIndex]]) {
                selectVaccine(stageIndex, searchableVaccines.value(stageIndex)[highlightedVaccineIndex.value[stageIndex]]);
            }
            break;
        case 'Escape':
            showVaccineDropdowns.value[stageIndex] = false;
            highlightedVaccineIndex.value[stageIndex] = -1;
            break;
    }
};

// Keyboard navigation for edit disease dropdown
const handleEditDiseaseKeydown = (event: KeyboardEvent, stageIndex: number) => {
    if (!showEditDiseaseDropdowns.value[stageIndex]) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedEditDiseaseIndex.value[stageIndex] = Math.min(
                highlightedEditDiseaseIndex.value[stageIndex] + 1,
                searchableEditDiseases.value(stageIndex).length - 1,
            );
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedEditDiseaseIndex.value[stageIndex] = Math.max(highlightedEditDiseaseIndex.value[stageIndex] - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (
                highlightedEditDiseaseIndex.value[stageIndex] >= 0 &&
                searchableEditDiseases.value(stageIndex)[highlightedEditDiseaseIndex.value[stageIndex]]
            ) {
                selectEditDisease(stageIndex, searchableEditDiseases.value(stageIndex)[highlightedEditDiseaseIndex.value[stageIndex]]);
            }
            break;
        case 'Escape':
            showEditDiseaseDropdowns.value[stageIndex] = false;
            highlightedEditDiseaseIndex.value[stageIndex] = -1;
            break;
    }
};

// Keyboard navigation for edit vaccine dropdown
const handleEditVaccineKeydown = (event: KeyboardEvent, stageIndex: number) => {
    if (!showEditVaccineDropdowns.value[stageIndex]) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedEditVaccineIndex.value[stageIndex] = Math.min(
                highlightedEditVaccineIndex.value[stageIndex] + 1,
                searchableEditVaccines.value(stageIndex).length - 1,
            );
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedEditVaccineIndex.value[stageIndex] = Math.max(highlightedEditVaccineIndex.value[stageIndex] - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (
                highlightedEditVaccineIndex.value[stageIndex] >= 0 &&
                searchableEditVaccines.value(stageIndex)[highlightedEditVaccineIndex.value[stageIndex]]
            ) {
                selectEditVaccine(stageIndex, searchableEditVaccines.value(stageIndex)[highlightedEditVaccineIndex.value[stageIndex]]);
            }
            break;
        case 'Escape':
            showEditVaccineDropdowns.value[stageIndex] = false;
            highlightedEditVaccineIndex.value[stageIndex] = -1;
            break;
    }
};

// Error categorization for vaccine schedule form
const basicInfoFields = ['company_id', 'project_id', 'flock_id', 'shed_id', 'batch_id', 'breed_type_id'];

const basicInfoErrors = computed(() => {
    const errors: Record<string, any> = {};
    Object.keys(scheduleForm.errors).forEach((key) => {
        if (basicInfoFields.includes(key)) {
            errors[key] = (scheduleForm.errors as any)[key];
        }
    });
    return errors;
});

const hasBasicInfoErrors = computed(() => Object.keys(basicInfoErrors.value).length > 0);

const stagesErrors = computed(() => {
    const errors: Record<string, any> = {};
    Object.keys(scheduleForm.errors).forEach((key) => {
        if (key.startsWith('stages')) {
            errors[key] = (scheduleForm.errors as any)[key];
        }
    });
    return errors;
});

const hasStagesErrors = computed(() => Object.keys(stagesErrors.value).length > 0);

const otherErrors = computed(() => {
    const errors: Record<string, any> = {};
    Object.keys(scheduleForm.errors).forEach((key) => {
        if (!basicInfoFields.includes(key) && !key.startsWith('stages')) {
            errors[key] = (scheduleForm.errors as any)[key];
        }
    });
    return errors;
});

const hasOtherErrors = computed(() => Object.keys(otherErrors.value).length > 0);

// Client-side validation for schedule form
const validateScheduleForm = () => {
    const errors: Record<string, string> = {};

    // Basic information validation
    if (!scheduleForm.company_id) {
        errors.company_id = 'Please select a company.';
    }

    if (!scheduleForm.project_id) {
        errors.project_id = 'Please select a project.';
    }

    if (!scheduleForm.flock_id) {
        errors.flock_id = 'Please select a flock.';
    }

    if (!scheduleForm.shed_id) {
        errors.shed_id = 'Please select a shed.';
    }

    if (!scheduleForm.batch_id) {
        errors.batch_id = 'Please select a batch.';
    }

    if (!scheduleForm.breed_type_id) {
        errors.breed_type_id = 'Please select a breed type.';
    }

    // Stages validation
    if (!scheduleForm.stages || scheduleForm.stages.length === 0) {
        errors.stages = 'At least one vaccination stage is required.';
    } else {
        scheduleForm.stages.forEach((stage: any, index: number) => {
            if (!stage.disease_id) {
                errors[`stages.${index}.disease_id`] = 'Please select a disease for this stage.';
            }

            if (!stage.vaccine_id) {
                errors[`stages.${index}.vaccine_id`] = 'Please select a vaccine for this stage.';
            }

            if (!stage.age || stage.age.trim() === '') {
                errors[`stages.${index}.age`] = 'Age is required for this stage.';
            }

            if (!stage.vaccination_date) {
                errors[`stages.${index}.vaccination_date`] = 'Vaccination date is required for this stage.';
            }
        });
    }

    return errors;
};

// Save new vaccine
const saveVaccine = () => {
    // Clear previous errors
    vaccineForm.clearErrors();

    // Client-side validation
    const clientErrors = validateForm();
    if (Object.keys(clientErrors).length > 0) {
        // Set client-side errors
        Object.keys(clientErrors).forEach((key) => {
            vaccineForm.setError(key as keyof typeof vaccineForm.data, clientErrors[key]);
        });
        return; // Don't submit if client validation fails
    }

    vaccineForm.post('/vaccine', {
        onSuccess: () => {
            // Reset form
            vaccineForm.reset();
            vaccineForm.status = 1; // Reset status to active
            showVaccineModal.value = false;
        },
        onError: (errors) => {
            // Keep modal open and show validation errors
            console.error('Vaccine creation failed:', errors);
            // Modal stays open to display validation errors
        },
    });
};
</script>

<template>
    <Head title="Vaccine Schedule" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
                <!-- Add New Vaccine -->
                <button
                    v-if="can('vaccine-schedule.create')"
                    @click="openVaccineModal"
                    class="flex transform items-center gap-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl"
                >
                    <div class="rounded-lg bg-white/20 p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    Add New Vaccine
                </button>

                <!-- Vaccination Schedule -->
                <button
                    v-if="can('vaccine-schedule.create')"
                    @click="openScheduleModal"
                    class="flex transform items-center gap-3 rounded-xl bg-gradient-to-r from-green-600 to-green-700 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-green-700 hover:to-green-800 hover:shadow-xl"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    Vaccination Schedule
                </button>

                <button
                    v-if="can('vaccine-routing.create')"
                    @click="openRoutingModal"
                    class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white hover:bg-purple-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-6-6V7h6v4m-6 0H5l7-7 7 7h-4" />
                    </svg>
                    Vaccine Routing
                </button>

                <!-- View Used Vaccine -->
                <button
                    @click="showLastVaccineModal = true"
                    class="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                    </svg>
                    View Used Vaccine
                </button>
            </div>

            <!-- Vaccine Schedules Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-lg dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Vaccination Management</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage vaccination schedules and their details</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr>
                                <th class="border-b bg-blue-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">S/N</th>
                                <th class="border-b bg-green-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Company</th>
                                <th class="border-b bg-purple-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Project</th>
                                <th class="border-b bg-orange-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Flock Details</th>
                                <th class="border-b bg-pink-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Breed Type</th>
                                <th class="border-b bg-indigo-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Status</th>
                                <th class="border-b bg-red-500 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Created</th>
                                <th class="border-b bg-gray-600 px-4 py-2 text-sm font-semibold whitespace-nowrap text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(schedule, index) in props.vaccineSchedules" :key="schedule.id">
                                <!-- Main Schedule Row -->
                                <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" @click="toggleRow(schedule.id)">
                                    <td class="border-b px-4 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <button class="mr-2 rounded p-1 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 transition-transform duration-200"
                                                    :class="{ 'rotate-90': isRowExpanded(schedule.id) }"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ index + 1 }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-b px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.company_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.details.length }} stage(s)</div>
                                    </td>
                                    <td class="border-b px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.project_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.project_code }}</div>
                                    </td>
                                    <td class="border-b px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.flock_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ schedule.flock_code
                                            }}{{ schedule.batch_name && schedule.batch_name !== 'N/A' ? ` | Batch: ${schedule.batch_name}` : '' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Shed: {{ schedule.shed_name }}</div>
                                    </td>
                                    <td class="border-b px-4 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.breed_type_name }}</div>
                                    </td>
                                    <td class="border-b px-4 py-2 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="
                                                schedule.status === 1
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            "
                                        >
                                            {{ schedule.status === 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="border-b px-4 py-2 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ new Date(schedule.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="border-b px-4 py-2 text-sm font-medium whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <button
                                                @click.stop="editSchedule(schedule)"
                                                class="text-blue-600 transition-colors duration-200 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Edit Schedule"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                @click.stop="deleteSchedule(schedule.id)"
                                                class="text-red-600 transition-colors duration-200 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Delete Schedule"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Expanded Details Row -->
                                <tr v-if="isRowExpanded(schedule.id)" class="bg-gray-50 dark:bg-gray-700/50">
                                    <td colspan="8" class="border-b px-4 py-4">
                                        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-600 dark:bg-gray-800">
                                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-600">
                                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Vaccination Stages</h4>
                                            </div>
                                            <div class="p-4">
                                                <div v-if="schedule.details.length === 0" class="py-4 text-center text-gray-500 dark:text-gray-400">
                                                    No vaccination stages found
                                                </div>
                                                <div v-else class="space-y-4">
                                                    <div
                                                        v-for="(detail, index) in schedule.details"
                                                        :key="detail.id"
                                                        class="rounded-lg border border-gray-200 p-4 dark:border-gray-600"
                                                    >
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                                            <div>
                                                                <label
                                                                    class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                    >Stage {{ index + 1 }}</label
                                                                >
                                                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ detail.disease_name }} → {{ detail.vaccine_name }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                    >Age</label
                                                                >
                                                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ detail.age }}</div>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                    >Vaccination Date</label
                                                                >
                                                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ new Date(detail.vaccination_date).toLocaleDateString() }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                    >Next Date</label
                                                                >
                                                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{
                                                                        detail.next_vaccination_date
                                                                            ? new Date(detail.next_vaccination_date).toLocaleDateString()
                                                                            : 'N/A'
                                                                    }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                                                            <div>
                                                                <label
                                                                    class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                    >Status</label
                                                                >
                                                                <div class="mt-1">
                                                                    <span
                                                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                                        :class="{
                                                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                                                detail.status === 'pending',
                                                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                                                detail.status === 'completed',
                                                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                                                detail.status === 'cancelled',
                                                                        }"
                                                                    >
                                                                        {{ detail.status.charAt(0).toUpperCase() + detail.status.slice(1) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div v-if="detail.administered_by">
                                                                <label
                                                                    class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                    >Administered By</label
                                                                >
                                                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ detail.administered_by }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div v-if="detail.notes" class="mt-3">
                                                            <label
                                                                class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                                                >Notes</label
                                                            >
                                                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ detail.notes }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr v-if="props.vaccineSchedules.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="mx-auto mb-4 h-12 w-12 text-gray-300"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <p class="text-lg font-medium">No vaccine schedules found</p>
                                        <p class="text-sm">Get started by creating your first vaccination schedule.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modern Vaccine Schedule Modal -->
            <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div
                    class="max-h-[95vh] w-full max-w-6xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out dark:bg-gray-800"
                >
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-white/20 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold">Add Vaccine Schedule</h2>
                                    <p class="text-sm text-green-100">Create a comprehensive vaccination schedule for your flock</p>
                                </div>
                            </div>
                            <button
                                @click="
                                    () => {
                                        scheduleForm.clearErrors();
                                        showScheduleModal = false;
                                    }
                                "
                                class="rounded-lg p-2 transition-colors duration-200 hover:bg-white/20"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="max-h-[calc(95vh-140px)] overflow-y-auto p-6">
                        <!-- General Error Message -->
                        <div
                            v-if="scheduleForm.hasErrors"
                            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-red-100 p-1 dark:bg-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-2 font-semibold">Please correct the following errors:</p>
                                    <div class="space-y-2">
                                        <!-- Basic Information Errors -->
                                        <div v-if="hasBasicInfoErrors" class="border-l-2 border-red-300 pl-3">
                                            <p class="mb-1 text-sm font-medium text-red-600 dark:text-red-400">Basic Information:</p>
                                            <ul class="space-y-1 text-sm">
                                                <li v-for="(error, field) in basicInfoErrors" :key="field" class="flex items-center gap-2">
                                                    <span class="h-1 w-1 rounded-full bg-red-500"></span>
                                                    {{ Array.isArray(error) ? error[0] : error }}
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Stages Errors -->
                                        <div v-if="hasStagesErrors" class="border-l-2 border-red-300 pl-3">
                                            <p class="mb-1 text-sm font-medium text-red-600 dark:text-red-400">Vaccination Stages:</p>
                                            <ul class="space-y-1 text-sm">
                                                <li v-for="(error, field) in stagesErrors" :key="field" class="flex items-center gap-2">
                                                    <span class="h-1 w-1 rounded-full bg-red-500"></span>
                                                    {{ Array.isArray(error) ? error[0] : error }}
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Other Errors -->
                                        <div v-if="hasOtherErrors" class="border-l-2 border-red-300 pl-3">
                                            <p class="mb-1 text-sm font-medium text-red-600 dark:text-red-400">Other Issues:</p>
                                            <ul class="space-y-1 text-sm">
                                                <li v-for="(error, field) in otherErrors" :key="field" class="flex items-center gap-2">
                                                    <span class="h-1 w-1 rounded-full bg-red-500"></span>
                                                    {{ Array.isArray(error) ? error[0] : error }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Information -->
                        <div
                            class="mb-6 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20"
                        >
                            <div class="flex items-center gap-3 text-sm text-blue-700 dark:text-blue-300">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Required Information</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">
                                        All fields marked with <span class="font-bold text-red-500">*</span> are mandatory. You can add multiple
                                        vaccination stages for comprehensive scheduling.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information Section -->
                        <div class="mb-8">
                            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Basic Information
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <!-- Company -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                            Company
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        v-model="scheduleForm.company_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': scheduleForm.errors.company_id,
                                            'border-gray-300 dark:border-gray-600': !scheduleForm.errors.company_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Company</option>
                                        <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                            {{ company.name }}
                                        </option>
                                    </select>
                                    <div v-if="scheduleForm.errors.company_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ scheduleForm.errors.company_id }}
                                    </div>
                                </div>

                                <!-- Project -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                />
                                            </svg>
                                            Project
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        v-model="scheduleForm.project_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': scheduleForm.errors.project_id,
                                            'border-gray-300 dark:border-gray-600': !scheduleForm.errors.project_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Project</option>
                                        <option v-for="project in filteredProjects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div v-if="scheduleForm.errors.project_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ scheduleForm.errors.project_id }}
                                    </div>
                                </div>

                                <!-- Flock -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                />
                                            </svg>
                                            Flock
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                    </label>
                                    <div ref="flockDropdownRef" class="relative">
                                        <!-- Search Input -->
                                        <div class="relative">
                                            <input
                                                v-model="flockSearchQuery"
                                                @focus="showFlockDropdown = true"
                                                @input="
                                                    showFlockDropdown = true;
                                                    highlightedFlockIndex = -1;
                                                "
                                                @keydown="handleFlockKeydown"
                                                type="text"
                                                placeholder="Search flocks..."
                                                class="w-full rounded-xl border px-4 py-3 pr-12 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                :class="{
                                                    'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': scheduleForm.errors.flock_id,
                                                    'border-gray-300 dark:border-gray-600': !scheduleForm.errors.flock_id,
                                                }"
                                                required
                                            />
                                            <button
                                                type="button"
                                                @click="toggleFlockDropdown"
                                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Dropdown -->
                                        <div
                                            v-if="showFlockDropdown"
                                            class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-700 dark:ring-gray-600"
                                        >
                                            <div class="max-h-60 overflow-y-auto">
                                                <div v-if="searchableFlocks.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    No flocks found
                                                </div>
                                                <button
                                                    v-for="(flock, index) in searchableFlocks"
                                                    :key="flock.id"
                                                    type="button"
                                                    @click="selectFlock(flock)"
                                                    :class="[
                                                        'w-full px-4 py-3 text-left text-sm transition-colors focus:outline-none',
                                                        index === highlightedFlockIndex
                                                            ? 'bg-blue-50 text-blue-900 dark:bg-blue-900/20 dark:text-blue-100'
                                                            : 'hover:bg-gray-50 dark:hover:bg-gray-600',
                                                    ]"
                                                >
                                                    {{ flock.name }} ({{ flock.code }})
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Clear button -->
                                        <button
                                            v-if="selectedFlock"
                                            type="button"
                                            @click="clearFlockSelection"
                                            class="absolute top-1/2 right-10 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="scheduleForm.errors.flock_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ scheduleForm.errors.flock_id }}
                                    </div>
                                </div>

                                <!-- Shed -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                                                />
                                            </svg>
                                            Shed
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        v-model="scheduleForm.shed_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': scheduleForm.errors.shed_id,
                                            'border-gray-300 dark:border-gray-600': !scheduleForm.errors.shed_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Shed</option>
                                        <option v-for="shed in props.sheds" :key="shed.id" :value="shed.id">
                                            {{ shed.name }}
                                        </option>
                                    </select>
                                    <div v-if="scheduleForm.errors.shed_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ scheduleForm.errors.shed_id }}
                                    </div>
                                </div>

                                <!-- Batch -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                />
                                            </svg>
                                            Batch
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        v-model="scheduleForm.batch_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': scheduleForm.errors.batch_id,
                                            'border-gray-300 dark:border-gray-600': !scheduleForm.errors.batch_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Batch</option>
                                        <option v-for="batch in props.batches" :key="batch.id" :value="batch.id">
                                            {{ batch.name }}
                                        </option>
                                    </select>
                                    <div v-if="scheduleForm.errors.batch_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ scheduleForm.errors.batch_id }}
                                    </div>
                                </div>

                                <!-- Breed Type -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                                />
                                            </svg>
                                            Breed Type
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                    </label>
                                    <select
                                        v-model="scheduleForm.breed_type_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': scheduleForm.errors.breed_type_id,
                                            'border-gray-300 dark:border-gray-600': !scheduleForm.errors.breed_type_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Breed Type</option>
                                        <option v-for="breedType in props.breedTypes" :key="breedType.id" :value="breedType.id">
                                            {{ breedType.name }}
                                        </option>
                                    </select>
                                    <div v-if="scheduleForm.errors.breed_type_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ scheduleForm.errors.breed_type_id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vaccination Stages Section -->
                        <div class="mb-8">
                            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                Vaccination Stages
                            </h3>

                            <div
                                v-for="(stage, index) in scheduleForm.stages"
                                :key="index"
                                class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-600 dark:bg-gray-700/50"
                            >
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">Stage {{ index + 1 }}</h4>
                                    <button
                                        v-if="scheduleForm.stages.length > 1"
                                        @click="removeStage(index)"
                                        class="rounded-lg p-2 text-red-500 transition-colors duration-200 hover:bg-red-100 dark:hover:bg-red-900/20"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <!-- Disease -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Disease <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input
                                                    v-model="diseaseSearchQueries[index]"
                                                    @focus="showDiseaseDropdowns[index] = true"
                                                    @input="
                                                        showDiseaseDropdowns[index] = true;
                                                        highlightedDiseaseIndex[index] = -1;
                                                    "
                                                    @keydown="handleDiseaseKeydown($event, index)"
                                                    type="text"
                                                    placeholder="Search diseases..."
                                                    class="w-full rounded-lg border px-3 py-2 pr-10 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                                    :class="{
                                                        'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (
                                                            scheduleForm.errors as any
                                                        )[`stages.${index}.disease_id`],
                                                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[
                                                            `stages.${index}.disease_id`
                                                        ],
                                                    }"
                                                    required
                                                />
                                                <button
                                                    type="button"
                                                    @click="toggleDiseaseDropdown(index)"
                                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown -->
                                            <div
                                                v-if="showDiseaseDropdowns[index]"
                                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-600 dark:ring-gray-500"
                                            >
                                                <div class="max-h-48 overflow-y-auto">
                                                    <div
                                                        v-if="searchableDiseases(index).length === 0"
                                                        class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        No diseases found
                                                    </div>
                                                    <button
                                                        v-for="(disease, diseaseIndex) in searchableDiseases(index)"
                                                        :key="disease.id"
                                                        type="button"
                                                        @click="selectDisease(index, disease)"
                                                        :class="[
                                                            'w-full px-3 py-2 text-left text-sm transition-colors focus:outline-none',
                                                            diseaseIndex === highlightedDiseaseIndex[index]
                                                                ? 'bg-green-50 text-green-900 dark:bg-green-900/20 dark:text-green-100'
                                                                : 'hover:bg-gray-50 dark:hover:bg-gray-500',
                                                        ]"
                                                    >
                                                        {{ disease.name }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Clear button -->
                                            <button
                                                v-if="selectedDiseases[index]"
                                                type="button"
                                                @click="clearDiseaseSelection(index)"
                                                class="absolute top-1/2 right-8 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-if="(scheduleForm.errors as any)[`stages.${index}.disease_id`]" class="text-sm text-red-500">
                                            {{ (scheduleForm.errors as any)[`stages.${index}.disease_id`] }}
                                        </div>
                                    </div>

                                    <!-- Vaccine -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Vaccine <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input
                                                    v-model="vaccineSearchQueries[index]"
                                                    @focus="showVaccineDropdowns[index] = true"
                                                    @input="
                                                        showVaccineDropdowns[index] = true;
                                                        highlightedVaccineIndex[index] = -1;
                                                    "
                                                    @keydown="handleVaccineKeydown($event, index)"
                                                    type="text"
                                                    placeholder="Search vaccines..."
                                                    class="w-full rounded-lg border px-3 py-2 pr-10 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                                    :class="{
                                                        'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (
                                                            scheduleForm.errors as any
                                                        )[`stages.${index}.vaccine_id`],
                                                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[
                                                            `stages.${index}.vaccine_id`
                                                        ],
                                                    }"
                                                    required
                                                />
                                                <button
                                                    type="button"
                                                    @click="toggleVaccineDropdown(index)"
                                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown -->
                                            <div
                                                v-if="showVaccineDropdowns[index]"
                                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-600 dark:ring-gray-500"
                                            >
                                                <div class="max-h-48 overflow-y-auto">
                                                    <div
                                                        v-if="searchableVaccines(index).length === 0"
                                                        class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        No vaccines found
                                                    </div>
                                                    <button
                                                        v-for="(vaccine, vaccineIndex) in searchableVaccines(index)"
                                                        :key="vaccine.id"
                                                        type="button"
                                                        @click="selectVaccine(index, vaccine)"
                                                        :class="[
                                                            'w-full px-3 py-2 text-left text-sm transition-colors focus:outline-none',
                                                            vaccineIndex === highlightedVaccineIndex[index]
                                                                ? 'bg-green-50 text-green-900 dark:bg-green-900/20 dark:text-green-100'
                                                                : 'hover:bg-gray-50 dark:hover:bg-gray-500',
                                                        ]"
                                                    >
                                                        {{ vaccine.name }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Clear button -->
                                            <button
                                                v-if="selectedVaccines[index]"
                                                type="button"
                                                @click="clearVaccineSelection(index)"
                                                class="absolute top-1/2 right-8 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-if="(scheduleForm.errors as any)[`stages.${index}.vaccine_id`]" class="text-sm text-red-500">
                                            {{ (scheduleForm.errors as any)[`stages.${index}.vaccine_id`] }}
                                        </div>
                                    </div>

                                    <!-- Age -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Age <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="stage.age"
                                            type="text"
                                            class="w-full rounded-lg border px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                            :class="{
                                                'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (scheduleForm.errors as any)[
                                                    `stages.${index}.age`
                                                ],
                                                'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[`stages.${index}.age`],
                                            }"
                                            placeholder="e.g., 7 days, 2 weeks"
                                            required
                                        />
                                        <div v-if="(scheduleForm.errors as any)[`stages.${index}.age`]" class="text-sm text-red-500">
                                            {{ (scheduleForm.errors as any)[`stages.${index}.age`] }}
                                        </div>
                                    </div>

                                    <!-- Vaccination Date -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Vaccination Date <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="stage.vaccination_date"
                                                type="date"
                                                class="w-full rounded-xl border px-4 py-3 pr-4 pl-12 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white dark:placeholder-gray-400"
                                                :class="{
                                                    'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (scheduleForm.errors as any)[
                                                        `stages.${index}.vaccination_date`
                                                    ],
                                                    'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[
                                                        `stages.${index}.vaccination_date`
                                                    ],
                                                }"
                                                required
                                            />
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-gray-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <div
                                            v-if="(scheduleForm.errors as any)[`stages.${index}.vaccination_date`]"
                                            class="flex items-center gap-1 text-sm text-red-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            {{ (scheduleForm.errors as any)[`stages.${index}.vaccination_date`] }}
                                        </div>
                                    </div>

                                    <!-- Next Vaccination Date -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Next Vaccination Date
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(Optional)</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="stage.next_vaccination_date"
                                                type="date"
                                                class="w-full rounded-xl border px-4 py-3 pr-4 pl-12 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white dark:placeholder-gray-400"
                                                :class="{
                                                    'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (scheduleForm.errors as any)[
                                                        `stages.${index}.next_vaccination_date`
                                                    ],
                                                    'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[
                                                        `stages.${index}.next_vaccination_date`
                                                    ],
                                                }"
                                            />
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-gray-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <div
                                            v-if="(scheduleForm.errors as any)[`stages.${index}.next_vaccination_date`]"
                                            class="flex items-center gap-1 text-sm text-red-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            {{ (scheduleForm.errors as any)[`stages.${index}.next_vaccination_date`] }}
                                        </div>
                                    </div>

                                    <!-- Administered By -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300"> Administered By </label>
                                        <input
                                            v-model="stage.administered_by"
                                            type="text"
                                            class="w-full rounded-lg border px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                            placeholder="e.g., Dr. Smith"
                                        />
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mt-4 space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300"> Notes </label>
                                    <textarea
                                        v-model="stage.notes"
                                        class="w-full resize-none rounded-lg border px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                        placeholder="Additional notes for this vaccination stage..."
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Add Stage Button -->
                            <button
                                @click="addStage"
                                class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 font-medium text-white transition-all duration-200 hover:bg-green-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Another Stage
                            </button>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700/50">
                        <div class="flex justify-end gap-3">
                            <button
                                class="rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-700 transition-all duration-200 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                @click="
                                    () => {
                                        scheduleForm.clearErrors();
                                        showScheduleModal = false;
                                    }
                                "
                                :disabled="scheduleForm.processing"
                            >
                                Cancel
                            </button>
                            <button
                                class="transform rounded-xl bg-gradient-to-r from-green-600 to-green-700 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-green-700 hover:to-green-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                @click="saveSchedule"
                                :disabled="scheduleForm.processing"
                            >
                                <span v-if="scheduleForm.processing" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Creating Schedule...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Create Schedule
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Vaccine Modal -->
            <div v-if="showVaccineModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div
                    class="max-h-[90vh] w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out dark:bg-gray-800"
                >
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-white/20 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold">Add New Vaccine</h2>
                                    <p class="text-sm text-blue-100">Create a new vaccine entry for your inventory</p>
                                </div>
                            </div>
                            <button
                                @click="
                                    () => {
                                        vaccineForm.clearErrors();
                                        showVaccineModal = false;
                                    }
                                "
                                class="rounded-lg p-2 transition-colors duration-200 hover:bg-white/20"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="max-h-[calc(90vh-140px)] overflow-y-auto p-6">
                        <!-- General Error Message -->
                        <div
                            v-if="vaccineForm.hasErrors"
                            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-red-100 p-1 dark:bg-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Please correct the following errors:</p>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        <li v-for="(error, field) in vaccineForm.errors" :key="field" class="flex items-center gap-2">
                                            <span class="h-1 w-1 rounded-full bg-red-500"></span>
                                            {{ Array.isArray(error) ? error[0] : error }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="space-y-6">
                            <!-- Vaccine Name -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-blue-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        Vaccine Name
                                        <span class="font-bold text-red-500">*</span>
                                    </span>
                                    <span class="text-xs font-normal text-gray-500">Enter the name of the vaccine</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="vaccineForm.name"
                                        type="text"
                                        class="w-full rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': vaccineForm.errors.name,
                                            'border-gray-300 dark:border-gray-600': !vaccineForm.errors.name,
                                        }"
                                        placeholder="e.g., Lasota, Gumboro, HVT"
                                        required
                                    />
                                    <div v-if="vaccineForm.errors.name" class="absolute top-1/2 right-3 -translate-y-1/2 transform">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-red-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="vaccineForm.errors.name" class="flex items-center gap-1 text-sm text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ vaccineForm.errors.name }}
                                </div>
                            </div>

                            <!-- Vaccine Type -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-blue-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>
                                        Vaccine Type
                                        <span class="font-bold text-red-500">*</span>
                                    </span>
                                    <span class="text-xs font-normal text-gray-500">Select the type of vaccine</span>
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="vaccineForm.vaccine_type_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': vaccineForm.errors.vaccine_type_id,
                                            'border-gray-300 dark:border-gray-600': !vaccineForm.errors.vaccine_type_id,
                                        }"
                                        required
                                    >
                                        <option value="">Choose vaccine type...</option>
                                        <option v-for="type in props.vaccineTypes" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 transform">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                    <div v-if="vaccineForm.errors.vaccine_type_id" class="absolute top-1/2 right-10 -translate-y-1/2 transform">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-red-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="vaccineForm.errors.vaccine_type_id" class="flex items-center gap-1 text-sm text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ vaccineForm.errors.vaccine_type_id }}
                                </div>
                            </div>
                            <!-- Optional Fields Grid -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Applicator -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Applicator
                                            <span class="text-xs font-normal text-gray-500">(Optional)</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Method of application</span>
                                    </label>
                                    <input
                                        v-model="vaccineForm.applicator"
                                        type="text"
                                        class="w-full rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': vaccineForm.errors.applicator,
                                            'border-gray-300 dark:border-gray-600': !vaccineForm.errors.applicator,
                                        }"
                                        placeholder="e.g., Eye drop, Injection, Spray"
                                    />
                                    <div v-if="vaccineForm.errors.applicator" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ vaccineForm.errors.applicator }}
                                    </div>
                                </div>

                                <!-- Dose -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Dose
                                            <span class="text-xs font-normal text-gray-500">(Optional)</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Recommended dosage amount</span>
                                    </label>
                                    <input
                                        v-model="vaccineForm.dose"
                                        type="text"
                                        class="w-full rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': vaccineForm.errors.dose,
                                            'border-gray-300 dark:border-gray-600': !vaccineForm.errors.dose,
                                        }"
                                        placeholder="e.g., 0.5ml, 1 dose, 2 drops"
                                    />
                                    <div v-if="vaccineForm.errors.dose" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ vaccineForm.errors.dose }}
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-green-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        Notes
                                        <span class="text-xs font-normal text-gray-500">(Optional)</span>
                                    </span>
                                    <span class="text-xs font-normal text-gray-500">Additional information or special instructions</span>
                                </label>
                                <textarea
                                    v-model="vaccineForm.note"
                                    class="w-full resize-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{
                                        'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': vaccineForm.errors.note,
                                        'border-gray-300 dark:border-gray-600': !vaccineForm.errors.note,
                                    }"
                                    placeholder="Enter any additional notes or special instructions..."
                                    rows="4"
                                ></textarea>
                                <div v-if="vaccineForm.errors.note" class="flex items-center gap-1 text-sm text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ vaccineForm.errors.note }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Status Indicator -->
                        <div
                            class="mt-6 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20"
                        >
                            <div class="flex items-center gap-3 text-sm text-blue-700 dark:text-blue-300">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Required Information</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">Vaccine Name and Vaccine Type are mandatory fields</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700/50">
                        <div class="flex justify-end gap-3">
                            <button
                                class="rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-700 transition-all duration-200 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                @click="
                                    () => {
                                        vaccineForm.clearErrors();
                                        showVaccineModal = false;
                                    }
                                "
                                :disabled="vaccineForm.processing"
                            >
                                Cancel
                            </button>
                            <button
                                class="transform rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                @click="saveVaccine"
                                :disabled="vaccineForm.processing"
                            >
                                <span v-if="vaccineForm.processing" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Creating Vaccine...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Create Vaccine
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Vaccine Schedule Modal -->
            <div v-if="showEditScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div
                    class="max-h-[95vh] w-full max-w-6xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out dark:bg-gray-800"
                >
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-white/20 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold">Edit Vaccine Schedule</h2>
                                    <p class="text-sm text-blue-100">Update vaccination schedule information</p>
                                </div>
                            </div>
                            <button
                                @click="
                                    () => {
                                        editScheduleForm.clearErrors();
                                        showEditScheduleModal = false;
                                    }
                                "
                                class="rounded-lg p-2 transition-colors duration-200 hover:bg-white/20"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="max-h-[calc(95vh-140px)] overflow-y-auto p-6">
                        <!-- General Error Message -->
                        <div
                            v-if="editScheduleForm.hasErrors"
                            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-red-100 p-1 dark:bg-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-2 font-semibold">Please correct the following errors:</p>
                                    <div class="space-y-2">
                                        <ul class="mt-2 space-y-1 text-sm">
                                            <li v-for="(error, field) in editScheduleForm.errors" :key="field" class="flex items-center gap-2">
                                                <span class="h-1 w-1 rounded-full bg-red-500"></span>
                                                {{ Array.isArray(error) ? error[0] : error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Information -->
                        <div
                            class="mb-6 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20"
                        >
                            <div class="flex items-center gap-3 text-sm text-blue-700 dark:text-blue-300">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Update Required Information</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">
                                        All fields marked with <span class="font-bold text-red-500">*</span> are mandatory. You can add multiple
                                        vaccination stages for comprehensive scheduling.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information Section -->
                        <div class="mb-8">
                            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-blue-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                Basic Information
                            </h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <!-- Company -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                            Company
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Select the company</span>
                                    </label>
                                    <select
                                        v-model="editScheduleForm.company_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': editScheduleForm.errors.company_id,
                                            'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.company_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Company</option>
                                        <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                            {{ company.name }}
                                        </option>
                                    </select>
                                    <div v-if="editScheduleForm.errors.company_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ editScheduleForm.errors.company_id }}
                                    </div>
                                </div>

                                <!-- Project -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                                />
                                            </svg>
                                            Project
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Select the project</span>
                                    </label>
                                    <select
                                        v-model="editScheduleForm.project_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': editScheduleForm.errors.project_id,
                                            'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.project_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Project</option>
                                        <option v-for="project in filteredEditProjects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div v-if="editScheduleForm.errors.project_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ editScheduleForm.errors.project_id }}
                                    </div>
                                </div>

                                <!-- Flock -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                />
                                            </svg>
                                            Flock
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Select flock</span>
                                    </label>
                                    <div ref="editFlockDropdownRef" class="relative">
                                        <!-- Search Input -->
                                        <div class="relative">
                                            <input
                                                v-model="editFlockSearchQuery"
                                                @focus="showEditFlockDropdown = true"
                                                @input="
                                                    showEditFlockDropdown = true;
                                                    highlightedEditFlockIndex = -1;
                                                "
                                                @keydown="handleEditFlockKeydown"
                                                type="text"
                                                placeholder="Search flocks..."
                                                class="w-full rounded-xl border px-4 py-3 pr-12 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                :class="{
                                                    'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20':
                                                        editScheduleForm.errors.flock_id,
                                                    'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.flock_id,
                                                }"
                                                required
                                            />
                                            <button
                                                type="button"
                                                @click="toggleEditFlockDropdown"
                                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Dropdown -->
                                        <div
                                            v-if="showEditFlockDropdown"
                                            class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-700 dark:ring-gray-600"
                                        >
                                            <div class="max-h-60 overflow-y-auto">
                                                <div
                                                    v-if="searchableEditFlocks.length === 0"
                                                    class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    No flocks found
                                                </div>
                                                <button
                                                    v-for="(flock, index) in searchableEditFlocks"
                                                    :key="flock.id"
                                                    type="button"
                                                    @click="selectEditFlock(flock)"
                                                    :class="[
                                                        'w-full px-4 py-3 text-left text-sm transition-colors focus:outline-none',
                                                        index === highlightedEditFlockIndex
                                                            ? 'bg-blue-50 text-blue-900 dark:bg-blue-900/20 dark:text-blue-100'
                                                            : 'hover:bg-gray-50 dark:hover:bg-gray-600',
                                                    ]"
                                                >
                                                    {{ flock.name }} ({{ flock.code }})
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Clear button -->
                                        <button
                                            v-if="selectedEditFlock"
                                            type="button"
                                            @click="clearEditFlockSelection"
                                            class="absolute top-1/2 right-10 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="editScheduleForm.errors.flock_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ editScheduleForm.errors.flock_id }}
                                    </div>
                                </div>

                                <!-- Shed -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                            Shed
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Select the shed</span>
                                    </label>
                                    <select
                                        v-model="editScheduleForm.shed_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': editScheduleForm.errors.shed_id,
                                            'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.shed_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Shed</option>
                                        <option v-for="shed in props.sheds" :key="shed.id" :value="shed.id">
                                            {{ shed.name }}
                                        </option>
                                    </select>
                                    <div v-if="editScheduleForm.errors.shed_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ editScheduleForm.errors.shed_id }}
                                    </div>
                                </div>

                                <!-- Batch -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                />
                                            </svg>
                                            Batch
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Select batch</span>
                                    </label>
                                    <select
                                        v-model="editScheduleForm.batch_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': editScheduleForm.errors.batch_id,
                                            'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.batch_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Batch</option>
                                        <option v-for="batch in props.batches" :key="batch.id" :value="batch.id">
                                            {{ batch.name }}
                                        </option>
                                    </select>
                                    <div v-if="editScheduleForm.errors.batch_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ editScheduleForm.errors.batch_id }}
                                    </div>
                                </div>

                                <!-- Breed Type -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                                />
                                            </svg>
                                            Breed Type
                                            <span class="font-bold text-red-500">*</span>
                                        </span>
                                        <span class="text-xs font-normal text-gray-500">Select the breed type</span>
                                    </label>
                                    <select
                                        v-model="editScheduleForm.breed_type_id"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': editScheduleForm.errors.breed_type_id,
                                            'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.breed_type_id,
                                        }"
                                        required
                                    >
                                        <option value="">Select Breed Type</option>
                                        <option v-for="breedType in props.breedTypes" :key="breedType.id" :value="breedType.id">
                                            {{ breedType.name }}
                                        </option>
                                    </select>
                                    <div v-if="editScheduleForm.errors.breed_type_id" class="flex items-center gap-1 text-sm text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{ editScheduleForm.errors.breed_type_id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vaccination Stages Section -->
                        <div class="mb-8">
                            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                Vaccination Stages
                            </h3>

                            <div
                                v-for="(stage, index) in editScheduleForm.stages"
                                :key="index"
                                class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-600 dark:bg-gray-700/50"
                            >
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">Stage {{ index + 1 }}</h4>
                                    <button
                                        v-if="editScheduleForm.stages.length > 1"
                                        @click="removeEditStage(index)"
                                        class="rounded-lg p-2 text-red-500 transition-colors duration-200 hover:bg-red-100 dark:hover:bg-red-900/20"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <!-- Disease -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Disease <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input
                                                    v-model="editDiseaseSearchQueries[index]"
                                                    @focus="showEditDiseaseDropdowns[index] = true"
                                                    @input="
                                                        showEditDiseaseDropdowns[index] = true;
                                                        highlightedEditDiseaseIndex[index] = -1;
                                                    "
                                                    @keydown="handleEditDiseaseKeydown($event, index)"
                                                    type="text"
                                                    placeholder="Search diseases..."
                                                    class="w-full rounded-lg border px-3 py-2 pr-10 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                                    :class="{
                                                        'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (
                                                            editScheduleForm.errors as any
                                                        )[`stages.${index}.disease_id`],
                                                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[
                                                            `stages.${index}.disease_id`
                                                        ],
                                                    }"
                                                    required
                                                />
                                                <button
                                                    type="button"
                                                    @click="toggleEditDiseaseDropdown(index)"
                                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown -->
                                            <div
                                                v-if="showEditDiseaseDropdowns[index]"
                                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-600 dark:ring-gray-500"
                                            >
                                                <div class="max-h-48 overflow-y-auto">
                                                    <div
                                                        v-if="searchableEditDiseases(index).length === 0"
                                                        class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        No diseases found
                                                    </div>
                                                    <button
                                                        v-for="(disease, diseaseIndex) in searchableEditDiseases(index)"
                                                        :key="disease.id"
                                                        type="button"
                                                        @click="selectEditDisease(index, disease)"
                                                        :class="[
                                                            'w-full px-3 py-2 text-left text-sm transition-colors focus:outline-none',
                                                            diseaseIndex === highlightedEditDiseaseIndex[index]
                                                                ? 'bg-green-50 text-green-900 dark:bg-green-900/20 dark:text-green-100'
                                                                : 'hover:bg-gray-50 dark:hover:bg-gray-500',
                                                        ]"
                                                    >
                                                        {{ disease.name }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Clear button -->
                                            <button
                                                v-if="selectedEditDiseases[index]"
                                                type="button"
                                                @click="clearEditDiseaseSelection(index)"
                                                class="absolute top-1/2 right-8 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-if="(editScheduleForm.errors as any)[`stages.${index}.disease_id`]" class="text-sm text-red-500">
                                            {{ (editScheduleForm.errors as any)[`stages.${index}.disease_id`] }}
                                        </div>
                                    </div>

                                    <!-- Vaccine -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Vaccine <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <!-- Search Input -->
                                            <div class="relative">
                                                <input
                                                    v-model="editVaccineSearchQueries[index]"
                                                    @focus="showEditVaccineDropdowns[index] = true"
                                                    @input="
                                                        showEditVaccineDropdowns[index] = true;
                                                        highlightedEditVaccineIndex[index] = -1;
                                                    "
                                                    @keydown="handleEditVaccineKeydown($event, index)"
                                                    type="text"
                                                    placeholder="Search vaccines..."
                                                    class="w-full rounded-lg border px-3 py-2 pr-10 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                                    :class="{
                                                        'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (
                                                            editScheduleForm.errors as any
                                                        )[`stages.${index}.vaccine_id`],
                                                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[
                                                            `stages.${index}.vaccine_id`
                                                        ],
                                                    }"
                                                    required
                                                />
                                                <button
                                                    type="button"
                                                    @click="toggleEditVaccineDropdown(index)"
                                                    class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown -->
                                            <div
                                                v-if="showEditVaccineDropdowns[index]"
                                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg bg-white shadow-xl ring-1 ring-gray-200 dark:bg-gray-600 dark:ring-gray-500"
                                            >
                                                <div class="max-h-48 overflow-y-auto">
                                                    <div
                                                        v-if="searchableEditVaccines(index).length === 0"
                                                        class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        No vaccines found
                                                    </div>
                                                    <button
                                                        v-for="(vaccine, vaccineIndex) in searchableEditVaccines(index)"
                                                        :key="vaccine.id"
                                                        type="button"
                                                        @click="selectEditVaccine(index, vaccine)"
                                                        :class="[
                                                            'w-full px-3 py-2 text-left text-sm transition-colors focus:outline-none',
                                                            vaccineIndex === highlightedEditVaccineIndex[index]
                                                                ? 'bg-green-50 text-green-900 dark:bg-green-900/20 dark:text-green-100'
                                                                : 'hover:bg-gray-50 dark:hover:bg-gray-500',
                                                        ]"
                                                    >
                                                        {{ vaccine.name }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Clear button -->
                                            <button
                                                v-if="selectedEditVaccines[index]"
                                                type="button"
                                                @click="clearEditVaccineSelection(index)"
                                                class="absolute top-1/2 right-8 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-if="(editScheduleForm.errors as any)[`stages.${index}.vaccine_id`]" class="text-sm text-red-500">
                                            {{ (editScheduleForm.errors as any)[`stages.${index}.vaccine_id`] }}
                                        </div>
                                    </div>

                                    <!-- Age -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            Age <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="stage.age"
                                            type="text"
                                            class="w-full rounded-lg border px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                            :class="{
                                                'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (editScheduleForm.errors as any)[
                                                    `stages.${index}.age`
                                                ],
                                                'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[`stages.${index}.age`],
                                            }"
                                            placeholder="e.g., 7 days, 2 weeks"
                                            required
                                        />
                                        <div v-if="(editScheduleForm.errors as any)[`stages.${index}.age`]" class="text-sm text-red-500">
                                            {{ (editScheduleForm.errors as any)[`stages.${index}.age`] }}
                                        </div>
                                    </div>

                                    <!-- Vaccination Date -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Vaccination Date <span class="font-bold text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="stage.vaccination_date"
                                                type="date"
                                                class="w-full rounded-xl border px-4 py-3 pr-4 pl-12 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white dark:placeholder-gray-400"
                                                :class="{
                                                    'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (
                                                        editScheduleForm.errors as any
                                                    )[`stages.${index}.vaccination_date`],
                                                    'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[
                                                        `stages.${index}.vaccination_date`
                                                    ],
                                                }"
                                                required
                                            />
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-gray-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <div
                                            v-if="(editScheduleForm.errors as any)[`stages.${index}.vaccination_date`]"
                                            class="flex items-center gap-1 text-sm text-red-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            {{ (editScheduleForm.errors as any)[`stages.${index}.vaccination_date`] }}
                                        </div>
                                    </div>

                                    <!-- Next Vaccination Date -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Next Vaccination Date
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(Optional)</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="stage.next_vaccination_date"
                                                type="date"
                                                class="w-full rounded-xl border px-4 py-3 pr-4 pl-12 text-gray-900 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white dark:placeholder-gray-400"
                                                :class="{
                                                    'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': (
                                                        editScheduleForm.errors as any
                                                    )[`stages.${index}.next_vaccination_date`],
                                                    'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[
                                                        `stages.${index}.next_vaccination_date`
                                                    ],
                                                }"
                                            />
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-gray-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <div
                                            v-if="(editScheduleForm.errors as any)[`stages.${index}.next_vaccination_date`]"
                                            class="flex items-center gap-1 text-sm text-red-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            {{ (editScheduleForm.errors as any)[`stages.${index}.next_vaccination_date`] }}
                                        </div>
                                    </div>

                                    <!-- Administered By -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300"> Administered By </label>
                                        <input
                                            v-model="stage.administered_by"
                                            type="text"
                                            class="w-full rounded-lg border px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                            placeholder="Enter name of person who administered"
                                        />
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mt-4 space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300"> Notes </label>
                                    <textarea
                                        v-model="stage.notes"
                                        class="w-full resize-none rounded-lg border px-3 py-2 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-500 dark:bg-gray-600 dark:text-white"
                                        placeholder="Additional notes for this vaccination stage..."
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Add Stage Button -->
                            <button
                                @click="addEditStage"
                                class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 font-medium text-white transition-all duration-200 hover:bg-green-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Another Stage
                            </button>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700/50">
                        <div class="flex justify-end gap-3">
                            <button
                                class="rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-700 transition-all duration-200 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                @click="
                                    () => {
                                        editScheduleForm.clearErrors();
                                        showEditScheduleModal = false;
                                    }
                                "
                                :disabled="editScheduleForm.processing"
                            >
                                Cancel
                            </button>
                            <button
                                class="transform rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                @click="updateSchedule"
                                :disabled="editScheduleForm.processing"
                            >
                                <span v-if="editScheduleForm.processing" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Updating Schedule...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Schedule
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Vaccine Routing Modal -->
            <div v-if="showRoutingModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div
                    class="max-h-[90vh] w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out dark:bg-gray-800"
                >
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-white/20 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold">Add New Vaccine Routing</h2>
                                    <p class="text-sm text-purple-100">Create a new vaccine routing configuration</p>
                                </div>
                            </div>
                            <button
                                @click="
                                    () => {
                                        routingForm.clearErrors();
                                        showRoutingModal = false;
                                    }
                                "
                                class="rounded-lg p-2 transition-colors duration-200 hover:bg-white/20"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="max-h-[calc(90vh-140px)] overflow-y-auto p-6">
                        <!-- General Error Message -->
                        <div
                            v-if="routingForm.hasErrors"
                            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-red-100 p-1 dark:bg-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Please correct the following errors:</p>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        <li v-for="(error, field) in routingForm.errors" :key="field" class="flex items-center gap-2">
                                            <span class="h-1 w-1 rounded-full bg-red-500"></span>
                                            {{ Array.isArray(error) ? error[0] : error }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Form Information -->
                        <div
                            class="mb-6 rounded-xl border border-purple-200 bg-gradient-to-r from-purple-50 to-indigo-50 p-4 dark:border-purple-800 dark:from-purple-900/20 dark:to-indigo-900/20"
                        >
                            <div class="flex items-center gap-3 text-sm text-purple-700 dark:text-purple-300">
                                <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Required Information</p>
                                    <p class="text-xs text-purple-600 dark:text-purple-400">
                                        Route name and status are required. Description is optional.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="space-y-6">
                            <!-- Route Name -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-purple-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                        Route Name
                                        <span class="font-bold text-red-500">*</span>
                                    </span>
                                    <span class="text-xs font-normal text-gray-500">Enter a unique name for the vaccine routing</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="routingForm.name"
                                        type="text"
                                        class="w-full rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': routingForm.errors.name,
                                            'border-gray-300 dark:border-gray-600': !routingForm.errors.name,
                                        }"
                                        placeholder="e.g., Primary Route, Secondary Route, Emergency Route"
                                        required
                                    />
                                    <div v-if="routingForm.errors.name" class="absolute top-1/2 right-3 -translate-y-1/2 transform">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-red-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="routingForm.errors.name" class="flex items-center gap-1 text-sm text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ routingForm.errors.name }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-green-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        Description
                                        <span class="text-xs font-normal text-gray-500">(Optional)</span>
                                    </span>
                                    <span class="text-xs font-normal text-gray-500">Provide additional details about this routing</span>
                                </label>
                                <textarea
                                    v-model="routingForm.description"
                                    class="w-full resize-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{
                                        'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': routingForm.errors.description,
                                        'border-gray-300 dark:border-gray-600': !routingForm.errors.description,
                                    }"
                                    placeholder="Enter a detailed description of this vaccine routing configuration..."
                                    rows="4"
                                ></textarea>
                                <div v-if="routingForm.errors.description" class="flex items-center gap-1 text-sm text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ routingForm.errors.description }}
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-blue-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        Status
                                        <span class="font-bold text-red-500">*</span>
                                    </span>
                                    <span class="text-xs font-normal text-gray-500">Select the status for this vaccine routing</span>
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="routingForm.status"
                                        class="w-full cursor-pointer appearance-none rounded-xl border px-4 py-3 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{
                                            'border-red-500 bg-red-50 focus:ring-red-500 dark:bg-red-900/20': routingForm.errors.status,
                                            'border-gray-300 dark:border-gray-600': !routingForm.errors.status,
                                        }"
                                        required
                                    >
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                    <div v-if="routingForm.errors.status" class="absolute top-1/2 right-8 -translate-y-1/2 transform">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-red-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="routingForm.errors.status" class="flex items-center gap-1 text-sm text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    {{ routingForm.errors.status }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700/50">
                        <div class="flex justify-end gap-3">
                            <button
                                class="rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-700 transition-all duration-200 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                @click="
                                    () => {
                                        routingForm.clearErrors();
                                        showRoutingModal = false;
                                    }
                                "
                                :disabled="routingForm.processing"
                            >
                                Cancel
                            </button>
                            <button
                                class="transform rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-3 font-medium text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-purple-700 hover:to-purple-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                @click="saveRouting"
                                :disabled="routingForm.processing"
                            >
                                <span v-if="routingForm.processing" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Creating Routing...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Create Routing
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
