<template>
  <div class="approval-matrix-status">
    <div class="approval-header">
      <h3 class="text-lg font-semibold text-gray-900">Approval Status</h3>
      <span :class="statusClass" class="px-2 py-1 text-xs font-semibold rounded-full">
        {{ statusText }}
      </span>
    </div>
    
    <div class="approval-layers mt-4">
      <div 
        v-for="(layer, index) in layers" 
        :key="layer.id"
        :class="['approval-layer', layer.status]"
        class="flex items-center justify-between p-3 border rounded-lg mb-2"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div :class="getLayerIconClass(layer.status)" class="w-8 h-8 rounded-full flex items-center justify-center">
              <span class="text-sm font-semibold">{{ layer.layer_order }}</span>
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-900">{{ layer.layer_name }}</div>
            <div class="text-xs text-gray-500">{{ layer.role_name }}</div>
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <span :class="getStatusBadgeClass(layer.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
            {{ getStatusText(layer.status) }}
          </span>
          <span v-if="layer.approver_name" class="text-xs text-gray-500">
            by {{ layer.approver_name }}
          </span>
        </div>
      </div>
    </div>

    <!-- Action buttons for current user -->
    <div v-if="canApprove && currentLayer" class="mt-4 p-4 bg-blue-50 rounded-lg">
      <h4 class="text-sm font-medium text-blue-900 mb-2">Your Action Required</h4>
      <p class="text-sm text-blue-700 mb-3">
        You need to approve or reject this {{ currentLayer.layer_name }} request.
      </p>
      <div class="flex space-x-2">
        <button
          @click="$emit('approve')"
          class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
        >
          Approve
        </button>
        <button
          @click="$emit('reject')"
          class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
        >
          Reject
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Layer {
  id: number;
  layer_order: number;
  layer_name: string;
  role_name: string;
  is_required: boolean;
  can_override: boolean;
  timeout_hours?: number;
  status: 'pending' | 'completed' | 'rejected' | 'current';
  approver_name?: string;
  action_at?: string;
  comments?: string;
}

interface Props {
  layers: Layer[];
  currentLayer?: Layer;
  canApprove: boolean;
  overallStatus: 'pending' | 'approved' | 'rejected' | 'expired';
}

const props = defineProps<Props>();

const emit = defineEmits<{
  approve: [];
  reject: [];
}>();

const statusText = computed(() => {
  const statusMap = {
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Rejected',
    expired: 'Expired',
  };
  return statusMap[props.overallStatus] || 'Unknown';
});

const statusClass = computed(() => {
  const classMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    expired: 'bg-gray-100 text-gray-800',
  };
  return classMap[props.overallStatus] || 'bg-gray-100 text-gray-800';
});

const getLayerIconClass = (status: string) => {
  const classMap = {
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    current: 'bg-blue-100 text-blue-800',
    pending: 'bg-gray-100 text-gray-800',
  };
  return classMap[status] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeClass = (status: string) => {
  const classMap = {
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    current: 'bg-blue-100 text-blue-800',
    pending: 'bg-gray-100 text-gray-800',
  };
  return classMap[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status: string) => {
  const textMap = {
    completed: '✓ Completed',
    rejected: '✗ Rejected',
    current: '⏳ Current',
    pending: '⏸ Waiting',
  };
  return textMap[status] || 'Unknown';
};
</script>

<style scoped>
.approval-matrix-status {
  @apply bg-white p-4 rounded-lg shadow-sm border;
}

.approval-header {
  @apply flex items-center justify-between mb-4;
}

.approval-layer {
  @apply transition-colors duration-200;
}

.approval-layer.completed {
  @apply bg-green-50 border-green-200;
}

.approval-layer.rejected {
  @apply bg-red-50 border-red-200;
}

.approval-layer.current {
  @apply bg-blue-50 border-blue-200;
}

.approval-layer.pending {
  @apply bg-gray-50 border-gray-200;
}
</style>
