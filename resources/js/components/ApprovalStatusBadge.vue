<template>
    <div v-if="loading" class="flex items-center justify-center">
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="approvalData" class="flex items-center space-x-2">
        <div :class="statusBadgeClass" class="px-2 py-1 rounded-full text-xs font-medium">
            {{ statusText }}
        </div>
        <div v-if="approvalData.current_layer && approvalData.total_layers" class="text-xs text-gray-500">
            {{ approvalData.current_layer }}/{{ approvalData.total_layers }}
        </div>
    </div>
    
    <div v-else class="text-xs text-gray-400">
        No approval
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    psReceiveId: {
        type: Number,
        required: true
    }
})

// Reactive data
const loading = ref(false)
const approvalData = ref(null)

// Computed properties
const statusText = computed(() => {
    if (!approvalData.value) return 'Loading...'
    
    switch (approvalData.value.status) {
        case 'pending':
            return 'Pending'
        case 'in_progress':
            return 'In Progress'
        case 'approved':
            return 'Approved'
        case 'rejected':
            return 'Rejected'
        default:
            return 'Unknown'
    }
})

const statusBadgeClass = computed(() => {
    if (!approvalData.value) return 'bg-gray-100 text-gray-800'
    
    switch (approvalData.value.status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800'
        case 'in_progress':
            return 'bg-blue-100 text-blue-800'
        case 'approved':
            return 'bg-green-100 text-green-800'
        case 'rejected':
            return 'bg-red-100 text-red-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
})

// Methods
const fetchApprovalStatus = async () => {
    loading.value = true
    try {
        const response = await fetch(`/ps-receive/${props.psReceiveId}/approval-status`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        if (response.ok) {
            const data = await response.json()
            approvalData.value = data
        } else {
            console.error('Failed to fetch approval status:', response.status, response.statusText)
        }
    } catch (error) {
        console.error('Error fetching approval status:', error)
    } finally {
        loading.value = false
    }
}

// Lifecycle
onMounted(() => {
    fetchApprovalStatus()
})
</script>

<style scoped>
/* Component styles */
</style>
