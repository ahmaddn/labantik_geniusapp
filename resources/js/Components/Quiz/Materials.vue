<script setup>
import { ref, onUnmounted, computed, reactive, watch } from "vue";
import * as LucideIcons from "lucide-vue-next";
import { BookOpen, Music, Video, CloudRain, Droplets } from "lucide-vue-next";
import SimulationEffects from "@/Components/Simulation/SimulationEffects.vue";

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const sliderValues = reactive({});

const conceptualData = computed(() => {
    if (
        [
            "conceptual_systematic",
            "learning_objectives",
            "initial_questions",
            "cover_page",
            "image_comparison",
            "process_list",
            "interactive_examples",
            "summary_list",
        ].includes(props.question?.layout_type)
    ) {
        try {
            return JSON.parse(props.question.content);
        } catch (e) {
            return null;
        }
    }
    return null;
});

const variables = computed(() => conceptualData.value?.variables || []);
const levels = computed(() => conceptualData.value?.levels || []);

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
    { immediate: true, deep: true },
);

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
    let normalized =
        (dangerScore.value - minPossibleScore) /
        (maxPossibleScore - minPossibleScore || 1);
    let maxIndex = levels.value.length - 1;
    let index = Math.round(normalized * maxIndex);
    return levels.value[index];
});

const isDanger = computed(() => currentLevelData.value?.status === "bahaya");
const isWarning = computed(() => currentLevelData.value?.status === "waspada");

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
    none: "",
    rain_light: "Gerimis",
    rain_heavy: "Hujan Deras",
    snow: "Salju",
    bubbles: "Gelembung Air",
    fire_sparks: "Percikan Api",
    wind_leaves: "Daun Berterbangan",
    dust: "Debu / Polusi",
    sunbeams: "Cerah",
    earthquake: "Gempa",
    confetti: "Konfeti",
    lightning: "Petir",
    stars: "Bintang",
    fog: "Kabut",
    clouds: "Berawan",
};

const translatedEffect = computed(() => {
    const effect = currentLevelData.value?.animation_effect;
    if (!effect || effect === "none") return "";
    return effectTranslations[effect] || effect;
});

const levelImage = computed(() => {
    if (currentLevelData.value?.image) {
        return `/storage/${currentLevelData.value.image}`;
    }
    return imageUrl(props.question.image);
});

const isVideo = (path) => {
    if (!path) return false;
    const ext = path.split("?")[0].split(".").pop().toLowerCase();
    return ["mp4", "webm", "ogg", "mov", "avi"].includes(ext);
};

const imageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith("http://") || path.startsWith("https://")) return path;
    const base = window.location.origin;
    const clean = path.startsWith("/") ? path : `/storage/${path}`;
    return `${base}${clean}`;
};

// ── Canvas draw helper ──
const makeDrawLoop = (videoRef, canvasRef) => {
    let rafId = null;
    const draw = () => {
        const video = videoRef.value;
        const canvas = canvasRef.value;
        if (!video || !canvas) return;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        if (!video.paused && !video.ended) {
            rafId = requestAnimationFrame(draw);
        }
    };
    const start = () => {
        cancelAnimationFrame(rafId);
        draw();
    };
    const stop = () => {
        cancelAnimationFrame(rafId);
        // draw one last frame so it doesn't freeze on black
        const video = videoRef.value;
        const canvas = canvasRef.value;
        if (!video || !canvas) return;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    };
    const cleanup = () => cancelAnimationFrame(rafId);
    return { start, stop, cleanup };
};

// ── Banner player ──
const bannerVideoEl = ref(null);
const bannerCanvasEl = ref(null);
const bannerPlaying = ref(false);
const bannerLoop = ref(null);

const onBannerLoaded = () => {
    bannerLoop.value = makeDrawLoop(bannerVideoEl, bannerCanvasEl);
};

const toggleBannerPlay = () => {
    if (!bannerVideoEl.value) return;
    if (bannerPlaying.value) {
        bannerVideoEl.value.pause();
    } else {
        bannerVideoEl.value.play();
    }
    bannerPlaying.value = !bannerPlaying.value;
};

const onBannerPlay = () => {
    bannerPlaying.value = true;
    bannerLoop.value?.start();
};
const onBannerPause = () => {
    bannerPlaying.value = false;
    bannerLoop.value?.stop();
};
const onBannerSeeked = () => {
    // redraw immediately on seek so canvas doesn't lag
    const video = bannerVideoEl.value;
    const canvas = bannerCanvasEl.value;
    if (!video || !canvas) return;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    if (!video.paused) bannerLoop.value?.start();
};

// ── Content player ──
const videoEl = ref(null);
const canvasEl = ref(null);
const playing = ref(false);
const loop = ref(null);

const onLoaded = () => {
    loop.value = makeDrawLoop(videoEl, canvasEl);
};

const togglePlay = () => {
    if (!videoEl.value) return;
    if (playing.value) {
        videoEl.value.pause();
    } else {
        videoEl.value.play();
    }
    playing.value = !playing.value;
};

const onPlay = () => {
    playing.value = true;
    loop.value?.start();
};
const onPause = () => {
    playing.value = false;
    loop.value?.stop();
};
const onSeeked = () => {
    const video = videoEl.value;
    const canvas = canvasEl.value;
    if (!video || !canvas) return;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    if (!video.paused) loop.value?.start();
};

onUnmounted(() => {
    loop.value?.cleanup();
    bannerLoop.value?.cleanup();
});
</script>

<template>
    <div
        v-if="props.question?.layout_type === 'conceptual_systematic'"
        class="w-full flex flex-col items-center py-2 lg:py-6 px-1 lg:px-2 gap-4 lg:gap-8"
    >
        <h2
            class="text-xl lg:text-3xl font-black text-[#1cb0f6] uppercase tracking-wide drop-shadow-sm text-center"
            style="font-family: &quot;Nunito&quot;, sans-serif"
        >
            {{ props.question.title }}
        </h2>

        <!-- DESKTOP LAYOUT (lg and up) -->
        <div
            class="hidden lg:flex w-full max-w-5xl flex-col items-center justify-center gap-8 relative z-10"
        >
            <!-- Top Row: Concept Cards & Polaroid Centerpiece -->
            <div
                class="w-full flex flex-row items-center justify-center gap-12"
            >
                <!-- Left Texts -->
                <div
                    class="flex flex-col gap-5 w-3/12 items-center lg:items-end"
                >
                    <div
                        class="bg-white rounded-[20px] border-2 border-gray-200 border-b-[6px] p-4 w-full max-w-[280px] shadow-sm transform transition-transform hover:-translate-y-1 relative flex flex-row items-center gap-3 min-h-[90px]"
                    >
                        <span
                            class="w-7 h-7 rounded-full bg-blue-100 text-[#1cb0f6] flex-shrink-0 flex items-center justify-center text-[12px] font-black"
                            >1</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[13px] md:text-[14px] break-words leading-snug text-left"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.topLeft }}
                        </p>
                        <!-- Arrow pointing right -->
                        <div
                            class="absolute top-1/2 -right-6 w-6 h-[3px] bg-gray-300 -translate-y-1/2 after:content-[''] after:absolute after:right-[-6px] after:top-[-4.5px] after:border-t-[6px] after:border-b-[6px] after:border-l-[8px] after:border-t-transparent after:border-b-transparent after:border-l-gray-300"
                        ></div>
                    </div>
                    <div
                        class="bg-white rounded-[20px] border-2 border-gray-200 border-b-[6px] p-4 w-full max-w-[280px] shadow-sm transform transition-transform hover:-translate-y-1 relative flex flex-row items-center gap-3 min-h-[90px]"
                    >
                        <span
                            class="w-7 h-7 rounded-full bg-green-100 text-[#78c257] flex-shrink-0 flex items-center justify-center text-[12px] font-black"
                            >2</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[13px] md:text-[14px] break-words leading-snug text-left"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.bottomLeft }}
                        </p>
                        <div
                            class="absolute top-1/2 -right-6 w-6 h-[3px] bg-gray-300 -translate-y-1/2 after:content-[''] after:absolute after:right-[-6px] after:top-[-4.5px] after:border-t-[6px] after:border-b-[6px] after:border-l-[8px] after:border-t-transparent after:border-b-transparent after:border-l-gray-300"
                        ></div>
                    </div>
                </div>

                <!-- Center Image (Polaroid Style) -->
                <div
                    class="w-4/12 max-w-[320px] flex-shrink-0 flex items-center justify-center relative"
                >
                    <!-- Background Polaroid -->
                    <div
                        class="absolute w-full bg-white p-3.5 pb-12 shadow-md border border-gray-200 transform -rotate-3 z-0 translate-y-2 translate-x-2"
                    >
                        <div
                            class="w-full aspect-[4/5] bg-gray-50 border border-gray-100"
                        ></div>
                    </div>

                    <!-- Foreground Polaroid -->
                    <div
                        class="bg-white p-3.5 pb-12 shadow-lg border border-gray-200 relative w-full z-10 transform rotate-1 transition-transform hover:rotate-0 duration-300"
                        :class="{
                            'animate-shake':
                                currentLevelData?.animation_effect ===
                                'earthquake',
                        }"
                    >
                        <div
                            class="w-full aspect-[4/5] bg-blue-50 overflow-hidden border border-gray-100 relative flex items-center justify-center"
                        >
                            <transition
                                :name="
                                    currentLevelData?.image_transition !==
                                    'none'
                                        ? 'magic-' +
                                          currentLevelData?.image_transition
                                        : ''
                                "
                            >
                                <img
                                    :key="levelImage"
                                    v-if="levelImage"
                                    :src="levelImage"
                                    alt="Concept"
                                    class="w-full h-full object-cover z-10 relative"
                                />
                                <div
                                    v-else
                                    class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-50"
                                >
                                    <LucideIcons.Image
                                        class="w-12 h-12 mb-2 opacity-50"
                                    />
                                    <span
                                        class="text-sm font-bold"
                                        style="
                                            font-family:
                                                &quot;Nunito&quot;, sans-serif;
                                        "
                                        >Gambar Utama</span
                                    >
                                </div>
                            </transition>

                            <!-- Dynamic Effects Overlay -->
                            <SimulationEffects
                                :effect="currentLevelData?.animation_effect"
                            />
                        </div>

                        <!-- Status Badge Overlay (inside polaroid) -->
                        <div
                            v-if="currentLevelData"
                            class="absolute top-0 right-0 z-30 px-3.5 py-2 rounded-bl-xl font-black border-l-2 border-b-2 shadow-sm text-[11px] tracking-wider uppercase"
                            :class="statusBg + ' ' + statusColor"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            <span :class="statusColor">
                                {{ statusText }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Texts -->
                <div
                    class="flex flex-col gap-5 w-3/12 items-center lg:items-start"
                >
                    <div
                        class="bg-white rounded-[20px] border-2 border-gray-200 border-b-[6px] p-4 w-full max-w-[280px] shadow-sm transform transition-transform hover:-translate-y-1 relative flex flex-row items-center gap-3 min-h-[90px]"
                    >
                        <div
                            class="absolute top-1/2 -left-6 w-6 h-[3px] bg-gray-300 -translate-y-1/2 after:content-[''] after:absolute after:left-[-6px] after:top-[-4.5px] after:border-t-[6px] after:border-b-[6px] after:border-r-[8px] after:border-t-transparent after:border-b-transparent after:border-r-gray-300"
                        ></div>
                        <span
                            class="w-7 h-7 rounded-full bg-yellow-100 text-[#ffc800] flex-shrink-0 flex items-center justify-center text-[12px] font-black"
                            >3</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[13px] md:text-[14px] break-words leading-snug text-left"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.topRight }}
                        </p>
                    </div>
                    <div
                        class="bg-white rounded-[20px] border-2 border-gray-200 border-b-[6px] p-4 w-full max-w-[280px] shadow-sm transform transition-transform hover:-translate-y-1 relative flex flex-row items-center gap-3 min-h-[90px]"
                    >
                        <div
                            class="absolute top-1/2 -left-6 w-6 h-[3px] bg-gray-300 -translate-y-1/2 after:content-[''] after:absolute after:left-[-6px] after:top-[-4.5px] after:border-t-[6px] after:border-b-[6px] after:border-r-[8px] after:border-t-transparent after:border-b-transparent after:border-r-gray-300"
                        ></div>
                        <span
                            class="w-7 h-7 rounded-full bg-red-100 text-[#ff847c] flex-shrink-0 flex items-center justify-center text-[12px] font-black"
                            >4</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[13px] md:text-[14px] break-words leading-snug text-left"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.bottomRight }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Dashboard Row: Split-screen for Narration & Sliders (prevents vertical scroll) -->
            <div class="w-full grid grid-cols-2 gap-8 items-stretch mt-4">
                <!-- Column 1: Narration Box / Metrics Area -->
                <div
                    v-if="currentLevelData"
                    class="bg-[#f0f9ff] rounded-[24px] border-2 border-[#bae6fd] border-b-[6px] p-6 shadow-sm flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-2.5 h-2.5 rounded-full bg-[#1cb0f6]"
                            ></span>
                            <h3
                                class="font-black text-[#2f3542] text-lg uppercase tracking-wide"
                                style="
                                    font-family: &quot;Nunito&quot;, sans-serif;
                                "
                            >
                                {{
                                    currentLevelData.level_name ||
                                    "Amati Perubahan"
                                }}
                            </h3>
                        </div>
                        <p
                            class="text-[#5b738b] font-bold text-[15px] md:text-[16px] leading-relaxed break-words"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{
                                currentLevelData.narration ||
                                "Ayo ubah penggeser untuk melihat perbedaan dampaknya!"
                            }}
                        </p>
                    </div>

                    <div v-if="currentLevelData.metric_value">
                        <div
                            class="inline-block px-5 py-2.5 bg-white text-[#1cb0f6] font-black rounded-[16px] border-[3px] border-[#1cb0f6] text-[13px] uppercase tracking-widest shadow-sm"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ currentLevelData.metric_value }}
                        </div>
                    </div>
                </div>

                <!-- Column 2: Sliders Controls Area -->
                <div
                    class="flex flex-col justify-center gap-4 bg-white p-6 rounded-[24px] border-2 border-gray-200 border-b-[6px] shadow-sm"
                >
                    <div
                        v-if="variables.length === 0"
                        class="text-center text-gray-400 font-bold bg-gray-50 p-6 rounded-xl w-full border-2 border-dashed border-gray-100"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        Belum ada variabel penggeser.
                    </div>

                    <div
                        v-for="(v, idx) in variables"
                        :key="'v-' + idx"
                        class="w-full flex flex-col gap-2.5"
                    >
                        <div class="flex justify-between items-center px-1">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="w-2 h-2 rounded-full bg-[#1cb0f6]"
                                ></span>
                                <span
                                    class="font-black text-[#2f3542] text-[12px] uppercase tracking-wide"
                                >
                                    {{ v.name || `Variabel ${idx + 1}` }}
                                </span>
                            </div>
                            <span
                                class="font-black text-[#1cb0f6] bg-[#f0f9ff] px-3 py-0.5 rounded-full text-[10px] uppercase tracking-wide border border-[#bae6fd]"
                            >
                                {{
                                    sliderValues[idx] === 1
                                        ? v.min_label
                                        : sliderValues[idx] === 3
                                          ? v.max_label
                                          : "Sedang"
                                }}
                            </span>
                        </div>

                        <div
                            class="flex items-center gap-3 bg-gray-50 p-2.5 rounded-xl border border-gray-100"
                        >
                            <span
                                class="text-[11px] font-bold text-gray-400 uppercase w-16 text-right truncate"
                                >{{ v.min_label || "Min" }}</span
                            >
                            <input
                                type="range"
                                min="1"
                                max="3"
                                step="1"
                                v-model.number="sliderValues[idx]"
                                class="cs-slider flex-1 h-5"
                            />
                            <span
                                class="text-[11px] font-bold text-gray-400 uppercase w-16 text-left truncate"
                                >{{ v.max_label || "Max" }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOBILE LAYOUT (less than lg, optimized max-width for small screens & tablets) -->
        <div
            class="flex lg:hidden w-full max-w-[480px] mx-auto flex-col gap-4 relative z-10"
        >
            <!-- Unified Card: Polaroid & Narration Box -->
            <div
                class="bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-4 shadow-md flex flex-col gap-3.5"
            >
                <div class="flex items-center gap-4">
                    <!-- Polaroid Image (Compact) -->
                    <div
                        class="w-[85px] sm:w-[105px] flex-shrink-0 flex items-center justify-center relative"
                    >
                        <!-- Background Polaroid -->
                        <div
                            class="absolute inset-0 bg-white p-1 pb-3 shadow-sm border border-gray-200 transform -rotate-2 z-0 translate-y-1 translate-x-1 rounded-sm"
                        >
                            <div
                                class="w-full aspect-[4/5] bg-gray-50 border border-gray-100"
                            ></div>
                        </div>
                        <!-- Foreground Polaroid -->
                        <div
                            class="bg-white p-1 pb-3 shadow border border-gray-200 relative w-full z-10 transform rotate-1 rounded-sm"
                            :class="{
                                'animate-shake':
                                    currentLevelData?.animation_effect ===
                                    'earthquake',
                            }"
                        >
                            <div
                                class="w-full aspect-[4/5] bg-blue-50 overflow-hidden border border-gray-100 relative flex items-center justify-center"
                            >
                                <transition
                                    :name="
                                        currentLevelData?.image_transition !==
                                        'none'
                                            ? 'magic-' +
                                              currentLevelData?.image_transition
                                            : ''
                                    "
                                >
                                    <img
                                        :key="levelImage"
                                        v-if="levelImage"
                                        :src="levelImage"
                                        alt="Concept"
                                        class="w-full h-full object-cover z-10 relative"
                                    />
                                    <div
                                        v-else
                                        class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 bg-gray-50"
                                    >
                                        <LucideIcons.Image
                                            class="w-6 h-6 mb-1 opacity-50"
                                        />
                                    </div>
                                </transition>
                                <SimulationEffects
                                    :effect="currentLevelData?.animation_effect"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Narration Box & Status Badges -->
                    <div
                        class="flex-1 flex flex-col gap-1.5 max-h-[140px] overflow-y-auto"
                    >
                        <div class="flex flex-wrap items-center gap-1.5">
                            <!-- Level Name / Badge -->
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase border border-current shadow-sm"
                                :class="statusBg + ' ' + statusColor"
                            >
                                {{ statusText }}
                            </span>
                        </div>
                        <h3
                            class="font-black text-[#2f3542] text-sm sm:text-base leading-tight"
                        >
                            {{
                                currentLevelData?.level_name ||
                                "Amati Perubahan"
                            }}
                        </h3>
                        <p
                            class="text-[#5b738b] font-bold text-[11px] sm:text-[12px] leading-snug break-words"
                        >
                            {{
                                currentLevelData?.narration ||
                                "Ubah penggeser di bawah!"
                            }}
                        </p>
                    </div>
                </div>

                <!-- Metric Value Alert (if exists) -->
                <div
                    v-if="currentLevelData?.metric_value"
                    class="w-full bg-[#f0f9ff] border-2 border-[#1cb0f6] rounded-[16px] py-2 px-3 text-center text-[#1cb0f6] font-black text-[11px] sm:text-xs uppercase tracking-wider shadow-inner"
                >
                    {{ currentLevelData.metric_value }}
                </div>
            </div>

            <!-- Static Concepts (Horizontal scroll bar with colorful markers) -->
            <div class="w-full flex flex-col gap-1.5">
                <span
                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest px-1"
                    >Gagasan Utama / Konseptual</span
                >
                <div
                    class="w-full flex flex-row overflow-x-auto gap-2.5 pb-2 px-1 scrollbar-none snap-x"
                    style="-webkit-overflow-scrolling: touch"
                >
                    <div
                        v-if="conceptualData?.topLeft"
                        class="flex-shrink-0 w-[140px] snap-center bg-white rounded-[16px] border-2 border-gray-200 border-b-[4px] p-2.5 text-center flex flex-col items-center justify-center min-h-[60px] shadow-sm transform transition-transform hover:-translate-y-0.5"
                    >
                        <span
                            class="w-4 h-4 rounded-full bg-blue-100 text-[#1cb0f6] flex items-center justify-center text-[9px] font-black mb-1"
                            >1</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[10px] sm:text-[11px] break-words leading-tight"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.topLeft }}
                        </p>
                    </div>
                    <div
                        v-if="conceptualData?.bottomLeft"
                        class="flex-shrink-0 w-[140px] snap-center bg-white rounded-[16px] border-2 border-gray-200 border-b-[4px] p-2.5 text-center flex flex-col items-center justify-center min-h-[60px] shadow-sm transform transition-transform hover:-translate-y-0.5"
                    >
                        <span
                            class="w-4 h-4 rounded-full bg-green-100 text-[#78c257] flex items-center justify-center text-[9px] font-black mb-1"
                            >2</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[10px] sm:text-[11px] break-words leading-tight"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.bottomLeft }}
                        </p>
                    </div>
                    <div
                        v-if="conceptualData?.topRight"
                        class="flex-shrink-0 w-[140px] snap-center bg-white rounded-[16px] border-2 border-gray-200 border-b-[4px] p-2.5 text-center flex flex-col items-center justify-center min-h-[60px] shadow-sm transform transition-transform hover:-translate-y-0.5"
                    >
                        <span
                            class="w-4 h-4 rounded-full bg-yellow-100 text-[#ffc800] flex items-center justify-center text-[9px] font-black mb-1"
                            >3</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[10px] sm:text-[11px] break-words leading-tight"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.topRight }}
                        </p>
                    </div>
                    <div
                        v-if="conceptualData?.bottomRight"
                        class="flex-shrink-0 w-[140px] snap-center bg-white rounded-[16px] border-2 border-gray-200 border-b-[4px] p-2.5 text-center flex flex-col items-center justify-center min-h-[60px] shadow-sm transform transition-transform hover:-translate-y-0.5"
                    >
                        <span
                            class="w-4 h-4 rounded-full bg-red-100 text-[#ff847c] flex items-center justify-center text-[9px] font-black mb-1"
                            >4</span
                        >
                        <p
                            class="font-bold text-[#5b738b] text-[10px] sm:text-[11px] break-words leading-tight"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ conceptualData?.bottomRight }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Slim Sliders Control Room -->
            <div class="w-full flex flex-col gap-2.5">
                <div
                    v-for="(v, idx) in variables"
                    :key="'mv-' + idx"
                    class="w-full bg-white p-3 rounded-[20px] border-2 border-gray-200 border-b-[4px] shadow-sm flex flex-col gap-2"
                >
                    <div class="flex justify-between items-center px-1">
                        <div class="flex items-center gap-1.5">
                            <span
                                class="w-2 h-2 rounded-full bg-[#1cb0f6]"
                            ></span>
                            <span
                                class="font-black text-[#2f3542] text-[11px] uppercase tracking-wide"
                            >
                                {{ v.name }}
                            </span>
                        </div>
                        <span
                            class="font-black text-[#1cb0f6] bg-[#f0f9ff] px-2.5 py-0.5 rounded-full text-[9px] uppercase tracking-wide border border-[#bae6fd]"
                        >
                            {{
                                sliderValues[idx] === 1
                                    ? v.min_label
                                    : sliderValues[idx] === 3
                                      ? v.max_label
                                      : "Sedang"
                            }}
                        </span>
                    </div>
                    <div
                        class="flex items-center gap-2.5 bg-gray-50 p-2 rounded-xl border border-gray-100"
                    >
                        <span
                            class="text-[9px] font-bold text-gray-400 uppercase w-12 text-right truncate"
                            >{{ v.min_label || "Min" }}</span
                        >
                        <input
                            type="range"
                            min="1"
                            max="3"
                            step="1"
                            v-model.number="sliderValues[idx]"
                            class="cs-slider flex-1 h-5"
                        />
                        <span
                            class="text-[9px] font-bold text-gray-400 uppercase w-12 text-left truncate"
                            >{{ v.max_label || "Max" }}</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        v-else-if="props.question?.layout_type === 'video_only'"
        class="vo-container w-full h-full flex flex-col items-center justify-center"
    >
        <div class="vo-header text-center mb-6">
            <h2
                class="vo-title text-3xl md:text-4xl font-heading font-black text-blue-900 drop-shadow-sm uppercase tracking-wide"
            >
                VIDEO PEMBELAJARAN
            </h2>
        </div>

        <div
            class="vo-video-wrapper bg-white p-2 md:p-4 rounded-3xl shadow-xl mx-auto w-full max-w-4xl relative"
            style="aspect-ratio: 16/9"
        >
            <iframe
                class="w-full h-full rounded-2xl bg-gray-100"
                :src="
                    props.question.youtube_link +
                    (props.question.youtube_link?.includes('?')
                        ? '&autoplay=1'
                        : '?autoplay=1')
                "
                title="YouTube video player"
                frameborder="0"
                allow="
                    accelerometer;
                    autoplay;
                    clipboard-write;
                    encrypted-media;
                    gyroscope;
                    picture-in-picture;
                "
                allowfullscreen
            >
            </iframe>
        </div>
    </div>

    <!-- Learning Objectives -->
    <div
        v-else-if="props.question?.layout_type === 'learning_objectives'"
        class="w-full flex flex-col justify-center py-6 px-2"
    >
        <!-- Title -->
        <h2
            class="text-2xl md:text-3xl font-black text-[#1cb0f6] uppercase mb-6 md:mb-8 tracking-wide drop-shadow-sm text-center"
            style="font-family: &quot;Nunito&quot;, sans-serif"
        >
            {{ props.question.title || "Tujuan Pembelajaran" }}
        </h2>

        <!-- List -->
        <div class="w-full max-w-3xl mx-auto space-y-4 md:space-y-5">
            <div
                v-for="(item, idx) in conceptualData"
                :key="idx"
                class="flex items-start md:items-center gap-0 group"
            >
                <!-- Number Circle -->
                <div
                    class="w-10 h-10 md:w-11 md:h-11 rounded-full bg-[#1cb0f6] border-[3px] border-[#1899d6] text-white font-black text-lg flex items-center justify-center shadow-sm z-10 flex-shrink-0 mt-1 md:mt-0"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ idx + 1 }}
                </div>
                <!-- Text Box -->
                <div
                    class="bg-white px-5 py-3 md:py-3.5 rounded-[1.25rem] md:rounded-full shadow-sm border-[3px] border-[#1cb0f6] w-full -ml-5 pl-8 md:pl-9 transition-colors group-hover:bg-[#f0f9ff] min-h-[48px] md:min-h-[52px] flex items-center"
                >
                    <span
                        class="font-bold text-[#5b738b] text-[13px] md:text-base tracking-wide block w-full leading-relaxed break-words"
                        style="
                            font-family: &quot;Nunito&quot;, sans-serif;
                            word-break: break-word;
                        "
                        >{{ item }}</span
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Page -->
    <div
        v-else-if="props.question?.layout_type === 'cover_page'"
        class="w-full flex flex-col justify-center items-center py-4 px-2"
    >
        <div
            class="w-full max-w-3xl bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] overflow-hidden flex flex-col shadow-sm text-center"
        >
            <!-- Optional Image -->
            <img
                v-if="props.question.image"
                :src="imageUrl(props.question.image)"
                class="w-full h-48 md:h-64 object-cover border-b-2 border-gray-100"
            />
            <div class="p-8 md:p-12 flex flex-col items-center">
                <span
                    class="inline-block px-4 py-1.5 bg-[#f0f9ff] text-[#1cb0f6] font-black text-xs md:text-sm uppercase tracking-widest rounded-xl border-2 border-[#bae6fd] mb-4"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ conceptualData?.subtitle || "Pendahuluan" }}
                </span>
                <h1
                    class="text-3xl md:text-5xl font-black text-[#5b738b] uppercase tracking-wide leading-tight"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ props.question.title }}
                </h1>
            </div>
        </div>
    </div>

    <!-- Initial Questions -->
    <div
        v-else-if="props.question?.layout_type === 'initial_questions'"
        class="w-full flex flex-col justify-center items-center py-6 px-2"
    >
        <h2
            class="text-2xl md:text-3xl font-black text-[#1cb0f6] uppercase mb-6 md:mb-8 tracking-wide drop-shadow-sm text-center"
            style="font-family: &quot;Nunito&quot;, sans-serif"
        >
            {{ props.question.title || "Pertanyaan Awal" }}
        </h2>
        <div class="w-full max-w-3xl space-y-4">
            <div
                v-for="(q, idx) in conceptualData"
                :key="idx"
                class="bg-white p-5 md:p-6 rounded-[24px] shadow-sm border-2 border-gray-200 border-b-[6px] transform transition-transform hover:-translate-y-1"
            >
                <p
                    class="text-base md:text-lg font-bold text-[#5b738b] leading-relaxed"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ q }}
                </p>
            </div>
        </div>
    </div>

    <!-- Image Comparison -->
    <div
        v-else-if="props.question?.layout_type === 'image_comparison'"
        class="w-full flex flex-col justify-center items-center py-6 px-2"
    >
        <h2
            class="text-2xl md:text-3xl font-black text-[#1cb0f6] uppercase mb-6 md:mb-8 tracking-wide drop-shadow-sm text-center"
            style="font-family: &quot;Nunito&quot;, sans-serif"
        >
            {{ props.question.title }}
        </h2>

        <div
            class="w-full max-w-4xl grid grid-cols-1 sm:grid-cols-2 gap-6 md:gap-8"
        >
            <!-- Left Image -->
            <div
                class="bg-white p-3 md:p-4 rounded-[24px] shadow-sm border-2 border-gray-200 border-b-[6px]"
            >
                <div
                    class="aspect-[4/3] w-full rounded-[16px] overflow-hidden mb-4 bg-gray-50 border-2 border-gray-100 relative"
                >
                    <img
                        v-if="conceptualData?.image_left"
                        :src="`/storage/${conceptualData.image_left}`"
                        class="w-full h-full object-cover"
                    />
                    <div
                        v-else
                        class="w-full h-full flex flex-col items-center justify-center text-gray-400 font-bold"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        <LucideIcons.Image class="w-8 h-8 mb-2 opacity-50" />
                        Gambar Kiri
                    </div>
                </div>
                <div
                    class="bg-[#f0f9ff] py-2 px-4 rounded-xl border-2 border-[#bae6fd] text-center"
                >
                    <h3
                        class="text-sm md:text-base font-black text-[#1cb0f6] uppercase tracking-widest"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        {{ conceptualData?.left_label || "Gambar 1" }}
                    </h3>
                </div>
            </div>

            <!-- Right Image -->
            <div
                class="bg-white p-3 md:p-4 rounded-[24px] shadow-sm border-2 border-gray-200 border-b-[6px]"
            >
                <div
                    class="aspect-[4/3] w-full rounded-[16px] overflow-hidden mb-4 bg-gray-50 border-2 border-gray-100 relative"
                >
                    <img
                        v-if="conceptualData?.image_right"
                        :src="`/storage/${conceptualData.image_right}`"
                        class="w-full h-full object-cover"
                    />
                    <div
                        v-else
                        class="w-full h-full flex flex-col items-center justify-center text-gray-400 font-bold"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        <LucideIcons.Image class="w-8 h-8 mb-2 opacity-50" />
                        Gambar Kanan
                    </div>
                </div>
                <div
                    class="bg-[#fdf0f0] py-2 px-4 rounded-xl border-2 border-[#fecdd3] text-center"
                >
                    <h3
                        class="text-sm md:text-base font-black text-[#fb7185] uppercase tracking-widest"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        {{ conceptualData?.right_label || "Gambar 2" }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Process List -->
    <div
        v-else-if="props.question?.layout_type === 'process_list'"
        class="w-full mx-auto flex flex-col justify-center py-4 px-2"
    >
        <div
            class="flex flex-col sm:flex-row gap-6 md:gap-12 relative z-10 w-full max-w-5xl mx-auto items-center md:items-start px-4"
        >
            <!-- Left Side: Stacked Polaroids -->
            <div
                class="w-10/12 sm:w-5/12 max-w-[260px] md:max-w-[360px] lg:max-w-[400px] flex-shrink-0 flex items-center justify-center relative mt-6 md:mt-8 mx-auto"
            >
                <!-- Background Polaroid (Rotated) -->
                <div
                    class="absolute w-full bg-white p-2.5 md:p-3.5 lg:p-4 pb-8 md:pb-12 lg:pb-14 shadow-md border border-gray-200 transform -rotate-6 z-0 translate-y-2 translate-x-2 md:translate-y-3 md:translate-x-3"
                >
                    <div
                        class="w-full aspect-square bg-gray-50 border border-gray-100"
                    ></div>
                </div>

                <!-- Foreground Polaroid -->
                <div
                    class="bg-white p-2.5 md:p-3.5 lg:p-4 pb-8 md:pb-12 lg:pb-14 shadow-lg border border-gray-200 relative w-full z-10 transform rotate-2 transition-transform hover:rotate-0 duration-300"
                >
                    <!-- Paper clip -->
                    <div
                        class="absolute -top-5 md:-top-6 left-1/2 -translate-x-1/2 w-7 h-12 md:w-9 md:h-16 border-[3px] border-gray-400 rounded-full bg-transparent z-20"
                    >
                        <div
                            class="absolute top-1 left-1.5 right-1.5 bottom-2 md:bottom-2.5 border-[3px] border-gray-400 rounded-full"
                        ></div>
                    </div>

                    <div
                        class="w-full aspect-square bg-blue-50 overflow-hidden border border-gray-100"
                    >
                        <img
                            v-if="props.question.image"
                            :src="imageUrl(props.question.image)"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex flex-col items-center justify-center text-gray-400"
                        >
                            <LucideIcons.Image
                                class="w-8 h-8 md:w-12 md:h-12 mb-1 opacity-50"
                            />
                            <span class="text-xs md:text-sm font-bold"
                                >Gambar</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: List -->
            <div
                class="w-full sm:w-7/12 flex flex-col justify-center mt-6 sm:mt-0"
            >
                <h2
                    class="text-2xl md:text-3xl font-black text-[#5b738b] uppercase mb-6 md:mb-8 leading-tight drop-shadow-sm text-center sm:text-left"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ props.question.title }}
                </h2>

                <div class="space-y-4 md:space-y-5">
                    <div
                        v-for="(item, idx) in conceptualData"
                        :key="idx"
                        class="flex items-start md:items-center gap-0 group"
                    >
                        <!-- Number Circle -->
                        <div
                            class="w-10 h-10 md:w-11 md:h-11 rounded-full bg-white border-[3px] border-[#8ea0b5] text-[#5b738b] font-black text-lg flex items-center justify-center shadow-sm z-10 flex-shrink-0 mt-1 md:mt-0"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{ idx + 1 }}
                        </div>
                        <!-- Text Box -->
                        <div
                            class="bg-white px-5 py-3 md:py-3.5 rounded-[1.25rem] md:rounded-full shadow-sm border-[3px] border-[#8ea0b5] w-full -ml-5 pl-8 md:pl-9 transition-colors group-hover:border-[#1cb0f6] group-hover:bg-[#f0f9ff] min-h-[48px] md:min-h-[52px] flex items-center"
                        >
                            <span
                                class="font-bold text-[#5b738b] text-[13px] md:text-base tracking-wide group-hover:text-[#1cb0f6] block w-full leading-relaxed break-words uppercase"
                                style="
                                    font-family: &quot;Nunito&quot;, sans-serif;
                                    word-break: break-word;
                                "
                                >{{ item }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Examples -->
    <div
        v-else-if="props.question?.layout_type === 'interactive_examples'"
        class="w-full flex flex-col md:flex-row gap-6 md:gap-10 justify-center items-center py-6 px-2"
    >
        <!-- Left Side: Stacked Polaroids -->
        <div
            class="w-10/12 sm:w-5/12 max-w-[260px] md:max-w-[360px] flex-shrink-0 flex items-center justify-center relative mt-6 md:mt-8 mx-auto"
        >
            <!-- Background Polaroid (Rotated) -->
            <div
                class="absolute w-full bg-white p-2.5 md:p-3.5 lg:p-4 pb-8 md:pb-12 shadow-md border border-gray-200 transform -rotate-6 z-0 translate-y-2 translate-x-2 md:translate-y-3 md:translate-x-3"
            >
                <div
                    class="w-full aspect-square bg-gray-50 border border-gray-100"
                ></div>
            </div>

            <!-- Foreground Polaroid -->
            <div
                class="bg-white p-2.5 md:p-3.5 lg:p-4 pb-8 md:pb-12 shadow-lg border border-gray-200 relative w-full z-10 transform rotate-2 transition-transform hover:rotate-0 duration-300"
            >
                <!-- Paper clip -->
                <div
                    class="absolute -top-5 md:-top-6 left-1/2 -translate-x-1/2 w-7 h-12 md:w-9 md:h-16 border-[3px] border-gray-400 rounded-full bg-transparent z-20"
                >
                    <div
                        class="absolute top-1 left-1.5 right-1.5 bottom-2 md:bottom-2.5 border-[3px] border-gray-400 rounded-full"
                    ></div>
                </div>

                <div
                    class="w-full aspect-square bg-blue-50 overflow-hidden border border-gray-100"
                >
                    <img
                        v-if="props.question.image"
                        :src="imageUrl(props.question.image)"
                        class="w-full h-full object-cover"
                    />
                    <div
                        v-else
                        class="w-full h-full flex flex-col items-center justify-center text-gray-400"
                    >
                        <LucideIcons.Image
                            class="w-8 h-8 md:w-12 md:h-12 mb-1 opacity-50"
                        />
                        <span class="text-xs md:text-sm font-bold">Gambar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Content & List Stack -->
        <div class="w-full md:w-7/12 flex flex-col justify-center mt-6 md:mt-0">
            <!-- Combined Card -->
            <div
                class="bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] shadow-sm relative pt-10 pb-6 px-6 md:px-8 mt-4 md:mt-6 w-full"
            >
                <!-- Title Block Overlapping Top -->
                <div
                    class="absolute -top-5 left-1/2 -translate-x-1/2 w-[90%] bg-[#58cc02] rounded-[16px] border-2 border-white shadow-sm py-2 px-4 text-center flex items-center justify-center min-h-[48px] z-10"
                >
                    <h2
                        class="text-xl md:text-2xl font-black text-white uppercase tracking-wide drop-shadow-sm leading-tight"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        {{ props.question.title }}
                    </h2>
                </div>

                <!-- Content Paragraph -->
                <p
                    class="text-[#5b738b] font-bold leading-relaxed text-[15px] md:text-lg text-center mt-2 mb-6 break-words"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{
                        props.question.subtitle ||
                        "Pelajari materi berikut ini:"
                    }}
                </p>

                <div
                    class="bg-blue-50/50 rounded-2xl p-4 md:p-6 border-2 border-blue-100"
                >
                    <h3
                        class="text-base md:text-lg font-black text-[#1cb0f6] mb-4 uppercase tracking-wider text-center"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        Contoh
                        {{
                            props.question.title
                                ? props.question.title
                                : "materi"
                        }}
                        :
                    </h3>

                    <div class="flex flex-col gap-3">
                        <div
                            v-for="(ex, idx) in conceptualData"
                            :key="idx"
                            class="w-full bg-white px-5 py-3 md:py-3.5 rounded-2xl md:rounded-full border-2 border-gray-200 shadow-sm cursor-default hover:border-[#1cb0f6] hover:bg-[#f0f9ff] transition-colors flex items-center min-h-[48px]"
                        >
                            <span
                                class="font-bold text-[#5b738b] text-[14px] md:text-[16px] break-words leading-snug w-full text-center md:text-left"
                                style="
                                    font-family: &quot;Nunito&quot;, sans-serif;
                                    word-break: break-word;
                                "
                                >{{ ex }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary List -->
    <div
        v-else-if="props.question?.layout_type === 'summary_list'"
        class="w-full flex flex-col justify-center items-center py-6 px-2"
    >
        <div class="w-full max-w-3xl flex flex-col items-center">
            <!-- Title -->
            <h2
                class="text-2xl md:text-3xl font-black text-[#1cb0f6] uppercase mb-6 md:mb-8 tracking-wide drop-shadow-sm text-center"
                style="font-family: &quot;Nunito&quot;, sans-serif"
            >
                {{ props.question.title || "Ringkasan" }}
            </h2>

            <!-- Content Box -->
            <div
                class="bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-6 md:p-8 w-full shadow-sm max-w-3xl"
            >
                <ul class="space-y-4 md:space-y-5">
                    <li
                        v-for="(item, idx) in conceptualData"
                        :key="idx"
                        class="flex items-start gap-3 md:gap-4 group cursor-default"
                    >
                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 mt-0.5 md:mt-1 group-hover:scale-110 transition-transform"
                        >
                            <span
                                v-if="item.icon && item.icon.length <= 4"
                                class="text-xl md:text-2xl"
                                >{{ item.icon }}</span
                            >
                            <LucideIcons.CheckCircle2
                                v-else
                                class="w-6 h-6 text-[#1cb0f6]"
                            />
                        </div>

                        <!-- Text -->
                        <span
                            class="text-[15px] md:text-lg font-bold text-[#5b738b] leading-relaxed"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            {{
                                typeof item === "object"
                                    ? item.text || item.icon
                                    : item
                            }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div
        v-else
        class="w-full max-w-4xl mx-auto flex flex-col gap-6 md:gap-8 pb-6 px-2"
    >
        <!-- Image/Video Banner -->
        <div v-if="props.question?.image" class="w-full">
            <!-- Banner: Video -->
            <div
                v-if="isVideo(props.question.image)"
                class="w-full bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] overflow-hidden flex flex-col shadow-sm"
            >
                <div
                    class="relative w-full max-h-[400px] bg-black flex items-center justify-center overflow-hidden"
                >
                    <canvas
                        ref="bannerCanvasEl"
                        class="absolute inset-0 w-full h-full object-cover filter blur-xl brightness-50 scale-110 pointer-events-none z-0"
                        width="640"
                        height="360"
                    ></canvas>
                    <video
                        ref="bannerVideoEl"
                        :src="imageUrl(props.question.image)"
                        class="relative z-10 w-full max-h-[400px] object-contain"
                        controls
                        @loadeddata="onBannerLoaded"
                        @play="onBannerPlay"
                        @pause="onBannerPause"
                        @seeked="onBannerSeeked"
                    ></video>
                </div>

                <div
                    class="p-4 md:p-5 flex justify-center bg-white border-t-2 border-gray-100"
                >
                    <button
                        @click="toggleBannerPlay"
                        class="flex items-center justify-center gap-2 px-8 py-3 bg-[#1cb0f6] hover:bg-[#1899d6] active:translate-y-1 active:border-b-0 text-white font-black text-sm md:text-base uppercase tracking-wider rounded-2xl border-b-[4px] border-[#1899d6] transition-all"
                        style="font-family: &quot;Nunito&quot;, sans-serif"
                    >
                        <svg
                            v-if="!bannerPlaying"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            width="18"
                            height="18"
                        >
                            <path d="M8 5v14l11-7z" />
                        </svg>
                        <svg
                            v-else
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            width="18"
                            height="18"
                        >
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                        </svg>
                        {{ bannerPlaying ? "Jeda" : "Putar Video" }}
                    </button>
                </div>
            </div>

            <!-- Banner: Image -->
            <div
                v-else
                class="w-full bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-2 md:p-3 shadow-sm"
            >
                <img
                    :src="imageUrl(props.question.image)"
                    :alt="props.question?.title || 'Materi'"
                    class="w-full max-h-[350px] object-cover rounded-[16px] md:rounded-[18px]"
                />
            </div>
        </div>

        <!-- Header -->
        <div
            class="flex items-center gap-4 bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-4 md:p-6 shadow-sm"
        >
            <div
                class="w-12 h-12 md:w-16 md:h-16 rounded-[1rem] md:rounded-[1.25rem] bg-[#f0f9ff] text-[#1cb0f6] flex items-center justify-center flex-shrink-0 border-2 border-[#bae6fd]"
            >
                <BookOpen
                    v-if="
                        !props.question?.material_type ||
                        props.question?.material_type === 'text'
                    "
                    class="w-6 h-6 md:w-8 md:h-8"
                />
                <Video
                    v-else-if="props.question?.material_type === 'video'"
                    class="w-6 h-6 md:w-8 md:h-8"
                />
                <Music
                    v-else-if="props.question?.material_type === 'audio'"
                    class="w-6 h-6 md:w-8 md:h-8"
                />
            </div>
            <div class="flex flex-col">
                <h3
                    class="text-xl md:text-2xl font-black text-[#5b738b] uppercase tracking-wide leading-tight"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ props.question?.title || "Materi Pembelajaran" }}
                </h3>
                <p
                    v-if="props.question?.subtitle"
                    class="text-[#778ca3] font-bold text-sm md:text-base mt-1"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    {{ props.question.subtitle }}
                </p>
            </div>
        </div>

        <!-- Content Area -->
        <div class="w-full flex flex-col gap-6 md:gap-8">
            <!-- YouTube Embed -->
            <div
                v-if="props.question?.youtube_link"
                class="w-full bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-2 md:p-3 shadow-sm"
            >
                <div
                    class="w-full rounded-[16px] md:rounded-[18px] overflow-hidden"
                    style="aspect-ratio: 16/9"
                >
                    <iframe
                        :src="props.question.youtube_link"
                        title="YouTube video player"
                        frameborder="0"
                        allow="
                            accelerometer;
                            autoplay;
                            clipboard-write;
                            encrypted-media;
                            gyroscope;
                            picture-in-picture;
                            web-share;
                        "
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        class="w-full h-full"
                    ></iframe>
                </div>
            </div>

            <!-- Text -->
            <div
                v-if="
                    !props.question?.material_type ||
                    props.question?.material_type === 'text'
                "
                class="bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-6 md:p-8 shadow-sm"
            >
                <div
                    class="prose max-w-none text-[#5b738b] font-bold leading-relaxed text-[15px] md:text-lg break-words"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                    v-html="props.question?.content || 'Konten tidak tersedia'"
                ></div>
            </div>

            <!-- Content Video -->
            <div
                v-else-if="props.question?.material_type === 'video'"
                class="w-full"
            >
                <div
                    v-if="props.question?.content"
                    class="w-full bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] overflow-hidden flex flex-col shadow-sm"
                >
                    <div
                        class="relative w-full max-h-[400px] bg-black flex items-center justify-center overflow-hidden"
                    >
                        <canvas
                            ref="canvasEl"
                            class="absolute inset-0 w-full h-full object-cover filter blur-xl brightness-50 scale-110 pointer-events-none z-0"
                            width="640"
                            height="360"
                        ></canvas>
                        <video
                            ref="videoEl"
                            :src="props.question.content"
                            class="relative z-10 w-full max-h-[400px] object-contain"
                            controls
                            @loadeddata="onLoaded"
                            @play="onPlay"
                            @pause="onPause"
                            @seeked="onSeeked"
                        ></video>
                    </div>

                    <div
                        class="p-4 md:p-5 flex justify-center bg-white border-t-2 border-gray-100"
                    >
                        <button
                            @click="togglePlay"
                            class="flex items-center justify-center gap-2 px-8 py-3 bg-[#1cb0f6] hover:bg-[#1899d6] active:translate-y-1 active:border-b-0 text-white font-black text-sm md:text-base uppercase tracking-wider rounded-2xl border-b-[4px] border-[#1899d6] transition-all"
                            style="font-family: &quot;Nunito&quot;, sans-serif"
                        >
                            <svg
                                v-if="!playing"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                width="18"
                                height="18"
                            >
                                <path d="M8 5v14l11-7z" />
                            </svg>
                            <svg
                                v-else
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                width="18"
                                height="18"
                            >
                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                            </svg>
                            {{ playing ? "Jeda" : "Putar Video" }}
                        </button>
                    </div>
                </div>
                <div
                    v-else
                    class="bg-red-50 text-red-600 font-bold p-4 rounded-2xl border-2 border-red-200 text-center"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    Video tidak tersedia
                </div>
            </div>

            <!-- Audio -->
            <div
                v-else-if="props.question?.material_type === 'audio'"
                class="w-full bg-white rounded-[24px] border-2 border-gray-200 border-b-[6px] p-6 shadow-sm flex flex-col items-center gap-4"
            >
                <audio
                    v-if="props.question?.content"
                    :src="props.question.content"
                    controls
                    class="w-full max-w-md"
                ></audio>
                <div
                    v-else
                    class="text-red-600 font-bold text-center"
                    style="font-family: &quot;Nunito&quot;, sans-serif"
                >
                    Audio tidak tersedia
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Conceptual Systematic Styles ── */
.cs-container {
    background: #ffffff;
    border-radius: 20px;
    border: 2px solid #cbd5e1;
    border-bottom-width: 5px;
    padding: 1.25rem 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    font-family: "Nunito", "Inter", sans-serif;
    overflow: hidden;
    position: relative;
    width: 100%;
}

.cs-title {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 900;
    color: #1cb0f6;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    word-break: break-word;
    overflow-wrap: break-word;
    line-height: 1.3;
}

@media (min-width: 640px) {
    .cs-title {
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
    }
}

.cs-grid {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 1024px) {
    .cs-grid {
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 1.5rem;
    }
}

.cs-texts-left,
.cs-texts-right {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    gap: 1.5rem;
    flex: 1;
    max-width: 280px;
    width: 100%;
}

.cs-text-box {
    background: #f8fafc;
    border-radius: 16px;
    padding: 1.25rem;
    font-size: 0.95rem;
    font-weight: 700;
    color: #475569;
    line-height: 1.5;
    border: 2px solid #e2e8f0;
    border-bottom-width: 4px;
    position: relative;
    text-align: center;
    transition:
        transform 0.2s ease,
        border-color 0.2s;
    word-break: break-word;
    overflow-wrap: break-word;
}

.cs-text-box:hover {
    transform: translateY(-2px);
    border-color: #cbd5e1;
}

.cs-arrow {
    display: none;
    position: absolute;
    top: 50%;
    width: 30px;
    height: 2px;
    background: #64748b;
}

@media (min-width: 1024px) {
    .cs-arrow {
        display: block;
    }
    .cs-arrow.right {
        right: -32px;
    }
    .cs-arrow.left {
        left: -32px;
    }
    .cs-arrow::after {
        content: "";
        position: absolute;
        top: -5px;
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
    }
    .cs-arrow.right::after {
        right: -6px;
        border-left: 8px solid #64748b;
    }
    .cs-arrow.left::after {
        left: -6px;
        border-right: 8px solid #64748b;
    }
}

.cs-image-wrap {
    flex: 0 0 auto;
    position: relative;
    width: 280px;
    height: 280px;
    background: #f1f5f9;
    border-radius: 24px;
    border: 2px solid #cbd5e1;
    border-bottom-width: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

.cs-center-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    position: absolute;
    top: 0;
    left: 0;
    padding: 1rem;
}

.cs-center-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: #f1f5f9;
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
    background: #ffffff;
    padding: 1rem 1.5rem;
    border-radius: 20px;
    border: 2px solid #cbd5e1;
    border-bottom-width: 4px;
}

.cs-slider-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
}

.cs-slider-label {
    font-weight: 800;
    color: #475569;
    font-size: 0.95rem;
}

.cs-slider {
    -webkit-appearance: none;
    appearance: none;
    height: 12px;
    background: #e2e8f0;
    border-radius: 10px;
    outline: none;
    cursor: pointer;
    width: 100%;
}

.cs-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #1cb0f6;
    cursor: pointer;
    box-shadow:
        0 0 0 4px #fff,
        0 4px 6px rgba(0, 0, 0, 0.15);
    transition: transform 0.1s;
}
.cs-slider::-webkit-slider-thumb:active {
    transform: scale(1.15);
}

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
    .cs-metrics-grid {
        flex-direction: row;
    }
}

.cs-metric-box {
    padding: 1.25rem 2rem;
    border-radius: 20px;
    color: white;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
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
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
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

.mat-title-section {
    flex: 1;
    text-align: left;
    min-width: 0;
}

.mat-title {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
    word-break: break-word;
    overflow-wrap: break-word;
}

.mat-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

/* ── Divider ── */
.mat-divider {
    height: 1px;
    background: #e2e8f0;
}

/* ── Content ── */
.mat-content {
    padding: 1.25rem;
    background: #ffffff;
}

.mat-text {
    line-height: 1.7;
    color: #475569;
}

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
    .mat-image {
        max-height: 200px;
    }
    .mat-header {
        padding: 0.85rem;
        gap: 0.65rem;
    }
    .mat-icon {
        width: 34px;
        height: 34px;
    }
    .mat-title {
        font-size: 0.875rem;
    }
    .mat-subtitle {
        font-size: 0.75rem;
    }
    .mat-content {
        padding: 0.9rem;
    }
    .mat-text p {
        font-size: 0.875rem;
    }
}

.mat-media {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: center;
}

/* ── Video Player ── */
.video-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
}

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

/* Magic Transitions */
.magic-fade-enter-active,
.magic-fade-leave-active {
    transition: opacity 0.8s ease-in-out;
}
.magic-fade-enter-from,
.magic-fade-leave-to {
    opacity: 0;
}

.magic-zoom-fade-enter-active,
.magic-zoom-fade-leave-active {
    transition: all 0.8s ease-in-out;
}
.magic-zoom-fade-enter-from {
    opacity: 0;
    transform: scale(1.1);
}
.magic-zoom-fade-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

.magic-slide-left-enter-active,
.magic-slide-left-leave-active {
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.magic-slide-left-enter-from {
    opacity: 0;
    transform: translateX(10%);
}
.magic-slide-left-leave-to {
    opacity: 0;
    transform: translateX(-10%);
}

.magic-slide-right-enter-active,
.magic-slide-right-leave-active {
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.magic-slide-right-enter-from {
    opacity: 0;
    transform: translateX(-10%);
}
.magic-slide-right-leave-to {
    opacity: 0;
    transform: translateX(10%);
}

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
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.35);
    transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.vid-play-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(29, 78, 216, 0.45);
}
.vid-play-btn:active {
    transform: translateY(1px);
}

/* ── Audio ── */
.mat-audio {
    width: 100%;
    max-width: 100%;
}

.mat-error {
    text-align: center;
    color: #dc2626;
    font-weight: 600;
    margin: 1rem 0 0;
}

/* ── Earthquake & Slider Styles ── */
@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    10%,
    30%,
    50%,
    70%,
    90% {
        transform: translateX(-5px) translateY(-2px) rotate(-1deg);
    }
    20%,
    40%,
    60%,
    80% {
        transform: translateX(5px) translateY(2px) rotate(1deg);
    }
}
.animate-shake {
    animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) infinite;
}

.cs-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 9999px;
    background: #cbd5e1;
    outline: none;
    transition: background 0.2s;
}

.cs-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #1cb0f6;
    border: 4px solid #ffffff;
    box-shadow: 0 2px 8px rgba(28, 176, 246, 0.45);
    cursor: pointer;
    transition:
        transform 0.1s,
        background-color 0.2s;
}

.cs-slider::-webkit-slider-thumb:hover {
    transform: scale(1.15);
    background: #189fdc;
}

.cs-slider::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #1cb0f6;
    border: 4px solid #ffffff;
    box-shadow: 0 2px 8px rgba(28, 176, 246, 0.45);
    cursor: pointer;
    transition:
        transform 0.1s,
        background-color 0.2s;
}

.cs-slider::-moz-range-thumb:hover {
    transform: scale(1.15);
    background: #189fdc;
}
</style>
