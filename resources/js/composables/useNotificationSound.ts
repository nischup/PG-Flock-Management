import { ref, readonly } from 'vue'

interface NotificationSoundOptions {
  volume?: number
  loop?: boolean
  preload?: boolean
}

export function useNotificationSound(options: NotificationSoundOptions = {}) {
  const {
    volume = 0.5,
    loop = false,
    preload = true
  } = options

  const audio = ref<HTMLAudioElement | null>(null)
  const isEnabled = ref(true)
  const isPlaying = ref(false)

  // Initialize audio element
  const initAudio = () => {
    if (audio.value) return

    audio.value = new Audio('/Audio/notification-sound.mp3')
    audio.value.volume = volume
    audio.value.loop = loop
    audio.value.preload = preload ? 'auto' : 'none'
    
    // Handle audio events
    audio.value.addEventListener('play', () => {
      isPlaying.value = true
    })
    
    audio.value.addEventListener('ended', () => {
      isPlaying.value = false
    })
    
    audio.value.addEventListener('error', (e) => {
      console.warn('Failed to load notification sound:', e)
      isPlaying.value = false
    })
  }

  // Play notification sound
  const playSound = async (): Promise<void> => {
    if (!isEnabled.value) return

    try {
      // Initialize audio if not already done
      if (!audio.value) {
        initAudio()
      }

      // Reset audio to beginning
      if (audio.value) {
        audio.value.currentTime = 0
        await audio.value.play()
      }
    } catch (error) {
      console.warn('Failed to play notification sound:', error)
    }
  }

  // Stop notification sound
  const stopSound = (): void => {
    if (audio.value && !audio.value.paused) {
      audio.value.pause()
      audio.value.currentTime = 0
      isPlaying.value = false
    }
  }

  // Enable/disable sound
  const setEnabled = (enabled: boolean): void => {
    isEnabled.value = enabled
    if (!enabled) {
      stopSound()
    }
  }

  // Set volume
  const setVolume = (newVolume: number): void => {
    const clampedVolume = Math.max(0, Math.min(1, newVolume))
    if (audio.value) {
      audio.value.volume = clampedVolume
    }
  }

  // Preload audio
  const preloadAudio = (): void => {
    if (!audio.value) {
      initAudio()
    }
  }

  return {
    playSound,
    stopSound,
    setEnabled,
    setVolume,
    preloadAudio,
    isEnabled: readonly(isEnabled),
    isPlaying: readonly(isPlaying)
  }
}
