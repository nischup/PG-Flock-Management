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

    const audioPath = '/Audio/notification-sound.mp3'
    console.log('Initializing notification sound with path:', audioPath)
    
    audio.value = new Audio(audioPath)
    audio.value.volume = volume
    audio.value.loop = loop
    audio.value.preload = preload ? 'auto' : 'none'
    
    // Handle audio events
    audio.value.addEventListener('loadstart', () => {
      console.log('Audio loading started')
    })
    
    audio.value.addEventListener('canplay', () => {
      console.log('Audio can play')
    })
    
    audio.value.addEventListener('play', () => {
      console.log('Audio playing')
      isPlaying.value = true
    })
    
    audio.value.addEventListener('ended', () => {
      console.log('Audio ended')
      isPlaying.value = false
    })
    
    audio.value.addEventListener('error', (e) => {
      console.error('Failed to load notification sound:', e)
      console.error('Audio error details:', {
        error: audio.value?.error,
        networkState: audio.value?.networkState,
        readyState: audio.value?.readyState,
        src: audio.value?.src
      })
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
        
        // Try to play the audio
        const playPromise = audio.value.play()
        
        if (playPromise !== undefined) {
          await playPromise
          console.log('Notification sound played successfully')
        }
      }
    } catch (error) {
      console.warn('Failed to play notification sound:', error)
      console.warn('This might be due to browser autoplay policies. User interaction may be required.')
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

  // Test function for debugging
  const testSound = (): void => {
    console.log('Testing notification sound...')
    playSound()
  }

  // Enable audio after user interaction (for autoplay policies)
  const enableAudioAfterInteraction = (): void => {
    if (audio.value) {
      // Try to play and immediately pause to enable audio
      audio.value.play().then(() => {
        audio.value?.pause()
        audio.value!.currentTime = 0
        console.log('Audio enabled after user interaction')
      }).catch((error) => {
        console.warn('Failed to enable audio:', error)
      })
    }
  }

  return {
    playSound,
    stopSound,
    setEnabled,
    setVolume,
    preloadAudio,
    testSound,
    enableAudioAfterInteraction,
    isEnabled: readonly(isEnabled),
    isPlaying: readonly(isPlaying)
  }
}
