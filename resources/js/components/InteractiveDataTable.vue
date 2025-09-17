<template>
  <div class="bg-white/40 backdrop-blur-md border border-white/50 rounded-xl shadow-lg overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
      <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
          </div>
          
          <!-- Filter Dropdown -->
          <select
            v-model="selectedFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All {{ filterKey }}</option>
            <option v-for="option in filterOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          
          <!-- Export Button -->
          <button
            @click="exportData"
            class="flex items-center space-x-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200"
          >
            <Download class="h-4 w-4" />
            <span>Export</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Table Content -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-100/50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              @click="sortBy(column.key)"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <ArrowUpDown v-if="sortKey === column.key" class="h-4 w-4" />
                <ChevronUp v-else-if="sortKey === column.key && sortDirection === 'asc'" class="h-4 w-4" />
                <ChevronDown v-else-if="sortKey === column.key && sortDirection === 'desc'" class="h-4 w-4" />
              </div>
            </th>
            <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="(row, index) in paginatedData"
            :key="index"
            class="hover:bg-gray-50 transition-colors duration-200"
            :class="{ 'bg-blue-50': selectedRows.includes(index) }"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
            >
              <div v-if="column.type === 'badge'" class="flex items-center">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getBadgeClass(row[column.key])"
                >
                  {{ row[column.key] }}
                </span>
              </div>
              <div v-else-if="column.type === 'progress'" class="flex items-center">
                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                  <div
                    class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                    :style="{ width: row[column.key] + '%' }"
                  ></div>
                </div>
                <span class="text-sm text-gray-600">{{ row[column.key] }}%</span>
              </div>
              <div v-else-if="column.type === 'date'" class="text-sm text-gray-900">
                {{ formatDate(row[column.key]) }}
              </div>
            
              <div v-else class="text-sm text-gray-900">
                {{ row[column.key] }}
              </div>
            </td>
            <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex justify-end space-x-2">
                <button
                  v-for="action in actions"
                  :key="action.name"
                  @click="action.handler(row, index)"
                  class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                  :class="action.class"
                >
                  <component :is="action.icon" class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-700">
            Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} results
          </span>
        </div>
        
        <div class="flex items-center space-x-2">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <div class="flex space-x-1">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="currentPage = page"
              class="px-3 py-1 text-sm border rounded-md transition-colors duration-200"
              :class="page === currentPage 
                ? 'bg-blue-500 text-white border-blue-500' 
                : 'border-gray-300 hover:bg-gray-50'"
            >
              {{ page }}
            </button>
          </div>
          
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Search, Download, ChevronUp, ChevronDown, ArrowUpDown, Eye, Edit, Trash2 } from 'lucide-vue-next'

interface Column {
  key: string
  label: string
  type?: 'text' | 'badge' | 'progress' | 'date' | 'currency'
  sortable?: boolean
}

interface Action {
  name: string
  icon: any
  handler: (row: any, index: number) => void
  class?: string
}

interface Props {
  title: string
  data: any[]
  columns: Column[]
  actions?: Action[]
  filterKey?: string
  filterOptions?: { value: string; label: string }[]
  itemsPerPage?: number
}

const props = withDefaults(defineProps<Props>(), {
  actions: () => [],
  filterKey: 'status',
  filterOptions: () => [],
  itemsPerPage: 10
})

// Reactive data
const searchQuery = ref('')
const selectedFilter = ref('')
const sortKey = ref('')
const sortDirection = ref<'asc' | 'desc'>('asc')
const currentPage = ref(1)
const selectedRows = ref<number[]>([])

// Computed properties
const filteredData = computed(() => {
  let filtered = props.data

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(row =>
      props.columns.some(column =>
        String(row[column.key]).toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    )
  }

  // Dropdown filter
  if (selectedFilter.value) {
    filtered = filtered.filter(row => row[props.filterKey] === selectedFilter.value)
  }

  // Sorting
  if (sortKey.value) {
    filtered = [...filtered].sort((a, b) => {
      const aVal = a[sortKey.value]
      const bVal = b[sortKey.value]
      
      if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  }

  return filtered
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / props.itemsPerPage))

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * props.itemsPerPage
  const end = start + props.itemsPerPage
  return filteredData.value.slice(start, end)
})

const startIndex = computed(() => (currentPage.value - 1) * props.itemsPerPage)
const endIndex = computed(() => Math.min(startIndex.value + props.itemsPerPage, filteredData.value.length))

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

const hasActions = computed(() => props.actions.length > 0)

// Methods
const sortBy = (key: string) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const exportData = () => {
  // Implement export functionality
  console.log('Exporting data...', filteredData.value)
}

const getBadgeClass = (value: string) => {
  const classes = {
    'Active': 'bg-green-100 text-green-800',
    'Inactive': 'bg-red-100 text-red-800',
    'Pending': 'bg-yellow-100 text-yellow-800',
    'Completed': 'bg-blue-100 text-blue-800'
  }
  return classes[value as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date: string | Date) => {
  return new Date(date).toLocaleDateString()
}

</script>
