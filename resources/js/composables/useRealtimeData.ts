import { ref, onMounted, onUnmounted, computed, readonly, watch } from 'vue'
import { router } from '@inertiajs/vue3'

interface RealtimeData {
  cards: any[]
  progressBars: any[]
  circleBars: any[]
  birdStage: any
  chartData: any
  lastUpdated: string
  timestamp: number
}

interface RealtimeOptions {
  pollingInterval?: number
  enableWebSocket?: boolean
  enablePolling?: boolean
  autoRefresh?: boolean
}

export function useRealtimeData(options: RealtimeOptions = {}) {
  const {
    pollingInterval = 30000, // 30 seconds
    enableWebSocket = false, // Disabled for now, using polling
    enablePolling = true,
    autoRefresh = true
  } = options

  // Reactive state
  const data = ref<RealtimeData | null>(null)
  const isLoading = ref(false)
  const isConnected = ref(false)
  const lastUpdate = ref<number>(0)
  const error = ref<string | null>(null)
  const connectionStatus = ref<'connecting' | 'connected' | 'disconnected' | 'error'>('disconnected')

  // Polling state
  let pollingTimer: NodeJS.Timeout | null = null
  let websocket: WebSocket | null = null

  // Computed properties
  const hasData = computed(() => data.value !== null)
  const timeSinceUpdate = computed(() => {
    if (!data.value?.timestamp) return null
    const now = Date.now() / 1000
    return Math.floor(now - data.value.timestamp)
  })

  // Methods
  const fetchRealtimeData = async (filters: Record<string, any> = {}) => {
    try {
      isLoading.value = true
      error.value = null
      connectionStatus.value = 'connecting'

      const response = await fetch('/api/dashboard/realtime?' + new URLSearchParams(filters), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()
      
      if (result.success) {
        data.value = result.data
        lastUpdate.value = result.timestamp
        isConnected.value = true
        connectionStatus.value = 'connected'
      } else {
        throw new Error(result.message || 'Failed to fetch data')
      }

    } catch (err) {
      console.error('Error fetching real-time data:', err)
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      connectionStatus.value = 'error'
      isConnected.value = false
    } finally {
      isLoading.value = false
    }
  }

  const pollData = async (filters: Record<string, any> = {}) => {
    try {
      const response = await fetch('/api/dashboard/poll?' + new URLSearchParams({
        ...filters,
        last_update: lastUpdate.value.toString()
      }), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()
      
      if (result.success && result.hasChanged) {
        data.value = result.data
        lastUpdate.value = result.timestamp
        isConnected.value = true
        connectionStatus.value = 'connected'
      }

    } catch (err) {
      console.error('Error polling data:', err)
      connectionStatus.value = 'error'
      isConnected.value = false
    }
  }

  const triggerUpdate = async (filters: Record<string, any> = {}) => {
    try {
      const response = await fetch('/api/dashboard/trigger-update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(filters)
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()
      
      if (result.success) {
        data.value = result.data
        lastUpdate.value = result.timestamp
      }

    } catch (err) {
      console.error('Error triggering update:', err)
    }
  }

  const startPolling = (filters: Record<string, any> = {}) => {
    if (pollingTimer) {
      clearInterval(pollingTimer)
    }

    pollingTimer = setInterval(() => {
      pollData(filters)
    }, pollingInterval)
  }

  const stopPolling = () => {
    if (pollingTimer) {
      clearInterval(pollingTimer)
      pollingTimer = null
    }
  }

  const connectWebSocket = () => {
    if (!enableWebSocket) return

    try {
      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:'
      const wsUrl = `${protocol}//${window.location.host}/ws`
      
      websocket = new WebSocket(wsUrl)

      websocket.onopen = () => {
        console.log('WebSocket connected')
        isConnected.value = true
        connectionStatus.value = 'connected'
      }

      websocket.onmessage = (event) => {
        try {
          const message = JSON.parse(event.data)
          if (message.type === 'dashboard.updated') {
            data.value = message.data
            lastUpdate.value = message.timestamp
          }
        } catch (err) {
          console.error('Error parsing WebSocket message:', err)
        }
      }

      websocket.onclose = () => {
        console.log('WebSocket disconnected')
        isConnected.value = false
        connectionStatus.value = 'disconnected'
        
        // Attempt to reconnect after 5 seconds
        setTimeout(() => {
          if (enableWebSocket) {
            connectWebSocket()
          }
        }, 5000)
      }

      websocket.onerror = (error) => {
        console.error('WebSocket error:', error)
        connectionStatus.value = 'error'
        isConnected.value = false
      }

    } catch (err) {
      console.error('Error connecting WebSocket:', err)
      connectionStatus.value = 'error'
    }
  }

  const disconnectWebSocket = () => {
    if (websocket) {
      websocket.close()
      websocket = null
    }
  }

  const refresh = async (filters: Record<string, any> = {}) => {
    await fetchRealtimeData(filters)
  }

  // Lifecycle
  onMounted(() => {
    if (autoRefresh) {
      fetchRealtimeData()
    }

    if (enablePolling) {
      startPolling()
    }

    if (enableWebSocket) {
      connectWebSocket()
    }
  })

  onUnmounted(() => {
    stopPolling()
    disconnectWebSocket()
  })

  return {
    // State
    data: readonly(data),
    isLoading: readonly(isLoading),
    isConnected: readonly(isConnected),
    lastUpdate: readonly(lastUpdate),
    error: readonly(error),
    connectionStatus: readonly(connectionStatus),
    
    // Computed
    hasData,
    timeSinceUpdate,
    
    // Methods
    fetchRealtimeData,
    pollData,
    triggerUpdate,
    startPolling,
    stopPolling,
    connectWebSocket,
    disconnectWebSocket,
    refresh
  }
}
