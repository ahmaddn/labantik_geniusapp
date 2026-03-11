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
            // Autoplay blocked → set false supaya tombol bisa diklik
            musicOn.value = false
            document.addEventListener('click', async (e) => {
                // Jangan trigger kalau user klik tombol toggle sendiri
                if (e.target.closest('.tbtn-sq')) return
                if (musicOn.value) return
                try { await audioRef.value?.play(); musicOn.value = true } catch {}
            }, { once: true })
        }
    }

    const toggleMusic = async (src) => {
    console.log('toggleMusic called', src)
    console.log('audioRef', audioRef.value)
    console.log('musicOn', musicOn.value)



        const resolvedSrc = src ?? DEFAULT_MUSIC
            console.log('resolvedSrc', resolvedSrc)

        if (!audioRef.value) {
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
        }

        if (musicOn.value) {
            audioRef.value.pause()
            musicOn.value = false
        } else {
            try { await audioRef.value.play(); musicOn.value = true }
            catch { musicOn.value = false }
        }
        savePref(musicOn.value)
    }

    const destroyAudio = () => {}

    return { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio }
}
