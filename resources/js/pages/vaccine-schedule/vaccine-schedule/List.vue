<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from "vue";

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
  vaccineSchedules: Array<{
    id: number;
    company_id: number;
    company_name: string;
    job_no: string;
    project_id: number;
    project_name: string;
    project_code: string;
    flock_no: string;
    shed_id: number;
    shed_name: string;
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
  // Populate edit form with schedule data
  editScheduleForm.id = schedule.id;
  editScheduleForm.company_id = schedule.company_id.toString();
  editScheduleForm.job_no = schedule.job_no;
  editScheduleForm.project_id = schedule.project_id.toString();
  editScheduleForm.flock_no = schedule.flock_no;
  editScheduleForm.shed_id = schedule.shed_id.toString();
  editScheduleForm.batch_no = schedule.batch_no;
  editScheduleForm.breed_type_id = schedule.breed_type_id.toString();
  
  // Populate stages data
  editScheduleForm.stages = schedule.details.map((detail: any) => ({
    id: detail.id,
    disease_id: detail.disease_id.toString(),
    vaccine_id: detail.vaccine_id.toString(),
    age: detail.age,
    vaccination_date: detail.vaccination_date,
    next_vaccination_date: detail.next_vaccination_date || "",
    notes: detail.notes || "",
    administered_by: detail.administered_by || ""
  }));
  
  // If no stages, add one empty stage
  if (editScheduleForm.stages.length === 0) {
    editScheduleForm.stages = [{
      id: null,
      disease_id: "",
      vaccine_id: "",
      age: "",
      vaccination_date: "",
      next_vaccination_date: "",
      notes: "",
      administered_by: ""
    }];
  }
  
  editScheduleForm.clearErrors();
  showEditScheduleModal.value = true;
};

// Add stage for edit form
const addEditStage = () => {
  editScheduleForm.stages.push({
    id: null,
    disease_id: "",
    vaccine_id: "",
    age: "",
    vaccination_date: "",
    next_vaccination_date: "",
    notes: "",
    administered_by: ""
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
    Object.keys(clientErrors).forEach(key => {
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
    }
  });
};

// Client-side validation for edit schedule form
const validateEditScheduleForm = () => {
  const errors: Record<string, string> = {};
  
  // Basic information validation
  if (!editScheduleForm.company_id) { errors.company_id = 'Please select a company.'; }
  if (!editScheduleForm.job_no || editScheduleForm.job_no.trim() === '') { errors.job_no = 'Job number is required.'; }
  if (!editScheduleForm.project_id) { errors.project_id = 'Please select a project.'; }
  if (!editScheduleForm.flock_no || editScheduleForm.flock_no.trim() === '') { errors.flock_no = 'Flock number is required.'; }
  if (!editScheduleForm.shed_id) { errors.shed_id = 'Please select a shed.'; }
  if (!editScheduleForm.batch_no || editScheduleForm.batch_no.trim() === '') { errors.batch_no = 'Batch number is required.'; }
  if (!editScheduleForm.breed_type_id) { errors.breed_type_id = 'Please select a breed type.'; }
  
  // Stages validation
  if (!editScheduleForm.stages || editScheduleForm.stages.length === 0) {
    errors.stages = 'At least one vaccination stage is required.';
  } else {
    editScheduleForm.stages.forEach((stage: any, index: number) => {
      if (!stage.disease_id) { errors[`stages.${index}.disease_id`] = 'Please select a disease for this stage.'; }
      if (!stage.vaccine_id) { errors[`stages.${index}.vaccine_id`] = 'Please select a vaccine for this stage.'; }
      if (!stage.age || stage.age.trim() === '') { errors[`stages.${index}.age`] = 'Age is required for this stage.'; }
      if (!stage.vaccination_date) { errors[`stages.${index}.vaccination_date`] = 'Vaccination date is required for this stage.'; }
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
    Object.keys(clientErrors).forEach(key => {
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
    }
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
  company_id: "",
  job_no: "",
  project_id: "",
  flock_no: "",
  shed_id: "",
  batch_no: "",
  breed_type_id: "",
  stages: [
    { 
      disease_id: "", 
      vaccine_id: "", 
      age: "", 
      vaccination_date: "", 
      next_vaccination_date: "",
      notes: "",
      administered_by: ""
    }
  ]
});

// Edit form data
const editScheduleForm = useForm({
  id: null,
  company_id: "",
  job_no: "",
  project_id: "",
  flock_no: "",
  shed_id: "",
  batch_no: "",
  breed_type_id: "",
  stages: [
    { 
      id: null,
      disease_id: "", 
      vaccine_id: "", 
      age: "", 
      vaccination_date: "", 
      next_vaccination_date: "",
      notes: "",
      administered_by: ""
    }
  ]
});

// Form data (vaccine) using Inertia useForm
const vaccineForm = useForm({
  vaccine_type_id: "",
  name: "",
  applicator: "",
  dose: "",
  note: "",
  status: 1,
});

// Form data (routing) using Inertia useForm
const routingForm = useForm({
  name: "",
  description: "",
  status: "active",
});

// Sample vaccination history data (in real app, this would come from backend)
const vaccinationHistory = ref([
  {
    id: 1,
    project: "PBL",
    flock: "000001",
    batch: "Batch A",
    breed: "EP",
    disease: "New Castle",
    vaccine: "Lasota",
    age: "7",
    vaccinationDate: "2024-01-15",
    status: "Completed",
    administeredBy: "Dr. Smith",
    notes: "All birds vaccinated successfully"
  },
  {
    id: 2,
    project: "PCL",
    flock: "000002",
    batch: "Batch B",
    breed: "IR",
    disease: "IBD",
    vaccine: "Gumboro",
    age: "14",
    vaccinationDate: "2024-01-20",
    status: "Completed",
    administeredBy: "Dr. Johnson",
    notes: "No adverse reactions observed"
  },
  {
    id: 3,
    project: "PBL",
    flock: "000003",
    batch: "Batch C",
    breed: "EP",
    disease: "Marek",
    vaccine: "HVT",
    age: "1",
    vaccinationDate: "2024-01-25",
    status: "Completed",
    administeredBy: "Dr. Brown",
    notes: "Day-old vaccination completed"
  },
  {
    id: 4,
    project: "PCL",
    flock: "000001",
    batch: "Batch A",
    breed: "IR",
    disease: "New Castle",
    vaccine: "Lasota",
    age: "21",
    vaccinationDate: "2024-02-01",
    status: "Completed",
    administeredBy: "Dr. Smith",
    notes: "Booster vaccination given"
  }
]);

// Add stage
const addStage = () => {
  scheduleForm.stages.push({ 
    disease_id: "", 
    vaccine_id: "", 
    age: "", 
    vaccination_date: "", 
    next_vaccination_date: "",
    notes: "",
    administered_by: ""
  });
};

// Remove stage
const removeStage = (index: number) => {
  scheduleForm.stages.splice(index, 1);
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
    Object.keys(clientErrors).forEach(key => {
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
          disease_id: "", 
          vaccine_id: "", 
          age: "", 
          vaccination_date: "", 
          next_vaccination_date: "",
          notes: "",
          administered_by: ""
        }
      ];
  showScheduleModal.value = false;
    },
    onError: (errors) => {
      console.error('Schedule creation failed:', errors);
      // Modal stays open to display validation errors
    }
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

// Error categorization for vaccine schedule form
const basicInfoFields = ['company_id', 'job_no', 'project_id', 'flock_no', 'shed_id', 'batch_no', 'breed_type_id'];

const basicInfoErrors = computed(() => {
  const errors: Record<string, any> = {};
  Object.keys(scheduleForm.errors).forEach(key => {
    if (basicInfoFields.includes(key)) {
      errors[key] = (scheduleForm.errors as any)[key];
    }
  });
  return errors;
});

const hasBasicInfoErrors = computed(() => Object.keys(basicInfoErrors.value).length > 0);

const stagesErrors = computed(() => {
  const errors: Record<string, any> = {};
  Object.keys(scheduleForm.errors).forEach(key => {
    if (key.startsWith('stages')) {
      errors[key] = (scheduleForm.errors as any)[key];
    }
  });
  return errors;
});

const hasStagesErrors = computed(() => Object.keys(stagesErrors.value).length > 0);

const otherErrors = computed(() => {
  const errors: Record<string, any> = {};
  Object.keys(scheduleForm.errors).forEach(key => {
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
  
  if (!scheduleForm.job_no || scheduleForm.job_no.trim() === '') {
    errors.job_no = 'Job number is required.';
  }
  
  if (!scheduleForm.project_id) {
    errors.project_id = 'Please select a project.';
  }
  
  if (!scheduleForm.flock_no || scheduleForm.flock_no.trim() === '') {
    errors.flock_no = 'Flock number is required.';
  }
  
  if (!scheduleForm.shed_id) {
    errors.shed_id = 'Please select a shed.';
  }
  
  if (!scheduleForm.batch_no || scheduleForm.batch_no.trim() === '') {
    errors.batch_no = 'Batch number is required.';
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
    Object.keys(clientErrors).forEach(key => {
      vaccineForm.setError(key as keyof typeof vaccineForm.data, clientErrors[key]);
    });
    return; // Don't submit if client validation fails
  }
  
  vaccineForm.post('/vaccine', {
    onSuccess: () => {
  // Add vaccine to options dynamically
      vaccineOptions.push(vaccineForm.name);

      // Reset form
      vaccineForm.reset();
      vaccineForm.status = 1; // Reset status to active
  showVaccineModal.value = false;
    },
    onError: (errors) => {
      // Keep modal open and show validation errors
      console.error('Vaccine creation failed:', errors);
      // Modal stays open to display validation errors
    }
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
          @click="openVaccineModal"
          class="flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
        >
          <div class="p-1 bg-white/20 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          </div>
          Add New Vaccine
        </button>

        <!-- Vaccination Schedule -->
        <button
          @click="openScheduleModal"
          class="flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Vaccination Schedule
        </button>

        <button
            @click="openRoutingModal"
            class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-6-6V7h6v4m-6 0H5l7-7 7 7h-4" />
            </svg>
            Vaccine Routing
        </button>

        <!-- View Used Vaccine -->
        <button
            @click="showLastVaccineModal = true"
            class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            View Used Vaccine
        </button>


      </div>

      <!-- Vaccine Schedules Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Vaccination Management</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage vaccination schedules and their details</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  S/L No
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Company
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Project
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Flock Details
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Breed Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Created
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Actions
                </th>
            </tr>
          </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <template v-for="schedule in props.vaccineSchedules" :key="schedule.id">
                <!-- Main Schedule Row -->
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" @click="toggleRow(schedule.id)">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <button class="mr-2 p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
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
                        {{ schedule.id }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.company_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.details.length }} stage(s)</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.project_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.project_code }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">Flock: {{ schedule.flock_no }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Batch: {{ schedule.batch_no }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Shed: {{ schedule.shed_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.breed_type_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="schedule.status === 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                      {{ schedule.status === 1 ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(schedule.created_at).toLocaleDateString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center gap-2">
                      <button 
                        @click.stop="editSchedule(schedule)"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200"
                        title="Edit Schedule"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button 
                        @click.stop="deleteSchedule(schedule.id)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                        title="Delete Schedule"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
              </td>
            </tr>

                <!-- Expanded Details Row -->
                <tr v-if="isRowExpanded(schedule.id)" class="bg-gray-50 dark:bg-gray-700/50">
                  <td colspan="8" class="px-6 py-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
                      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Vaccination Stages</h4>
                      </div>
                      <div class="p-4">
                        <div v-if="schedule.details.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                          No vaccination stages found
                        </div>
                        <div v-else class="space-y-4">
                          <div v-for="(detail, index) in schedule.details" :key="detail.id" 
                               class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                              <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stage {{ index + 1 }}</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                  {{ detail.disease_name }} → {{ detail.vaccine_name }}
                                </div>
                              </div>
                              <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Age</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ detail.age }}</div>
                              </div>
                              <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vaccination Date</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                  {{ new Date(detail.vaccination_date).toLocaleDateString() }}
                                </div>
                              </div>
                              <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Next Date</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                  {{ detail.next_vaccination_date ? new Date(detail.next_vaccination_date).toLocaleDateString() : 'N/A' }}
                                </div>
                              </div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                              <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</label>
                                <div class="mt-1">
                                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="{
                                          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': detail.status === 'pending',
                                          'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': detail.status === 'completed',
                                          'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': detail.status === 'cancelled'
                                        }">
                                    {{ detail.status.charAt(0).toUpperCase() + detail.status.slice(1) }}
                                  </span>
                                </div>
                              </div>
                              <div v-if="detail.administered_by">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Administered By</label>
                                <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ detail.administered_by }}</div>
                              </div>
                            </div>
                            <div v-if="detail.notes" class="mt-3">
                              <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Notes</label>
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
      <div v-if="showScheduleModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden transform transition-all duration-300 ease-out">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
            <div>
                  <h2 class="text-xl font-bold">Add Vaccine Schedule</h2>
                  <p class="text-green-100 text-sm">Create a comprehensive vaccination schedule for your flock</p>
                </div>
              </div>
              <button 
                @click="() => { scheduleForm.clearErrors(); showScheduleModal = false; }"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors duration-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]">
                        <!-- General Error Message -->
            <div v-if="scheduleForm.hasErrors" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl">
              <div class="flex items-start gap-3">
                <div class="p-1 bg-red-100 dark:bg-red-800 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="font-semibold mb-2">Please correct the following errors:</p>
                  <div class="space-y-2">
                    <!-- Basic Information Errors -->
                    <div v-if="hasBasicInfoErrors" class="border-l-2 border-red-300 pl-3">
                      <p class="text-sm font-medium text-red-600 dark:text-red-400 mb-1">Basic Information:</p>
                      <ul class="space-y-1 text-sm">
                        <li v-for="(error, field) in basicInfoErrors" :key="field" class="flex items-center gap-2">
                          <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                          {{ Array.isArray(error) ? error[0] : error }}
                        </li>
                      </ul>
                    </div>
                    
                    <!-- Stages Errors -->
                    <div v-if="hasStagesErrors" class="border-l-2 border-red-300 pl-3">
                      <p class="text-sm font-medium text-red-600 dark:text-red-400 mb-1">Vaccination Stages:</p>
                      <ul class="space-y-1 text-sm">
                        <li v-for="(error, field) in stagesErrors" :key="field" class="flex items-center gap-2">
                          <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                          {{ Array.isArray(error) ? error[0] : error }}
                        </li>
                      </ul>
                    </div>
                    
                    <!-- Other Errors -->
                    <div v-if="hasOtherErrors" class="border-l-2 border-red-300 pl-3">
                      <p class="text-sm font-medium text-red-600 dark:text-red-400 mb-1">Other Issues:</p>
                      <ul class="space-y-1 text-sm">
                        <li v-for="(error, field) in otherErrors" :key="field" class="flex items-center gap-2">
                          <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                          {{ Array.isArray(error) ? error[0] : error }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Information -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
              <div class="flex items-center gap-3 text-sm text-blue-700 dark:text-blue-300">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
            <div>
                  <p class="font-semibold">Required Information</p>
                  <p class="text-xs text-blue-600 dark:text-blue-400">All fields marked with <span class="text-red-500 font-bold">*</span> are mandatory. You can add multiple vaccination stages for comprehensive scheduling.</p>
                </div>
              </div>
            </div>

            <!-- Basic Information Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Basic Information
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Company -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      Company
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <select 
                    v-model="scheduleForm.company_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.company_id, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.company_id 
                    }"
                    required
                  >
                    <option value="">Select Company</option>
                    <option v-for="company in props.companies" :key="company.id" :value="company.id">
                      {{ company.name }}
                    </option>
              </select>
                  <div v-if="scheduleForm.errors.company_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.company_id }}
                  </div>
            </div>

                <!-- Job Number -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                      </svg>
                      Job Number
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <input 
                    v-model="scheduleForm.job_no" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.job_no, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.job_no 
                    }"
                    placeholder="e.g., JOB001"
                    required
                  />
                  <div v-if="scheduleForm.errors.job_no" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.job_no }}
                  </div>
                </div>

            <!-- Project -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                      </svg>
                      Project
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <select 
                    v-model="scheduleForm.project_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.project_id, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.project_id 
                    }"
                    required
                  >
                <option value="">Select Project</option>
                    <option v-for="project in props.projects" :key="project.id" :value="project.id">
                      {{ project.name }} ({{ project.code }})
                    </option>
              </select>
                  <div v-if="scheduleForm.errors.project_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.project_id }}
                  </div>
            </div>

                <!-- Flock Number -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      Flock Number
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <input 
                    v-model="scheduleForm.flock_no" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.flock_no, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.flock_no 
                    }"
                    placeholder="e.g., FL001"
                    required
                  />
                  <div v-if="scheduleForm.errors.flock_no" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.flock_no }}
                  </div>
            </div>

                <!-- Shed -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                      </svg>
                      Shed
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <select 
                    v-model="scheduleForm.shed_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.shed_id, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.shed_id 
                    }"
                    required
                  >
                    <option value="">Select Shed</option>
                    <option v-for="shed in props.sheds" :key="shed.id" :value="shed.id">
                      {{ shed.name }}
                    </option>
              </select>
                  <div v-if="scheduleForm.errors.shed_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.shed_id }}
                  </div>
            </div>

                <!-- Batch Number -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                      </svg>
                      Batch Number
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <input 
                    v-model="scheduleForm.batch_no" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.batch_no, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.batch_no 
                    }"
                    placeholder="e.g., BATCH001"
                    required
                  />
                  <div v-if="scheduleForm.errors.batch_no" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.batch_no }}
                  </div>
            </div>

                <!-- Breed Type -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                      Breed Type
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                  </label>
                  <select 
                    v-model="scheduleForm.breed_type_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': scheduleForm.errors.breed_type_id, 
                      'border-gray-300 dark:border-gray-600': !scheduleForm.errors.breed_type_id 
                    }"
                    required
                  >
                <option value="">Select Breed Type</option>
                    <option v-for="breedType in props.breedTypes" :key="breedType.id" :value="breedType.id">
                      {{ breedType.name }}
                    </option>
              </select>
                  <div v-if="scheduleForm.errors.breed_type_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ scheduleForm.errors.breed_type_id }}
                  </div>
                </div>
            </div>
          </div>

            <!-- Vaccination Stages Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Vaccination Stages
              </h3>
              
              <div 
                v-for="(stage, index) in scheduleForm.stages"
              :key="index"
                class="mb-6 p-6 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50"
              >
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">
                    Stage {{ index + 1 }}
                  </h4>
                  <button
                    v-if="scheduleForm.stages.length > 1"
                    @click="removeStage(index)"
                    class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Disease -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Disease <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select 
                      v-model="stage.disease_id" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (scheduleForm.errors as any)[`stages.${index}.disease_id`], 
                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[`stages.${index}.disease_id`] 
                      }"
                      required
                    >
                  <option value="">Select Disease</option>
                      <option v-for="disease in props.diseases" :key="disease.id" :value="disease.id">
                        {{ disease.name }}
                      </option>
                </select>
                    <div v-if="(scheduleForm.errors as any)[`stages.${index}.disease_id`]" class="text-red-500 text-sm">
                      {{ (scheduleForm.errors as any)[`stages.${index}.disease_id`] }}
                    </div>
              </div>

              <!-- Vaccine -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Vaccine <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select 
                      v-model="stage.vaccine_id" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (scheduleForm.errors as any)[`stages.${index}.vaccine_id`], 
                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[`stages.${index}.vaccine_id`] 
                      }"
                      required
                    >
                  <option value="">Select Vaccine</option>
                      <option v-for="vaccine in props.vaccines" :key="vaccine.id" :value="vaccine.id">
                        {{ vaccine.name }}
                      </option>
                </select>
                    <div v-if="(scheduleForm.errors as any)[`stages.${index}.vaccine_id`]" class="text-red-500 text-sm">
                      {{ (scheduleForm.errors as any)[`stages.${index}.vaccine_id`] }}
                    </div>
              </div>

              <!-- Age -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Age <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input 
                      v-model="stage.age" 
                      type="text" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (scheduleForm.errors as any)[`stages.${index}.age`], 
                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[`stages.${index}.age`] 
                      }"
                      placeholder="e.g., 7 days, 2 weeks"
                      required
                    />
                    <div v-if="(scheduleForm.errors as any)[`stages.${index}.age`]" class="text-red-500 text-sm">
                      {{ (scheduleForm.errors as any)[`stages.${index}.age`] }}
                    </div>
                  </div>

                  <!-- Vaccination Date -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Vaccination Date <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input 
                      v-model="stage.vaccination_date" 
                      type="date" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (scheduleForm.errors as any)[`stages.${index}.vaccination_date`], 
                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[`stages.${index}.vaccination_date`] 
                      }"
                      required
                    />
                    <div v-if="(scheduleForm.errors as any)[`stages.${index}.vaccination_date`]" class="text-red-500 text-sm">
                      {{ (scheduleForm.errors as any)[`stages.${index}.vaccination_date`] }}
                    </div>
              </div>

              <!-- Next Vaccination Date -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Next Vaccination Date
                    </label>
                    <input 
                      v-model="stage.next_vaccination_date" 
                      type="date" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (scheduleForm.errors as any)[`stages.${index}.next_vaccination_date`], 
                        'border-gray-300 dark:border-gray-500': !(scheduleForm.errors as any)[`stages.${index}.next_vaccination_date`] 
                      }"
                    />
                    <div v-if="(scheduleForm.errors as any)[`stages.${index}.next_vaccination_date`]" class="text-red-500 text-sm">
                      {{ (scheduleForm.errors as any)[`stages.${index}.next_vaccination_date`] }}
                    </div>
              </div>

                  <!-- Administered By -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Administered By
                    </label>
                    <input 
                      v-model="stage.administered_by" 
                      type="text" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      placeholder="e.g., Dr. Smith"
                    />
              </div>
            </div>

                <!-- Notes -->
                <div class="mt-4 space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Notes
                  </label>
                  <textarea 
                    v-model="stage.notes" 
                    class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white resize-none"
                    placeholder="Additional notes for this vaccination stage..."
                    rows="2"
                  ></textarea>
                </div>
              </div>

              <!-- Add Stage Button -->
            <button
              @click="addStage"
                class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 font-medium"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Another Stage
            </button>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex justify-end gap-3">
            <button
                class="px-6 py-3 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition-all duration-200 font-medium" 
                @click="() => { scheduleForm.clearErrors(); showScheduleModal = false; }"
                :disabled="scheduleForm.processing"
            >
              Cancel
            </button>
            <button
                class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
              @click="saveSchedule"
                :disabled="scheduleForm.processing"
              >
                <span v-if="scheduleForm.processing" class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
      <div v-if="showVaccineModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all duration-300 ease-out">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
            </div>
            <div>
                  <h2 class="text-xl font-bold">Add New Vaccine</h2>
                  <p class="text-blue-100 text-sm">Create a new vaccine entry for your inventory</p>
                </div>
              </div>
              <button 
                @click="() => { vaccineForm.clearErrors(); showVaccineModal = false; }"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors duration-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <!-- General Error Message -->
            <div v-if="vaccineForm.hasErrors" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl">
              <div class="flex items-start gap-3">
                <div class="p-1 bg-red-100 dark:bg-red-800 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
            </div>
            <div>
                  <p class="font-semibold">Please correct the following errors:</p>
                  <ul class="mt-2 space-y-1 text-sm">
                    <li v-for="(error, field) in vaccineForm.errors" :key="field" class="flex items-center gap-2">
                      <span class="w-1 h-1 bg-red-500 rounded-full"></span>
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Vaccine Name
                    <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-xs text-gray-500 font-normal">Enter the name of the vaccine</span>
                </label>
                <div class="relative">
                  <input 
                    v-model="vaccineForm.name" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': vaccineForm.errors.name, 
                      'border-gray-300 dark:border-gray-600': !vaccineForm.errors.name 
                    }"
                    placeholder="e.g., Lasota, Gumboro, HVT"
                    required
                  />
                  <div v-if="vaccineForm.errors.name" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div v-if="vaccineForm.errors.name" class="text-red-500 text-sm flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ vaccineForm.errors.name }}
                </div>
              </div>

              <!-- Vaccine Type -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Vaccine Type
                    <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-xs text-gray-500 font-normal">Select the type of vaccine</span>
                </label>
                <div class="relative">
                  <select 
                    v-model="vaccineForm.vaccine_type_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': vaccineForm.errors.vaccine_type_id, 
                      'border-gray-300 dark:border-gray-600': !vaccineForm.errors.vaccine_type_id 
                    }"
                    required
                  >
                    <option value="">Choose vaccine type...</option>
                    <option v-for="type in props.vaccineTypes" :key="type.id" :value="type.id">
                      {{ type.name }}
                    </option>
              </select>
                  <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
            </div>
                  <div v-if="vaccineForm.errors.vaccine_type_id" class="absolute right-10 top-1/2 transform -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div v-if="vaccineForm.errors.vaccine_type_id" class="text-red-500 text-sm flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ vaccineForm.errors.vaccine_type_id }}
                </div>
              </div>
              <!-- Optional Fields Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Applicator -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Applicator
                      <span class="text-xs text-gray-500 font-normal">(Optional)</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Method of application</span>
                  </label>
                  <input 
                    v-model="vaccineForm.applicator" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': vaccineForm.errors.applicator, 
                      'border-gray-300 dark:border-gray-600': !vaccineForm.errors.applicator 
                    }"
                    placeholder="e.g., Eye drop, Injection, Spray"
                  />
                  <div v-if="vaccineForm.errors.applicator" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ vaccineForm.errors.applicator }}
                  </div>
                </div>

                <!-- Dose -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Dose
                      <span class="text-xs text-gray-500 font-normal">(Optional)</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Recommended dosage amount</span>
                  </label>
                  <input 
                    v-model="vaccineForm.dose" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': vaccineForm.errors.dose, 
                      'border-gray-300 dark:border-gray-600': !vaccineForm.errors.dose 
                    }"
                    placeholder="e.g., 0.5ml, 1 dose, 2 drops"
                  />
                  <div v-if="vaccineForm.errors.dose" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ vaccineForm.errors.dose }}
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Notes
                    <span class="text-xs text-gray-500 font-normal">(Optional)</span>
                  </span>
                  <span class="text-xs text-gray-500 font-normal">Additional information or special instructions</span>
                </label>
                <textarea 
                  v-model="vaccineForm.note" 
                  class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none"
                  :class="{ 
                    'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': vaccineForm.errors.note, 
                    'border-gray-300 dark:border-gray-600': !vaccineForm.errors.note 
                  }"
                  placeholder="Enter any additional notes or special instructions..."
                  rows="4"
                ></textarea>
                <div v-if="vaccineForm.errors.note" class="text-red-500 text-sm flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ vaccineForm.errors.note }}
                </div>
              </div>
            </div>

            <!-- Form Status Indicator -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
              <div class="flex items-center gap-3 text-sm text-blue-700 dark:text-blue-300">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
          <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex justify-end gap-3">
              <button 
                class="px-6 py-3 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition-all duration-200 font-medium" 
                @click="() => { vaccineForm.clearErrors(); showVaccineModal = false; }"
                :disabled="vaccineForm.processing"
              >
              Cancel
            </button>
              <button 
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
                @click="saveVaccine"
                :disabled="vaccineForm.processing"
              >
                <span v-if="vaccineForm.processing" class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
      <div v-if="showEditScheduleModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden transform transition-all duration-300 ease-out">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
            </div>
            <div>
                  <h2 class="text-xl font-bold">Edit Vaccine Schedule</h2>
                  <p class="text-blue-100 text-sm">Update vaccination schedule information</p>
            </div>
              </div>
              <button 
                @click="() => { editScheduleForm.clearErrors(); showEditScheduleModal = false; }"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors duration-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]">
            <!-- General Error Message -->
            <div v-if="editScheduleForm.hasErrors" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl">
              <div class="flex items-start gap-3">
                <div class="p-1 bg-red-100 dark:bg-red-800 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="font-semibold mb-2">Please correct the following errors:</p>
                  <div class="space-y-2">
                    <ul class="mt-2 space-y-1 text-sm">
                      <li v-for="(error, field) in editScheduleForm.errors" :key="field" class="flex items-center gap-2">
                        <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                        {{ Array.isArray(error) ? error[0] : error }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Information -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
              <div class="flex items-center gap-3 text-sm text-blue-700 dark:text-blue-300">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="font-semibold">Update Required Information</p>
                  <p class="text-xs text-blue-600 dark:text-blue-400">All fields marked with <span class="text-red-500 font-bold">*</span> are mandatory. You can add multiple vaccination stages for comprehensive scheduling.</p>
                </div>
              </div>
            </div>

            <!-- Basic Information Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Basic Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Company -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      Company
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Select the company</span>
                  </label>
                  <select 
                    v-model="editScheduleForm.company_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.company_id, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.company_id 
                    }"
                    required
                  >
                    <option value="">Select Company</option>
                    <option v-for="company in props.companies" :key="company.id" :value="company.id">
                      {{ company.name }}
                    </option>
                  </select>
                  <div v-if="editScheduleForm.errors.company_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.company_id }}
                  </div>
                </div>

                <!-- Job Number -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                      </svg>
                      Job Number
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Enter job number</span>
                  </label>
                  <input 
                    v-model="editScheduleForm.job_no" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.job_no, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.job_no 
                    }"
                    placeholder="Enter job number"
                    required
                  />
                  <div v-if="editScheduleForm.errors.job_no" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.job_no }}
                  </div>
                </div>

                <!-- Project -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                      Project
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Select the project</span>
                  </label>
                  <select 
                    v-model="editScheduleForm.project_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.project_id, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.project_id 
                    }"
                    required
                  >
                    <option value="">Select Project</option>
                    <option v-for="project in props.projects" :key="project.id" :value="project.id">
                      {{ project.name }} ({{ project.code }})
                    </option>
                  </select>
                  <div v-if="editScheduleForm.errors.project_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.project_id }}
                  </div>
                </div>

                <!-- Flock Number -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                      </svg>
                      Flock Number
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Enter flock number</span>
                  </label>
                  <input 
                    v-model="editScheduleForm.flock_no" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.flock_no, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.flock_no 
                    }"
                    placeholder="Enter flock number"
                    required
                  />
                  <div v-if="editScheduleForm.errors.flock_no" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.flock_no }}
                  </div>
                </div>

                <!-- Shed -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      Shed
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Select the shed</span>
                  </label>
                  <select 
                    v-model="editScheduleForm.shed_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.shed_id, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.shed_id 
                    }"
                    required
                  >
                    <option value="">Select Shed</option>
                    <option v-for="shed in props.sheds" :key="shed.id" :value="shed.id">
                      {{ shed.name }}
                    </option>
                  </select>
                  <div v-if="editScheduleForm.errors.shed_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.shed_id }}
                  </div>
                </div>

                <!-- Batch Number -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                      </svg>
                      Batch Number
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Enter batch number</span>
                  </label>
                  <input 
                    v-model="editScheduleForm.batch_no" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.batch_no, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.batch_no 
                    }"
                    placeholder="Enter batch number"
                    required
                  />
                  <div v-if="editScheduleForm.errors.batch_no" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.batch_no }}
                  </div>
                </div>

                <!-- Breed Type -->
                <div class="space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                      Breed Type
                      <span class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="text-xs text-gray-500 font-normal">Select the breed type</span>
                  </label>
                  <select 
                    v-model="editScheduleForm.breed_type_id" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': editScheduleForm.errors.breed_type_id, 
                      'border-gray-300 dark:border-gray-600': !editScheduleForm.errors.breed_type_id 
                    }"
                    required
                  >
                    <option value="">Select Breed Type</option>
                    <option v-for="breedType in props.breedTypes" :key="breedType.id" :value="breedType.id">
                      {{ breedType.name }}
                    </option>
                  </select>
                  <div v-if="editScheduleForm.errors.breed_type_id" class="text-red-500 text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ editScheduleForm.errors.breed_type_id }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Vaccination Stages Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Vaccination Stages
              </h3>
              
              <div 
                v-for="(stage, index) in editScheduleForm.stages"
                :key="index"
                class="mb-6 p-6 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50"
              >
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">
                    Stage {{ index + 1 }}
                  </h4>
                  <button
                    v-if="editScheduleForm.stages.length > 1"
                    @click="removeEditStage(index)"
                    class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <!-- Disease -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Disease <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select 
                      v-model="stage.disease_id" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (editScheduleForm.errors as any)[`stages.${index}.disease_id`], 
                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[`stages.${index}.disease_id`] 
                      }"
                      required
                    >
                      <option value="">Select Disease</option>
                      <option v-for="disease in props.diseases" :key="disease.id" :value="disease.id">
                        {{ disease.name }}
                      </option>
                    </select>
                    <div v-if="(editScheduleForm.errors as any)[`stages.${index}.disease_id`]" class="text-red-500 text-sm">
                      {{ (editScheduleForm.errors as any)[`stages.${index}.disease_id`] }}
                    </div>
                  </div>

                  <!-- Vaccine -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Vaccine <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select 
                      v-model="stage.vaccine_id" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (editScheduleForm.errors as any)[`stages.${index}.vaccine_id`], 
                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[`stages.${index}.vaccine_id`] 
                      }"
                      required
                    >
                <option value="">Select Vaccine</option>
                      <option v-for="vaccine in props.vaccines" :key="vaccine.id" :value="vaccine.id">
                        {{ vaccine.name }}
                      </option>
              </select>
                    <div v-if="(editScheduleForm.errors as any)[`stages.${index}.vaccine_id`]" class="text-red-500 text-sm">
                      {{ (editScheduleForm.errors as any)[`stages.${index}.vaccine_id`] }}
                    </div>
                  </div>

                  <!-- Age -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Age <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input 
                      v-model="stage.age" 
                      type="text" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (editScheduleForm.errors as any)[`stages.${index}.age`], 
                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[`stages.${index}.age`] 
                      }"
                      placeholder="e.g., 7 days, 2 weeks"
                      required
                    />
                    <div v-if="(editScheduleForm.errors as any)[`stages.${index}.age`]" class="text-red-500 text-sm">
                      {{ (editScheduleForm.errors as any)[`stages.${index}.age`] }}
                    </div>
                  </div>

                  <!-- Vaccination Date -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Vaccination Date <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input 
                      v-model="stage.vaccination_date" 
                      type="date" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (editScheduleForm.errors as any)[`stages.${index}.vaccination_date`], 
                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[`stages.${index}.vaccination_date`] 
                      }"
                      required
                    />
                    <div v-if="(editScheduleForm.errors as any)[`stages.${index}.vaccination_date`]" class="text-red-500 text-sm">
                      {{ (editScheduleForm.errors as any)[`stages.${index}.vaccination_date`] }}
                    </div>
                  </div>

                  <!-- Next Vaccination Date -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Next Vaccination Date
                    </label>
                    <input 
                      v-model="stage.next_vaccination_date" 
                      type="date" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      :class="{ 
                        'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': (editScheduleForm.errors as any)[`stages.${index}.next_vaccination_date`], 
                        'border-gray-300 dark:border-gray-500': !(editScheduleForm.errors as any)[`stages.${index}.next_vaccination_date`] 
                      }"
                    />
                    <div v-if="(editScheduleForm.errors as any)[`stages.${index}.next_vaccination_date`]" class="text-red-500 text-sm">
                      {{ (editScheduleForm.errors as any)[`stages.${index}.next_vaccination_date`] }}
                    </div>
                  </div>

                  <!-- Administered By -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      Administered By
                    </label>
                    <input 
                      v-model="stage.administered_by" 
                      type="text" 
                      class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                      placeholder="Enter name of person who administered"
                    />
                  </div>
                </div>

                <!-- Notes -->
                <div class="mt-4 space-y-2">
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Notes
                  </label>
                  <textarea 
                    v-model="stage.notes" 
                    class="w-full px-3 py-2 border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-white resize-none"
                    placeholder="Additional notes for this vaccination stage..."
                    rows="2"
                  ></textarea>
                </div>
              </div>

              <!-- Add Stage Button -->
              <button
                @click="addEditStage"
                class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 font-medium"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Another Stage
              </button>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex justify-end gap-3">
              <button 
                class="px-6 py-3 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition-all duration-200 font-medium" 
                @click="() => { editScheduleForm.clearErrors(); showEditScheduleModal = false; }"
                :disabled="editScheduleForm.processing"
              >
              Cancel
            </button>
              <button 
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
                @click="updateSchedule"
                :disabled="editScheduleForm.processing"
              >
                <span v-if="editScheduleForm.processing" class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
      <div v-if="showRoutingModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all duration-300 ease-out">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                  </svg>
            </div>
            <div>
                  <h2 class="text-xl font-bold">Add New Vaccine Routing</h2>
                  <p class="text-purple-100 text-sm">Create a new vaccine routing configuration</p>
                </div>
              </div>
              <button 
                @click="() => { routingForm.clearErrors(); showRoutingModal = false; }"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors duration-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <!-- General Error Message -->
            <div v-if="routingForm.hasErrors" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl">
              <div class="flex items-start gap-3">
                <div class="p-1 bg-red-100 dark:bg-red-800 rounded-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
            </div>
            <div>
                  <p class="font-semibold">Please correct the following errors:</p>
                  <ul class="mt-2 space-y-1 text-sm">
                    <li v-for="(error, field) in routingForm.errors" :key="field" class="flex items-center gap-2">
                      <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                      {{ Array.isArray(error) ? error[0] : error }}
                    </li>
                  </ul>
            </div>
            </div>
          </div>

            <!-- Form Information -->
            <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-200 dark:border-purple-800 rounded-xl">
              <div class="flex items-center gap-3 text-sm text-purple-700 dark:text-purple-300">
                <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="font-semibold">Required Information</p>
                  <p class="text-xs text-purple-600 dark:text-purple-400">Route name and status are required. Description is optional.</p>
                </div>
              </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-6">
              <!-- Route Name -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Route Name
                    <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-xs text-gray-500 font-normal">Enter a unique name for the vaccine routing</span>
                </label>
                <div class="relative">
                  <input 
                    v-model="routingForm.name" 
                    type="text" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': routingForm.errors.name, 
                      'border-gray-300 dark:border-gray-600': !routingForm.errors.name 
                    }"
                    placeholder="e.g., Primary Route, Secondary Route, Emergency Route"
                    required
                  />
                  <div v-if="routingForm.errors.name" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div v-if="routingForm.errors.name" class="text-red-500 text-sm flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ routingForm.errors.name }}
                </div>
              </div>

              <!-- Description -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Description
                    <span class="text-xs text-gray-500 font-normal">(Optional)</span>
                  </span>
                  <span class="text-xs text-gray-500 font-normal">Provide additional details about this routing</span>
                </label>
                <textarea 
                  v-model="routingForm.description" 
                  class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none"
                  :class="{ 
                    'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': routingForm.errors.description, 
                    'border-gray-300 dark:border-gray-600': !routingForm.errors.description 
                  }"
                  placeholder="Enter a detailed description of this vaccine routing configuration..."
                  rows="4"
                ></textarea>
                <div v-if="routingForm.errors.description" class="text-red-500 text-sm flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ routingForm.errors.description }}
                </div>
              </div>

              <!-- Status -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Status
                    <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-xs text-gray-500 font-normal">Select the status for this vaccine routing</span>
                </label>
                <div class="relative">
                  <select 
                    v-model="routingForm.status" 
                    class="w-full px-4 py-3 border rounded-xl transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none cursor-pointer"
                    :class="{ 
                      'border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-500': routingForm.errors.status, 
                      'border-gray-300 dark:border-gray-600': !routingForm.errors.status 
                    }"
                    required
                  >
                    <option value="">Select Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                  <div v-if="routingForm.errors.status" class="absolute right-8 top-1/2 transform -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div v-if="routingForm.errors.status" class="text-red-500 text-sm flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ routingForm.errors.status }}
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex justify-end gap-3">
              <button 
                class="px-6 py-3 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition-all duration-200 font-medium" 
                @click="() => { routingForm.clearErrors(); showRoutingModal = false; }"
                :disabled="routingForm.processing"
              >
              Cancel
            </button>
              <button 
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
                @click="saveRouting"
                :disabled="routingForm.processing"
              >
                <span v-if="routingForm.processing" class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
