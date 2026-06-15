<script setup>
import { ref, onUnmounted, computed, reactive, watch } from 'vue'
import * as LucideIcons from 'lucide-vue-next'
import { BookOpen, Music, Video, CloudRain, Droplets } from 'lucide-vue-next'
import SimulationEffects from "@/Components/Simulation/SimulationEffects.vue";

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
})

const sliderValues = reactive({})

const conceptualData = computed(() => {
  if (['conceptual_systematic', 'learning_objectives', 'initial_questions', 'cover_page', 'image_comparison', 'process_list'].includes(props.question?.layout_type)) {
    try {
      return JSON.parse(props.question.content)
    } catch (e) {
      return null
    }
  }
  return null
})

const variables = computed(() => conceptualData.value?.variables || [])
const levels = computed(() => conceptualData.value?.levels || [])

// Initialize sliders when variables change
watch(
  variables,
  (newVars) => {
    if (newVars) {
      newVars.forEach((v, idx) => {
        if (sliderValues[idx] === undefined) {
          sliderValues[idx] = 1;
        }
      });
    }
  },
  { immediate: true, deep: true }
)

const dangerScore = computed(() => {
  let sum = 0;
  for (let key in sliderValues) {
    sum += sliderValues[key];
  }
  return sum;
});

const currentLevelData = computed(() => {
  if (!levels.value || levels.value.length === 0) return null;
  const maxPossibleScore = variables.value.length * 3;
  const minPossibleScore = variables.value.length * 1;
  if (maxPossibleScore === 0) return levels.value[0];
  let normalized = (dangerScore.value - minPossibleScore) / (maxPossibleScore - minPossibleScore || 1);
  let maxIndex = levels.value.length - 1;
  let index = Math.round(normalized * maxIndex);
  return levels.value[index];
});

const isDanger = computed(() => currentLevelData.value?.status === 'bahaya');
const isWarning = computed(() => currentLevelData.value?.status === 'waspada');

const statusColor = computed(() => {
  if (isDanger.value) return "text-red-600";
  if (isWarning.value) return "text-yellow-600";
  return "text-green-600";
});
const statusBg = computed(() => {
  if (isDanger.value) return "bg-red-100 border-red-300";
  if (isWarning.value) return "bg-yellow-100 border-yellow-300";
  return "bg-green-100 border-green-300";
});
const statusText = computed(() => {
  if (isDanger.value) return "BAHAYA";
  if (isWarning.value) return "WASPADA";
  return "AMAN / NORMAL";
});

const effectTranslations = {
    'none': '',
    'rain_light': 'Gerimis',
    'rain_heavy': 'Hujan Deras',
    'snow': 'Salju',
    'bubbles': 'Gelembung Air',
    'fire_sparks': 'Percikan Api',
    'wind_leaves': 'Daun Berterbangan',
    'dust': 'Debu / Polusi',
    'sunbeams': 'Cerah',
    'earthquake': 'Gempa'
};

const translatedEffect = computed(() => {
    const effect = currentLevelData.value?.animation_effect;
    if (!effect || effect === 'none') return '';
    return effectTranslations[effect] || effect;
});

const levelImage = computed(() => {
  if (currentLevelData.value?.image) {
    return `/storage/${currentLevelData.value.image}`;
  }
  return imageUrl(props.question.image);
});

const isVideo = (path) => {
  if (!path) return false
  const ext = path.split('?')[0].split('.').pop().toLowerCase()
  return ['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(ext)
}

const imageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const base = window.location.origin
  const clean = path.startsWith('/') ? path : `/storage/${path}`
  return `${base}${clean}`
}

// ── Canvas draw helper ──
const makeDrawLoop = (videoRef, canvasRef) => {
  let rafId = null
  const draw = () => {
    const video = videoRef.value
    const canvas = canvasRef.value
    if (!video || !canvas) return
    const ctx = canvas.getContext('2d')
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
    if (!video.paused && !video.ended) {
      rafId = requestAnimationFrame(draw)
    }
  }
  const start = () => {
    cancelAnimationFrame(rafId)
    draw()
  }
  const stop = () => {
    cancelAnimationFrame(rafId)
    // draw one last frame so it doesn't freeze on black
    const video = videoRef.value
    const canvas = canvasRef.value
    if (!video || !canvas) return
    const ctx = canvas.getContext('2d')
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  }
  const cleanup = () => cancelAnimationFrame(rafId)
  return { start, stop, cleanup }
}

// ── Banner player ──
const bannerVideoEl = ref(null)
const bannerCanvasEl = ref(null)
const bannerPlaying = ref(false)
const bannerLoop = ref(null)

const onBannerLoaded = () => {
  bannerLoop.value = makeDrawLoop(bannerVideoEl, bannerCanvasEl)
}

const toggleBannerPlay = () => {
  if (!bannerVideoEl.value) return
  if (bannerPlaying.value) {
    bannerVideoEl.value.pause()
  } else {
    bannerVideoEl.value.play()
  }
  bannerPlaying.value = !bannerPlaying.value
}

const onBannerPlay = () => {
  bannerPlaying.value = true
  bannerLoop.value?.start()
}
const onBannerPause = () => {
  bannerPlaying.value = false
  bannerLoop.value?.stop()
}
const onBannerSeeked = () => {
  // redraw immediately on seek so canvas doesn't lag
  const video = bannerVideoEl.value
  const canvas = bannerCanvasEl.value
  if (!video || !canvas) return
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  if (!video.paused) bannerLoop.value?.start()
}

// ── Content player ──
const videoEl = ref(null)
const canvasEl = ref(null)
const playing = ref(false)
const loop = ref(null)

const onLoaded = () => {
  loop.value = makeDrawLoop(videoEl, canvasEl)
}

const togglePlay = () => {
  if (!videoEl.value) return
  if (playing.value) {
    videoEl.value.pause()
  } else {
    videoEl.value.play()
  }
  playing.value = !playing.value
}

const onPlay = () => {
  playing.value = true
  loop.value?.start()
}
const onPause = () => {
  playing.value = false
  loop.value?.stop()
}
const onSeeked = () => {
  const video = videoEl.value
  const canvas = canvasEl.value
  if (!video || !canvas) return
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  if (!video.paused) loop.value?.start()
}

onUnmounted(() => {
  loop.value?.cleanup()
  bannerLoop.value?.cleanup()
})
</script>

<template>
  <div v-if="props.question?.layout_type === 'conceptual_systematic'" class="cs-container">
    <h2 class="cs-title uppercase">
        {{ props.question.title }}
    </h2>

    <div class="cs-grid">
      <!-- Left Texts -->
      <div class="cs-texts-left">
        <div class="cs-text-box">
          <p>{{ conceptualData?.topLeft }}</p>
          <div class="cs-arrow right"></div>
        </div>
        <div class="cs-text-box">
          <p>{{ conceptualData?.bottomLeft }}</p>
          <div class="cs-arrow right"></div>
        </div>
      </div>

      <!-- Center Image -->
      <div 
        class="cs-image-wrap relative"
        :class="{ 
            'animate-shake': currentLevelData?.animation_effect === 'earthquake'
        }"
      >
        <transition :name="currentLevelData?.image_transition !== 'none' ? 'magic-' + currentLevelData?.image_transition : ''">
            <img :key="levelImage" v-if="levelImage" :src="levelImage" alt="Concept" class="cs-center-img z-10" />
        </transition>
        
        <!-- Dynamic Effects Overlay -->
        <SimulationEffects :effect="currentLevelData?.animation_effect" />

        <!-- Status Badge Overlay -->
        <div
            v-if="currentLevelData"
            class="absolute top-4 right-4 z-30 px-3 py-1.5 rounded-xl font-bold border-2 shadow-lg backdrop-blur-md text-xs"
            :class="statusBg + ' ' + statusColor"
        >
            <span :class="statusColor">
                {{ statusText }}
                <template v-if="translatedEffect">[{{ translatedEffect }}]</template>
            </span>
        </div>
      </div>

      <!-- Right Texts -->
      <div class="cs-texts-right">
        <div class="cs-text-box">
          <div class="cs-arrow left"></div>
          <p>{{ conceptualData?.topRight }}</p>
        </div>
        <div class="cs-text-box">
          <div class="cs-arrow left"></div>
          <p>{{ conceptualData?.bottomRight }}</p>
        </div>
      </div>
    </div>

    <!-- Sliders Controls Area -->
    <div class="cs-slider-area flex flex-col gap-6 w-full max-w-xl mx-auto mb-6 mt-6">
      <div v-if="variables.length === 0" class="text-center text-slate-500 font-bold bg-white/40 p-4 rounded-xl w-full">
        Belum ada variabel penggeser.
      </div>
      
      <div v-for="(v, idx) in variables" :key="'v-'+idx" class="cs-slider-group bg-white/50 backdrop-blur p-4 rounded-2xl border border-white/40 shadow-sm w-full">
        <div class="flex justify-between items-center mb-3">
          <span class="font-extrabold text-blue-900 bg-blue-100/60 px-3 py-1 rounded-lg text-sm">
            {{ v.name || `Variabel ${idx + 1}` }}
          </span>
          <span class="font-bold text-slate-600 text-xs">
            {{
                sliderValues[idx] === 1
                    ? v.min_label
                    : sliderValues[idx] === 3
                      ? v.max_label
                      : "Sedang"
            }}
          </span>
        </div>
        <div class="relative w-full px-1 flex items-center gap-4">
          <span class="text-xs font-bold text-slate-500 min-w-[40px] text-right">{{ v.min_label || "Min" }}</span>
          <input
              type="range"
              min="1"
              max="3"
              step="1"
              v-model.number="sliderValues[idx]"
              class="cs-slider-custom w-full h-3 rounded-full appearance-none outline-none focus:ring-2 focus:ring-blue-300"
              :class="idx % 2 === 0 ? 'bg-blue-200 thumb-blue' : 'bg-green-200 thumb-green'"
          />
          <span class="text-xs font-bold text-slate-500 min-w-[40px] text-left">{{ v.max_label || "Max" }}</span>
        </div>
      </div>
    </div>

    <!-- Narration Box / Metrics Area -->
    <div v-if="currentLevelData" class="cs-metrics-area">
      <h3 class="font-extrabold text-blue-950 text-lg mb-1">
        {{ currentLevelData.level_name || "Amati Perubahan" }}
      </h3>
      <p class="text-slate-700 font-medium text-sm leading-relaxed mb-4 max-w-xl mx-auto">
        {{ currentLevelData.narration || "Ayo ubah penggeser di atas untuk melihat perbedaan dampaknya!" }}
      </p>
      
      <div
          v-if="currentLevelData.metric_value"
          class="inline-block px-4 py-1.5 bg-blue-50 text-blue-800 font-bold rounded-full border border-blue-200 text-xs shadow-sm"
      >
          {{ currentLevelData.metric_value }}
      </div>
    </div>
  </div>

  <div v-else-if="props.question?.layout_type === 'video_only'" class="vo-container w-full h-full flex flex-col items-center justify-center">
    <div class="vo-header text-center mb-6">
      <h2 class="vo-title text-3xl md:text-4xl font-heading font-black text-blue-900 drop-shadow-sm uppercase tracking-wide">
        VIDEO PEMBELAJARAN
      </h2>
    </div>
    
    <div class="vo-video-wrapper bg-white p-2 md:p-4 rounded-3xl shadow-xl mx-auto w-full max-w-4xl relative" style="aspect-ratio: 16/9;">
        <iframe 
            class="w-full h-full rounded-2xl bg-gray-100"
            :src="props.question.youtube_link + (props.question.youtube_link?.includes('?') ? '&autoplay=1' : '?autoplay=1')" 
            title="YouTube video player" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    </div>
  </div>

  <!-- Learning Objectives -->
  <div v-else-if="props.question?.layout_type === 'learning_objectives'" class="w-full max-w-4xl mx-auto p-4 md:p-8 bg-blue-50/50 rounded-3xl min-h-[60vh] flex flex-col justify-center">
    <div class="bg-white p-8 md:p-12 rounded-3xl shadow-xl shadow-blue-100/50 border-4 border-blue-200 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-100 rounded-full opacity-50"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-yellow-100 rounded-full opacity-50"></div>
        
        <h2 class="text-3xl md:text-5xl font-black text-blue-900 mb-8 text-center relative z-10 font-heading tracking-wide uppercase drop-shadow-sm">Tujuan Pembelajaran</h2>
        
        <ul class="space-y-4 md:space-y-6 relative z-10">
            <li v-for="(item, idx) in conceptualData" :key="idx" class="flex gap-4 md:gap-6 items-start bg-blue-50/80 p-4 md:p-6 rounded-2xl border-2 border-blue-100 hover:border-blue-300 transition-all hover:-translate-y-1">
                <div class="w-10 h-10 md:w-12 md:h-12 flex-shrink-0 bg-blue-500 text-white font-black text-xl md:text-2xl rounded-full flex items-center justify-center shadow-md border-4 border-blue-200">
                    {{ idx + 1 }}
                </div>
                <p class="text-lg md:text-xl font-bold text-slate-700 leading-relaxed pt-1 md:pt-2">{{ item }}</p>
            </li>
        </ul>
    </div>
  </div>

  <!-- Cover Page -->
  <div v-else-if="props.question?.layout_type === 'cover_page'" class="w-full max-w-5xl mx-auto min-h-[70vh] flex items-center justify-center relative">
    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 w-full rounded-[3rem] p-8 md:p-16 shadow-2xl shadow-indigo-200 overflow-hidden relative border-8 border-white">
        <!-- Deco elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-yellow-300 opacity-20 rounded-full translate-y-1/3 -translate-x-1/3 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-12">
            <div class="flex-1 text-center md:text-left text-white space-y-6">
                <div class="inline-block px-6 py-2 bg-white/20 backdrop-blur-md rounded-full border border-white/30 text-white font-bold text-sm md:text-base tracking-widest uppercase shadow-inner">
                    {{ conceptualData?.subtitle || 'Misi Pembelajaran' }}
                </div>
                <h1 class="text-5xl md:text-7xl font-black font-heading leading-tight drop-shadow-xl text-yellow-300" style="-webkit-text-stroke: 2px rgba(0,0,0,0.1);">
                    {{ props.question.title }}
                </h1>
            </div>
            
            <div class="w-64 h-64 md:w-96 md:h-96 flex-shrink-0 relative flex items-center justify-center">
                <!-- Glowing circle behind mascot -->
                <div class="absolute w-4/5 h-4/5 bg-white rounded-full opacity-20 blur-xl animate-pulse"></div>
                <img v-if="props.question.mascot" :src="`/storage/${props.question.mascot.image}`" class="w-full h-full object-contain relative z-10 filter drop-shadow-2xl animate-float" />
                <img v-else src="/images/mascot.png" class="w-full h-full object-contain relative z-10 filter drop-shadow-2xl animate-float" />
            </div>
        </div>
    </div>
  </div>

  <!-- Initial Questions -->
  <div v-else-if="props.question?.layout_type === 'initial_questions'" class="w-full max-w-4xl mx-auto min-h-[60vh] flex flex-col items-center justify-center">
    <div class="w-full flex flex-col md:flex-row items-center gap-8 md:gap-16">
        <div class="w-48 h-48 md:w-72 md:h-72 flex-shrink-0">
            <img v-if="props.question.mascot" :src="`/storage/${props.question.mascot.image}`" class="w-full h-full object-contain filter drop-shadow-xl animate-bounce-slow" />
            <img v-else src="/images/mascot.png" class="w-full h-full object-contain filter drop-shadow-xl animate-bounce-slow" />
        </div>
        
        <div class="flex-1 w-full space-y-6">
            <!-- Chat bubble style -->
            <div v-for="(q, idx) in conceptualData" :key="idx" class="relative bg-white p-6 md:p-8 rounded-3xl rounded-tl-none shadow-xl border-4 border-purple-200 transform transition-transform hover:-translate-y-2">
                <div class="absolute -left-4 top-0 w-8 h-8 bg-white border-t-4 border-l-4 border-purple-200 transform -skew-x-12"></div>
                <p class="text-xl md:text-2xl font-bold text-purple-900 leading-relaxed font-heading relative z-10">
                    {{ q }}
                </p>
            </div>
        </div>
    </div>
  </div>

  <!-- Image Comparison -->
  <div v-else-if="props.question?.layout_type === 'image_comparison'" class="w-full max-w-6xl mx-auto bg-green-50/50 p-6 md:p-10 rounded-3xl min-h-[60vh] flex flex-col justify-center">
    <h2 class="text-3xl md:text-5xl font-black text-green-900 text-center font-heading uppercase mb-10 drop-shadow-sm tracking-wide">
        {{ props.question.title }}
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
        <div class="bg-white p-4 md:p-6 rounded-3xl shadow-xl border-4 border-green-200 transform transition-transform hover:scale-[1.02]">
            <div class="h-64 md:h-96 w-full rounded-2xl overflow-hidden mb-6 bg-gray-100">
                <img v-if="conceptualData?.image_left" :src="`/storage/${conceptualData.image_left}`" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-bold">Gambar Kiri</div>
            </div>
            <div class="bg-green-100 py-3 px-6 rounded-xl border-2 border-green-300 text-center">
                <h3 class="text-xl md:text-2xl font-black text-green-800 uppercase tracking-widest">{{ conceptualData?.left_label || 'Gambar 1' }}</h3>
            </div>
        </div>
        
        <div class="bg-white p-4 md:p-6 rounded-3xl shadow-xl border-4 border-blue-200 transform transition-transform hover:scale-[1.02]">
            <div class="h-64 md:h-96 w-full rounded-2xl overflow-hidden mb-6 bg-gray-100">
                <img v-if="conceptualData?.image_right" :src="`/storage/${conceptualData.image_right}`" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-bold">Gambar Kanan</div>
            </div>
            <div class="bg-blue-100 py-3 px-6 rounded-xl border-2 border-blue-300 text-center">
                <h3 class="text-xl md:text-2xl font-black text-blue-800 uppercase tracking-widest">{{ conceptualData?.right_label || 'Gambar 2' }}</h3>
            </div>
        </div>
    </div>
  </div>

  <!-- Process List -->
  <div v-else-if="props.question?.layout_type === 'process_list'" class="w-full max-w-6xl mx-auto min-h-[60vh] flex flex-col justify-center py-8">
    <div class="bg-gradient-to-b from-sky-100 to-green-100 rounded-[3rem] p-6 md:p-12 border-8 border-white shadow-2xl relative overflow-hidden">
        <!-- Deco elements -->
        <div class="absolute bottom-0 left-0 w-full h-32 bg-[url('/images/grass-pattern.png')] bg-repeat-x bg-bottom opacity-50 z-0"></div>
        <div class="absolute top-10 right-10 w-32 h-32 bg-white rounded-full opacity-30 blur-xl"></div>
        
        <div class="flex flex-col md:flex-row gap-8 md:gap-12 relative z-10">
            <!-- Left Side: Image / Polaroid -->
            <div class="w-full md:w-5/12 flex-shrink-0 flex items-center justify-center transform -rotate-2 hover:rotate-0 transition-transform duration-300">
                <div class="bg-white p-4 pb-12 rounded-lg shadow-xl border border-gray-200 relative w-full max-w-sm">
                    <!-- Paper clip -->
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-16 border-4 border-gray-400 rounded-full bg-transparent transform rotate-12 z-20"></div>
                    <div class="w-full aspect-square bg-blue-50 overflow-hidden border border-gray-100">
                        <img v-if="props.question.image" :src="imageUrl(props.question.image)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">Gambar</div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: List -->
            <div class="w-full md:w-7/12 flex flex-col justify-center">
                <h2 class="text-3xl md:text-5xl font-black text-slate-700 font-heading uppercase mb-8 drop-shadow-sm leading-tight" style="-webkit-text-stroke: 1px rgba(255,255,255,0.5);">
                    {{ props.question.title }}
                </h2>
                
                <div class="space-y-4">
                    <div v-for="(item, idx) in conceptualData" :key="idx" class="flex items-center gap-4 group">
                        <div class="w-12 h-12 rounded-full bg-white border-4 border-slate-400 text-slate-700 font-black text-xl flex items-center justify-center shadow-md group-hover:border-blue-500 group-hover:text-blue-500 transition-colors flex-shrink-0">
                            {{ idx + 1 }}.
                        </div>
                        <div class="bg-white px-6 py-3 rounded-full shadow-md border-2 border-white group-hover:border-blue-200 w-full transition-all">
                            <span class="font-bold text-slate-700 text-lg md:text-xl uppercase tracking-wide group-hover:text-blue-800">{{ item }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div v-else class="mat-container">

    <!-- Image/Video Banner -->
    <div v-if="props.question?.image" class="mat-image-wrap">

      <!-- Banner: Video -->
      <div v-if="isVideo(props.question.image)" class="video-wrap">
        <div class="vid-inner">
          <canvas ref="bannerCanvasEl" class="mat-canvas-bg" width="640" height="360"></canvas>
          <video
            ref="bannerVideoEl"
            :src="imageUrl(props.question.image)"
            class="mat-video"
            controls
            @loadeddata="onBannerLoaded"
            @play="onBannerPlay"
            @pause="onBannerPause"
            @seeked="onBannerSeeked"
          ></video>
        </div>

        <div class="vid-controls">
          <button class="vid-play-btn" @click="toggleBannerPlay">
            <svg v-if="!bannerPlaying" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M8 5v14l11-7z"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
            {{ bannerPlaying ? 'Pause' : 'Play' }}
          </button>
        </div>
      </div>

      <!-- Banner: Image -->
      <img
        v-else
        :src="imageUrl(props.question.image)"
        :alt="props.question?.title || 'Materi'"
        class="mat-image"
      />
    </div>

    <!-- Header -->
    <div class="mat-header">
      <div class="mat-icon">
        <BookOpen v-if="!props.question?.material_type || props.question?.material_type === 'text'" :size="18" />
        <Video    v-else-if="props.question?.material_type === 'video'" :size="18" />
        <Music    v-else-if="props.question?.material_type === 'audio'" :size="18" />
      </div>
      <div class="mat-title-section">
        <h3 class="mat-title">{{ props.question?.title || 'Materi Pembelajaran' }}</h3>
        <p v-if="props.question?.subtitle" class="mat-subtitle">{{ props.question.subtitle }}</p>
      </div>
    </div>

    <div class="mat-divider"></div>

    <!-- Content -->
    <div class="mat-content">

      <!-- YouTube Embed -->
      <div v-if="props.question?.youtube_link" class="mb-6 rounded-2xl overflow-hidden shadow-lg border border-gray-200" style="aspect-ratio: 16/9;">
        <iframe
            :src="props.question.youtube_link"
            title="YouTube video player"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen
            class="w-full h-full"
        ></iframe>
      </div>

      <!-- Text -->
      <div v-if="!props.question?.material_type || props.question?.material_type === 'text'" class="mat-text">
        <div v-html="props.question?.content || 'Konten tidak tersedia'"></div>
      </div>

      <!-- Video -->
      <div v-else-if="props.question?.material_type === 'video'" class="mat-media">
        <div v-if="props.question?.content" class="video-wrap">
          <div class="vid-inner">
            <canvas ref="canvasEl" class="mat-canvas-bg" width="640" height="360"></canvas>
            <video
              ref="videoEl"
              :src="props.question.content"
              class="mat-video"
              controls
              @loadeddata="onLoaded"
              @play="onPlay"
              @pause="onPause"
              @seeked="onSeeked"
            ></video>
          </div>

          <div class="vid-controls">
            <button class="vid-play-btn" @click="togglePlay">
              <svg v-if="!playing" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M8 5v14l11-7z"/></svg>
              <svg v-else viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
              {{ playing ? 'Pause' : 'Amati Perbuatan' }}
            </button>
          </div>
        </div>
        <p v-else class="mat-error">Video tidak tersedia</p>
      </div>

      <!-- Audio -->
      <div v-else-if="props.question?.material_type === 'audio'" class="mat-media">
        <audio
          v-if="props.question?.content"
          :src="props.question.content"
          controls
          class="mat-audio"
        ></audio>
        <p v-else class="mat-error">Audio tidak ti9ersedia</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Conceptual Systematic Styles ── */
.cs-container {
  background: linear-gradient(180deg, #d3e9ff 0%, #b5dbff 100%);
  border-radius: 24px;
  padding: 2.5rem 2rem;
  box-shadow: 0 10px 30px rgba(0, 100, 200, 0.15);
  font-family: 'Nunito', 'Inter', sans-serif;
  overflow: hidden;
  position: relative;
  width: 100%;
}

.cs-title {
  text-align: center;
  font-size: 1.8rem;
  font-weight: 900;
  color: #1e3a5f;
  margin-bottom: 2.5rem;
  text-shadow: 0 2px 4px rgba(255, 255, 255, 0.5);
  letter-spacing: 0.5px;
}

.cs-grid {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
  margin-bottom: 3rem;
}

@media (min-width: 768px) {
  .cs-grid {
    flex-direction: row;
    justify-content: center;
    align-items: stretch;
    gap: 1.5rem;
  }
}

.cs-texts-left, .cs-texts-right {
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  gap: 1.5rem;
  flex: 1;
  max-width: 280px;
}

.cs-text-box {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  border-radius: 16px;
  padding: 1.25rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #334155;
  line-height: 1.5;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: relative;
  transition: transform 0.3s ease;
}

.cs-text-box:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.8);
}

.cs-arrow {
  display: none;
  position: absolute;
  top: 50%;
  width: 30px;
  height: 2px;
  background: #64748b;
}

@media (min-width: 768px) {
  .cs-arrow { display: block; }
  .cs-arrow.right { right: -30px; }
  .cs-arrow.left { left: -30px; }
  .cs-arrow::after {
    content: '';
    position: absolute;
    top: -4px;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
  }
  .cs-arrow.right::after { right: -5px; border-left: 6px solid #64748b; }
  .cs-arrow.left::after { left: -5px; border-right: 6px solid #64748b; }
}

.cs-image-wrap {
  flex: 0 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.cs-center-img {
  width: 100%;
  max-width: 280px;
  height: auto;
  object-fit: contain;
  border-radius: 20px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  background: #fff;
  padding: 10px;
}

/* ── Slider Area ── */
.cs-slider-area {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}

.cs-slider-wrap {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 100%;
  max-width: 600px;
  background: rgba(255, 255, 255, 0.4);
  padding: 1rem 1.5rem;
  border-radius: 100px;
}

.cs-slider-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.cs-slider-label {
  font-weight: 800;
  color: #1e3a5f;
  font-size: 0.95rem;
}

.cs-slider {
  flex: 1;
  -webkit-appearance: none;
  appearance: none;
  height: 12px;
  background: #e2e8f0;
  border-radius: 10px;
  outline: none;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.cs-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 0 0 4px #fff, 0 4px 8px rgba(0,0,0,0.2);
  transition: transform 0.1s;
}
.cs-slider::-webkit-slider-thumb:active { transform: scale(1.1); }

/* ── Metrics Area ── */
.cs-metrics-area {
  background: rgba(126, 191, 252, 0.4);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 2rem;
  text-align: center;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.cs-instruction {
  font-weight: 700;
  color: #1e40af;
  margin-bottom: 1.5rem;
  font-size: 1.05rem;
}

.cs-metrics-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  justify-content: center;
}

@media (min-width: 640px) {
  .cs-metrics-grid { flex-direction: row; }
}

.cs-metric-box {
  padding: 1.25rem 2rem;
  border-radius: 20px;
  color: white;
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  flex: 1;
  max-width: 280px;
  margin: 0 auto;
}

.metric-green {
  background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
  border: 3px solid #bef264;
}

.metric-blue {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border: 3px solid #93c5fd;
}

.cs-metric-title {
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0;
  text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.cs-metric-desc {
  font-size: 0.85rem;
  opacity: 0.9;
  margin: 0.25rem 0 0.75rem 0;
}

.cs-metric-value {
  background: rgba(255, 255, 255, 0.25);
  padding: 0.5rem;
  border-radius: 12px;
  font-weight: 900;
  font-size: 1.2rem;
  backdrop-filter: blur(4px);
}

/* ── Standard Material Styles ── */
.mat-container {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* ── Image Banner ── */
.mat-image-wrap {
  width: 100%;
  overflow: hidden;
  background: #f1f5f9;
}

.mat-image {
  width: 100%;
  max-height: 280px;
  object-fit: cover;
  display: block;
}

/* ── Header ── */
.mat-header {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  padding: 1.1rem;
  background: linear-gradient(90deg, #f8fafc, #f1f5f9);
}

.mat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: #dbeafe;
  border-radius: 10px;
  color: #0369a1;
  flex-shrink: 0;
}

.mat-title-section { flex: 1; text-align: left; }

.mat-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
}

.mat-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

/* ── Divider ── */
.mat-divider { height: 1px; background: #e2e8f0; }

/* ── Content ── */
.mat-content { padding: 1.25rem; background: #ffffff; }

.mat-text { line-height: 1.7; color: #475569; }

.mat-text p {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
  font-size: 0.95rem;
  max-width: 100%;
}

/* ── Mobile ── */
@media (max-width: 640px) {
  .mat-image { max-height: 200px; }
  .mat-header { padding: 0.85rem; gap: 0.65rem; }
  .mat-icon { width: 34px; height: 34px; }
  .mat-title { font-size: 0.875rem; }
  .mat-subtitle { font-size: 0.75rem; }
  .mat-content { padding: 0.9rem; }
  .mat-text p { font-size: 0.875rem; }
}

.mat-media {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  align-items: center;
}

/* ── Video Player ── */
.video-wrap { width: 100%; display: flex; flex-direction: column; }

.vid-inner {
  position: relative;
  width: 100%;
  max-height: 400px;
  overflow: hidden;
  background: #000;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Canvas blur background */
.mat-canvas-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: blur(18px) brightness(0.45) saturate(1.4);
  transform: scale(1.12);
  pointer-events: none;
  z-index: 0;
}

.cs-center-img {
  width: 100%; height: 100%;
  object-fit: contain;
  position: absolute;
  top: 0; left: 0;
}

/* Magic Transitions */
.magic-fade-enter-active, .magic-fade-leave-active { transition: opacity 0.8s ease-in-out; }
.magic-fade-enter-from, .magic-fade-leave-to { opacity: 0; }

.magic-zoom-fade-enter-active, .magic-zoom-fade-leave-active { transition: all 0.8s ease-in-out; }
.magic-zoom-fade-enter-from { opacity: 0; transform: scale(1.1); }
.magic-zoom-fade-leave-to { opacity: 0; transform: scale(0.9); }

.magic-slide-left-enter-active, .magic-slide-left-leave-active { transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
.magic-slide-left-enter-from { opacity: 0; transform: translateX(10%); }
.magic-slide-left-leave-to { opacity: 0; transform: translateX(-10%); }

.magic-slide-right-enter-active, .magic-slide-right-leave-active { transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
.magic-slide-right-enter-from { opacity: 0; transform: translateX(-10%); }
.magic-slide-right-leave-to { opacity: 0; transform: translateX(10%); }

.mat-video {
  position: relative;
  z-index: 1;
  width: 100%;
  max-height: 400px;
  object-fit: contain;
  display: block;
  background: transparent;
}

/* ── Controls ── */
.vid-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.vid-play-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 28px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: #fff;
  font-size: 13.5px;
  font-weight: 700;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(29,78,216,0.35);
  transition: all 0.18s cubic-bezier(0.34,1.56,0.64,1);
}
.vid-play-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(29,78,216,0.45); }
.vid-play-btn:active { transform: translateY(1px); }

/* ── Audio ── */
.mat-audio { width: 100%; max-width: 100%; }

.mat-error {
  text-align: center;
  color: #dc2626;
  font-weight: 600;
  margin: 1rem 0 0;
}

/* ── Earthquake & Slider Styles ── */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px) translateY(-2px) rotate(-1deg); }
    20%, 40%, 60%, 80% { transform: translateX(5px) translateY(2px) rotate(1deg); }
}
.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) infinite;
}

.cs-slider-custom {
  flex: 1;
  -webkit-appearance: none;
  appearance: none;
  height: 12px;
  background: #e2e8f0;
  border-radius: 10px;
  outline: none;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.cs-slider-custom::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 0 0 4px #fff, 0 4px 6px rgba(0,0,0,0.15);
  transition: transform 0.1s;
}
.cs-slider-custom::-webkit-slider-thumb:active { transform: scale(1.1); }

.thumb-blue::-webkit-slider-thumb { background: #3b82f6; }
.thumb-green::-webkit-slider-thumb { background: #10b981; }
</style>
