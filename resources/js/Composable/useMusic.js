import { ref } from 'vue'

const STORAGE_KEY = 'geniuss_music_on'
export const DEFAULT_MUSIC = '/backsound/intro-song.mp3'

const musicOn    = ref(localStorage.getItem(STORAGE_KEY) !== 'false')
const audioRef   = ref(null)
const currentSrc = ref(null)

export function useMusic() {

    const savePref = (val) => localStorage.setItem(STORAGE_KEY, String(val))

    const handleVisibility = () => {
        if (!audioRef.value) return
        document.hidden
            ? audioRef.value.pause()
            : (musicOn.value && audioRef.value.play().catch(() => {}))
    }

    const initAutoMusic = async (src) => {
        const resolvedSrc = src ?? DEFAULT_MUSIC

        if (currentSrc.value === resolvedSrc) {
            if (musicOn.value && audioRef.value?.paused) {
                try { await audioRef.value.play() } catch {}
            }
            return
        }

        if (audioRef.value) {
            audioRef.value.pause()
            audioRef.value = null
        }

        currentSrc.value       = resolvedSrc
        audioRef.value         = new Audio(resolvedSrc)
        audioRef.value.loop    = true
        audioRef.value.volume  = 0.4
        audioRef.value.preload = 'auto'
        audioRef.value.addEventListener('error', () => {
            audioRef.value   = null
            currentSrc.value = null
            musicOn.value    = false
        })

        if (!musicOn.value) return

        try {
            await audioRef.value.play()
        } catch {
            // Autoplay blocked — mark as off, wait for any user click (NOT excluding music button)
            musicOn.value = false
            // NOTE: We do NOT attach a once-click listener here anymore.
            // toggleMusic handles play on demand correctly.
        }
    }

    const toggleMusic = async (src) => {
        const resolvedSrc = src ?? DEFAULT_MUSIC

        if (!audioRef.value || currentSrc.value !== resolvedSrc) {
            // Create or recreate audio if needed
            if (audioRef.value) {
                audioRef.value.pause()
                audioRef.value = null
            }
            currentSrc.value       = resolvedSrc
            audioRef.value         = new Audio(resolvedSrc)
            audioRef.value.loop    = true
            audioRef.value.volume  = 0.4
            audioRef.value.preload = 'auto'
            audioRef.value.addEventListener('error', () => {
                audioRef.value   = null
                currentSrc.value = null
                musicOn.value    = false
                savePref(false)
            })
        }

        if (musicOn.value) {
            audioRef.value.pause()
            musicOn.value = false
        } else {
            try {
                await audioRef.value.play()
                musicOn.value = true
            } catch {
                musicOn.value = false
            }
        }
        savePref(musicOn.value)
    }

    const destroyAudio = () => {}

    return { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio }
}