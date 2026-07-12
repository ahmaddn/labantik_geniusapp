<script setup>
import { ref, computed, watch, onMounted, reactive } from "vue";
import SimulationEffects from "@/Components/Simulation/SimulationEffects.vue";
import { AlertCircle, CheckCircle2, MessageCircle, AlertTriangle, Image as ImageIcon } from "lucide-vue-next";

const props = defineProps({
    quiz: Object,
    modelValue: [String, Number, Array],
});
const emit = defineEmits(['update-answer']);

const title = computed(() => props.quiz?.title || 'Simulasi Interaktif');
const variables = computed(() => props.quiz?.variables || []);
const levels = computed(() => props.quiz?.levels || []);

const sliderValues = reactive({});
variables.value.forEach((v, idx) => { sliderValues[idx] = 1; });

const dangerScore = computed(() => {
    let s = 0;
    for (let k in sliderValues) s += sliderValues[k];
    return s;
});

const currentLevelData = computed(() => {
    if (!levels.value.length) return null;
    const max = variables.value.length * 3;
    const min = variables.value.length * 1;
    if (max === 0) return levels.value[0];
    const norm = (dangerScore.value - min) / (max - min || 1);
    return levels.value[Math.round(norm * (levels.value.length - 1))];
});

const isDanger = computed(() => dangerScore.value >= Math.floor(variables.value.length * 3 * 0.8));
const isWarning = computed(() => {
    const max = variables.value.length * 3;
    const mid = Math.floor((max + variables.value.length) / 2);
    return dangerScore.value >= mid && dangerScore.value < Math.floor(max * 0.8);
});

const statusColor = computed(() => isDanger.value ? '#ef4444' : isWarning.value ? '#eab308' : '#22c55e');
const statusBg = computed(() => isDanger.value ? '#fef2f2' : isWarning.value ? '#fefce8' : '#f0fdf4');
const statusBorder = computed(() => isDanger.value ? '#fca5a5' : isWarning.value ? '#fde047' : '#bbf7d0');
const statusLabel = computed(() => isDanger.value ? 'BAHAYA' : isWarning.value ? 'WASPADA' : 'AMAN');
const levelImage = computed(() => currentLevelData.value?.image ? `/storage/${currentLevelData.value.image}` : null);
const levelNarration = computed(() => currentLevelData.value?.narration || 'Ayo ubah penggeser untuk melihat dampaknya!');

const sliderColor = (idx) => (['#1cb0f6', '#10b981', '#f97316', '#a855f7'][idx % 4]);
const sliderTrackColor = (idx) => (['#bae6fd', '#a7f3d0', '#fdba74', '#e9d5ff'][idx % 4]);

watch(sliderValues, () => {
    emit('update-answer', { value: Object.values(sliderValues).join('-') });
});
onMounted(() => {
    emit('update-answer', { value: Object.values(sliderValues).join('-') });
});
</script>

<template>
    <div class="ds-wrap">
        <!-- Title -->
        <h2 class="ds-title">{{ title }}</h2>

        <!-- Main Area: Visual + Narration -->
        <div class="ds-main">
            <!-- Visual box -->
            <div class="ds-visual" :class="{ 'ds-visual-danger': isDanger, 'ds-quake': currentLevelData?.animation_effect === 'earthquake' }">
                <transition :name="currentLevelData?.image_transition !== 'none' ? 'magic-' + currentLevelData?.image_transition : ''">
                    <img :key="levelImage" v-if="levelImage" :src="levelImage" class="ds-visual-img" />
                    <div v-else class="ds-visual-placeholder">
                        <ImageIcon :size="40" color="#94a3b8" :stroke-width="1.5" />
                        <span>Gambar Belum Tersedia</span>
                    </div>
                </transition>

                <SimulationEffects :effect="currentLevelData?.animation_effect || 'none'" />

                <!-- Status badge -->
                <div class="ds-status-badge" :style="{ background: statusBg, borderColor: statusBorder, color: statusColor }">
                    <AlertTriangle v-if="isDanger" :size="14" :stroke-width="3" />
                    <AlertCircle v-else-if="isWarning" :size="14" :stroke-width="3" />
                    <CheckCircle2 v-else :size="14" :stroke-width="3" />
                    <span class="ds-status-text">{{ statusLabel }}</span>
                </div>
            </div>

            <!-- Narration panel -->
            <div class="ds-narration">
                <div class="ds-narration-badge">
                    <MessageCircle :size="20" color="#1cb0f6" :stroke-width="2.5" />
                </div>
                <p class="ds-level-name" v-if="currentLevelData">"{{ currentLevelData.level_name || 'Amati Perubahan' }}"</p>
                <p class="ds-level-narration">{{ levelNarration }}</p>
                <div v-if="currentLevelData?.metric_value" class="ds-metric-pill">{{ currentLevelData.metric_value }}</div>
            </div>
        </div>

        <!-- Sliders Controls Area -->
        <div class="ds-controls">
            <div v-if="variables.length === 0" class="ds-empty">Belum ada variabel penggeser.</div>

            <div v-for="(v, idx) in variables" :key="idx" class="ds-slider-group">
                <div class="ds-slider-header">
                    <span class="ds-slider-name" :style="{ color: sliderColor(idx), background: sliderTrackColor(idx) }">
                        {{ v.name || `Variabel ${idx + 1}` }}
                    </span>
                    <span class="ds-slider-value">
                        {{ sliderValues[idx] === 1 ? (v.min_label || 'Rendah') : sliderValues[idx] === 3 ? (v.max_label || 'Tinggi') : 'Sedang' }}
                    </span>
                </div>
                
                <div class="ds-slider-inner-row">
                    <span class="ds-slider-limit-label">{{ v.min_label || 'Rendah' }}</span>
                    <div class="ds-slider-container">
                        <input
                            type="range"
                            min="1" max="3" step="1"
                            v-model.number="sliderValues[idx]"
                            class="ds-slider"
                            :style="{
                                '--track-color': '#cbd5e1',
                                '--thumb-color': sliderColor(idx),
                            }"
                        />
                        <div class="ds-step-dots">
                            <div v-for="s in 3" :key="s" class="ds-dot" :class="{ 'ds-dot-on': sliderValues[idx] >= s }" :style="{ background: sliderValues[idx] >= s ? sliderColor(idx) : '#cbd5e1' }"></div>
                        </div>
                    </div>
                    <span class="ds-slider-limit-label">{{ v.max_label || 'Tinggi' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.ds-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: 'Nunito', sans-serif;
}

/* Title */
.ds-title {
    font-size: 26px;
    font-weight: 900;
    color: #1cb0f6;
    text-transform: uppercase;
    text-align: center;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

/* Main Area */
.ds-main {
    display: flex;
    gap: 24px;
    align-items: stretch;
}

/* Visual Box */
.ds-visual {
    flex: 1.3;
    position: relative;
    border-radius: 24px;
    border: 3px solid #cbd5e1;
    border-bottom-width: 6px;
    background: #f8fafc;
    overflow: hidden;
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.ds-visual-danger { border-color: #ef4444; }
.ds-quake { animation: ds-quake-shake 0.35s cubic-bezier(.36,.07,.19,.97) infinite; }
@keyframes ds-quake-shake {
    0%,100% { transform: translate(0,0); }
    20% { transform: translate(-3px,-2px); }
    40% { transform: translate(3px,2px); }
    60% { transform: translate(-2px,3px); }
    80% { transform: translate(2px,-2px); }
}
.ds-visual-img { width: 100%; height: 100%; object-fit: cover; z-index: 10; position: absolute; top: 0; left: 0; will-change: transform, opacity; }
.ds-visual-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #94a3b8; font-weight: 800; font-size: 13px; position: absolute; width: 100%; height: 100%; }

/* Transitions */
.magic-fade-enter-active, .magic-fade-leave-active { transition: opacity 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); }
.magic-fade-enter-from, .magic-fade-leave-to { opacity: 0; }

.magic-zoom-fade-enter-active, .magic-zoom-fade-leave-active { transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); }
.magic-zoom-fade-enter-from { opacity: 0; transform: scale(1.04); }
.magic-zoom-fade-leave-to { opacity: 0; transform: scale(0.96); }

.magic-slide-left-enter-active, .magic-slide-left-leave-active { transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); }
.magic-slide-left-enter-from { opacity: 0; transform: translateX(4%); }
.magic-slide-left-leave-to { opacity: 0; transform: translateX(-4%); }

.magic-slide-right-enter-active, .magic-slide-right-leave-active { transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); }
.magic-slide-right-enter-from { opacity: 0; transform: translateX(-4%); }
.magic-slide-right-leave-to { opacity: 0; transform: translateX(4%); }

.ds-status-badge {
    position: absolute;
    top: 12px; right: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-radius: 99px;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 900;
    border: 2.5px solid;
    z-index: 30;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.ds-status-text {
    letter-spacing: 0.5px;
}

/* Narration Panel */
.ds-narration {
    flex: 1;
    border-radius: 24px;
    border: 3px solid #bae6fd;
    border-bottom-width: 6px;
    background: #f0f9ff;
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.ds-narration-badge {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #bae6fd;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 0 0 #bae6fd;
    flex-shrink: 0;
}
.ds-level-name { font-size: 15px; font-weight: 900; color: #0284c7; }
.ds-level-narration { font-size: 13.5px; font-weight: 800; color: #475569; line-height: 1.5; }
.ds-metric-pill {
    background: #ffffff;
    color: #1cb0f6;
    border-radius: 14px;
    border: 2.5px solid #bae6fd;
    padding: 5px 14px;
    font-size: 12.5px;
    font-weight: 900;
    box-shadow: 0 2px 5px rgba(28, 176, 246, 0.05);
}

/* Controls Center */
.ds-controls {
    background: #ffffff;
    border-radius: 28px;
    border: 3px solid #e2e8f0;
    border-bottom-width: 6px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.ds-empty { text-align: center; color: #94a3b8; font-weight: 800; font-size: 14px; }
.ds-slider-group {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    padding: 16px;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ds-slider-header { display: flex; align-items: center; justify-content: space-between; }
.ds-slider-name {
    font-size: 11px;
    font-weight: 900;
    border-radius: 12px;
    padding: 4px 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.ds-slider-value {
    font-size: 11px;
    font-weight: 900;
    color: #1cb0f6;
    background: #f0f9ff;
    padding: 3px 10px;
    border-radius: 99px;
    border: 1.5px solid #bae6fd;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ds-slider-inner-row {
    display: flex;
    align-items: center;
    gap: 16px;
    width: 100%;
}
.ds-slider-limit-label {
    font-size: 11px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    width: 60px;
}
.ds-slider-inner-row .ds-slider-limit-label:first-child {
    text-align: right;
}
.ds-slider-inner-row .ds-slider-limit-label:last-child {
    text-align: left;
}

.ds-slider-container {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
    height: 24px;
}

/* Custom Slider peg */
.ds-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 8px;
    background: var(--track-color, #cbd5e1);
    border-radius: 99px;
    outline: none;
    cursor: pointer;
    position: relative;
    z-index: 10;
}

.ds-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--thumb-color, #1cb0f6);
    border: 4px solid #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.1s;
}
.ds-slider::-webkit-slider-thumb:hover { transform: scale(1.15); }
.ds-slider::-webkit-slider-thumb:active { transform: scale(1.15); }

.ds-slider::-moz-range-thumb {
    width: 16px; height: 16px;
    border: 4px solid #ffffff;
    border-radius: 50%;
    background: var(--thumb-color, #1cb0f6);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.1s;
}
.ds-slider::-moz-range-thumb:hover { transform: scale(1.15); }

.ds-step-dots {
    display: flex;
    justify-content: space-between;
    position: absolute;
    left: 8px;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 5;
}
.ds-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #cbd5e1;
    transition: background 0.3s;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .ds-main { flex-direction: column; gap: 16px; }
    .ds-visual { min-height: 180px; }
    .ds-slider-inner-row { gap: 8px; }
    .ds-slider-limit-label { width: 45px; font-size: 9.5px; }
}
</style>
