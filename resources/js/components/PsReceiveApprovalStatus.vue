<template>
    <div class="approval-status-container">
        <div v-if="loading" class="flex items-center justify-center p-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        </div>
        
        <div v-else-if="approvalData" class="space-y-4">
            <!-- Approval Status Header -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <div :class="statusBadgeClass" class="px-3 py-1 rounded-full text-sm font-medium">
                            {{ statusText }}
                        </div>
                        <div class="text-sm text-gray-600">
                            Layer {{ approvalData.current_layer }} of {{ approvalData.total_layers }}
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div v-if="approvalData.can_approve || approvalData.can_reject" class="flex space-x-2">
                    <button
                        v-if="approvalData.can_approve"
                        @click="showApproveModal = true"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors"
                    >
                        Approve
                    </button>
                    <button
                        v-if="approvalData.can_reject"
                        @click="showRejectModal = true"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors"
                    >
                        Reject
                    </button>
                </div>
            </div>

            <!-- Approval Actions Timeline -->
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Approval Timeline</h4>
                <div class="space-y-2">
                    <div
                        v-for="(action, index) in approvalData.actions"
                        :key="index"
                        class="flex items-center space-x-3 p-3 bg-white border rounded-lg"
                    >
                        <div :class="getActionIconClass(action.status)" class="w-8 h-8 rounded-full flex items-center justify-center">
                            <CheckIcon v-if="action.status === 'completed'" class="w-4 h-4 text-white" />
                            <XIcon v-else-if="action.status === 'rejected'" class="w-4 h-4 text-white" />
                            <ClockIcon v-else class="w-4 h-4 text-white" />
                        </div>
                        <div class="flex-1">
                            <div class="font-medium">{{ action.layer_name }}</div>
                            <div class="text-sm text-gray-600">
                                {{ action.status === 'pending' ? 'Pending' : action.status === 'completed' ? 'Approved' : 'Rejected' }}
                                <span v-if="action.action_at" class="ml-2">
                                    on {{ formatDate(action.action_at) }}
                                </span>
                            </div>
                            <div v-if="action.comments" class="text-sm text-gray-500 mt-1">
                                {{ action.comments }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div v-if="showApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
                <h3 class="text-lg font-medium mb-4">Approve PS Receive</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comments (Optional)</label>
                        <textarea
                            v-model="approveComments"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            rows="3"
                            placeholder="Add any comments..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="showApproveModal = false; approveComments = ''"
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleApprove"
                            :disabled="approving"
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
                        >
                            {{ approving ? 'Approving...' : 'Approve' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4">
                <h3 class="text-lg font-medium mb-4">Reject PS Receive</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection *</label>
                        <textarea
                            v-model="rejectComments"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                            rows="3"
                            placeholder="Please provide a reason for rejection..."
                            required
                        ></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="showRejectModal = false; rejectComments = ''"
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleReject"
                            :disabled="rejecting || !rejectComments.trim()"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 disabled:opacity-50"
                        >
                            {{ rejecting ? 'Rejecting...' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CheckIcon, XIcon, ClockIcon } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
const props = defineProps({
    psReceiveId: {
        type: Number,
        required: true
    }
})

// Simple notification function
const notify = (type, message) => {
    if (type === 'success') {
        alert('✅ ' + message)
    } else if (type === 'error') {
        alert('❌ ' + message)
    }
}

// Reactive data
const loading = ref(false)
const approvalData = ref(null)
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const approveComments = ref('')
const rejectComments = ref('')
const approving = ref(false)
const rejecting = ref(false)

// Computed properties
const statusText = computed(() => {
    if (!approvalData.value) return 'Loading...'
    
    switch (approvalData.value.status) {
        case 'pending':
            return 'Pending Approval'
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
            approvalData.value = await response.json()
        } else {
            console.error('Failed to fetch approval status:', response.status, response.statusText)
        }
    } catch (error) {
        console.error('Error fetching approval status:', error)
    } finally {
        loading.value = false
    }
}

const handleApprove = async () => {
    approving.value = true
    try {
        router.post(`/ps-receive/${props.psReceiveId}/approve`, {
            comments: approveComments.value
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                // Check for flash messages
                if (page.props.flash?.success) {
                    notify('success', page.props.flash.success)
                }
                showApproveModal.value = false
                approveComments.value = ''
                fetchApprovalStatus()
            },
            onError: (errors) => {
                console.error('Approval failed:', errors)
                notify('error', 'Failed to approve PS Receive')
            },
            onFinish: () => {
                approving.value = false
            }
        })
    } catch (error) {
        console.error('Error approving:', error)
        notify('error', 'Failed to approve PS Receive')
        approving.value = false
    }
}

const handleReject = async () => {
    rejecting.value = true
    try {
        router.post(`/ps-receive/${props.psReceiveId}/reject`, {
            comments: rejectComments.value
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                // Check for flash messages
                if (page.props.flash?.success) {
                    notify('success', page.props.flash.success)
                }
                showRejectModal.value = false
                rejectComments.value = ''
                fetchApprovalStatus()
            },
            onError: (errors) => {
                console.error('Rejection failed:', errors)
                notify('error', 'Failed to reject PS Receive')
            },
            onFinish: () => {
                rejecting.value = false
            }
        })
    } catch (error) {
        console.error('Error rejecting:', error)
        notify('error', 'Failed to reject PS Receive')
        rejecting.value = false
    }
}

const getActionIconClass = (status) => {
    switch (status) {
        case 'completed':
            return 'bg-green-500'
        case 'rejected':
            return 'bg-red-500'
        default:
            return 'bg-gray-400'
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Lifecycle
onMounted(() => {
    fetchApprovalStatus()
})
</script>

<style scoped>
.approval-status-container {
    @apply w-full;
}
</style>
