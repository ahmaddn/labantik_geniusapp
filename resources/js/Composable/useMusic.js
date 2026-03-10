import { ref } from 'vue'

const STORAGE_KEY = 'geniuss_music_on'

const musicOn    = ref(localStorage.getItem(STORAGE_KEY) !== 'false')
const audioRef   = ref(null)
const currentSrc = ref(null) // ← track src yang sedang diputar

export function useMusic() {

    const savePref = (val) => localStorage.setItem(STORAGE_KEY, String(val))

    const handleVisibility = () => {
        if (!audioRef.value) return
        document.hidden
            ? audioRef.value.pause()
            : (musicOn.value && audioRef.value.play().catch(() => {}))
    }

    const initAutoMusic = async (src) => {
        if (!src) return

        // Src sama → lanjut play tanpa restart
        if (currentSrc.value === src) {
            if (musicOn.value && audioRef.value?.paused) {
                try { await audioRef.value.play() } catch {}
            }
            return
        }

        // Src beda → pause lama, buat baru
        if (audioRef.value) {
            audioRef.value.pause()
            audioRef.value = null
        }

        currentSrc.value   = src
        audioRef.value     = new Audio(src)
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
            document.addEventListener('click', async () => {
                if (!musicOn.value) return
                try { await audioRef.value?.play() } catch {}
            }, { once: true })
        }
    }

    const toggleMusic = async (src) => {
        if (!audioRef.value && src) {
            currentSrc.value   = src
            audioRef.value     = new Audio(src)
            audioRef.value.loop    = true
            audioRef.value.volume  = 0.4
            audioRef.value.preload = 'auto'
            audioRef.value.addEventListener('error', () => {
                audioRef.value   = null
                currentSrc.value = null
                musicOn.value    = false
            })
        }

        if (!audioRef.value) return

        if (musicOn.value) {
            audioRef.value.pause()
            musicOn.value = false
        } else {
            try { await audioRef.value.play(); musicOn.value = true }
            catch { musicOn.value = false }
        }
        savePref(musicOn.value)
    }

    const destroyAudio = () => {
        // Jangan destroy kalau src sama — biar lanjut di page berikutnya
        // Hanya reset listener, audio tetap jalan
    }

    return { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio }
}
