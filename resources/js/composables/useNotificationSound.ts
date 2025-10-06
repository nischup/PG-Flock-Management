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

    // Try multiple audio formats for better browser compatibility
    const audioFormats = [
      '/Audio/notification-sound.wav',  // WAV is more universally supported
      '/Audio/notification-sound.mp3',
      '/Audio/notification-sound.ogg'
    ]
    
    console.log('Initializing notification sound with fallback formats:', audioFormats)
    
    // Create audio element with the first format
    audio.value = new Audio(audioFormats[0])
    audio.value.volume = volume
    audio.value.loop = loop
    audio.value.preload = preload ? 'auto' : 'none'
    
    // Add crossOrigin attribute for better compatibility
    audio.value.crossOrigin = 'anonymous'
    
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
      
      // Try fallback formats if the first one fails
      const currentSrc = audio.value?.src
      const currentIndex = audioFormats.findIndex(format => currentSrc?.includes(format))
      
      if (currentIndex < audioFormats.length - 1) {
        console.log('Trying fallback audio format:', audioFormats[currentIndex + 1])
        audio.value!.src = audioFormats[currentIndex + 1]
        audio.value!.load()
      } else {
        console.warn('All audio formats failed to load. Notification sound disabled.')
        isEnabled.value = false
      }
      
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
      
      // Fallback: Use Web Audio API to generate a simple beep sound
      playFallbackSound()
    }
  }

  // Fallback sound using Web Audio API
  const playFallbackSound = (): void => {
    try {
      const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)()
      const oscillator = audioContext.createOscillator()
      const gainNode = audioContext.createGain()
      
      oscillator.connect(gainNode)
      gainNode.connect(audioContext.destination)
      
      oscillator.frequency.setValueAtTime(800, audioContext.currentTime) // 800Hz frequency
      oscillator.type = 'sine'
      
      gainNode.gain.setValueAtTime(0, audioContext.currentTime)
      gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3)
      
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.3)
      
      console.log('Played fallback notification sound')
    } catch (error) {
      console.warn('Fallback sound also failed:', error)
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

  // Test fallback sound
  const testFallbackSound = (): void => {
    console.log('Testing fallback notification sound...')
    playFallbackSound()
  }

  // Check if audio is supported
  const isAudioSupported = (): boolean => {
    try {
      const audio = new Audio()
      return !!(audio.canPlayType && audio.canPlayType('audio/wav') !== '')
    } catch (e) {
      return false
    }
  }

  // Get audio support info
  const getAudioSupportInfo = (): object => {
    const audio = new Audio()
    return {
      canPlayWAV: audio.canPlayType('audio/wav'),
      canPlayMP3: audio.canPlayType('audio/mpeg'),
      canPlayOGG: audio.canPlayType('audio/ogg'),
      hasWebAudio: !!(window.AudioContext || (window as any).webkitAudioContext)
    }
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
    testFallbackSound,
    enableAudioAfterInteraction,
    isAudioSupported,
    getAudioSupportInfo,
    isEnabled: readonly(isEnabled),
    isPlaying: readonly(isPlaying)
  }
}
