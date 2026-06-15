<script setup>
import { ref, computed, watch, onMounted, reactive } from "vue";
import SimulationEffects from "./SimulationEffects.vue";

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

const statusColor = computed(() => isDanger.value ? '#ff4b4b' : isWarning.value ? '#ffc800' : '#58cc02');
const statusLabel = computed(() => isDanger.value ? 'BAHAYA' : isWarning.value ? 'WASPADA' : 'AMAN / NORMAL');
const levelImage = computed(() => currentLevelData.value?.image ? `/storage/${currentLevelData.value.image}` : null);
const levelNarration = computed(() => currentLevelData.value?.narration || 'Ayo ubah penggeser untuk melihat dampaknya!');

const sliderColor = (idx) => (['#1cb0f6', '#58cc02', '#ffc800', '#ff4b4b'][idx % 4]);
const sliderTrackColor = (idx) => (['#bae6fd', '#d1fae5', '#fef3c7', '#fee2e2'][idx % 4]);

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

        <!-- Main Area: Visual + Narration side by side (desktop) / stacked (mobile) -->
        <div class="ds-main">
            <!-- Visual box -->
            <div class="ds-visual" :class="{ 'ds-visual-danger': isDanger }">
                <img v-if="levelImage" :src="levelImage" class="ds-visual-img" />
                <div v-else class="ds-visual-placeholder">
                    <div class="ds-visual-icon">📊</div>
                    <span>Gambar Simulasi</span>
                </div>

                <!-- Status badge -->
                <div class="ds-status-badge" :style="{ background: statusColor }">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round">
                        <path v-if="isDanger" d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <polyline v-else-if="isWarning" points="1 12 12 1 23 12"/>
                        <polyline v-else points="20 6 9 17 4 12"/>
                    </svg>
                    {{ statusLabel }}
                </div>
            </div>

            <!-- Narration panel -->
            <div class="ds-narration">
                <div class="ds-narration-badge">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <p class="ds-level-name" v-if="currentLevelData">"{{ currentLevelData.level_name || 'Amati Perubahan' }}"</p>
                <p class="ds-level-narration">{{ levelNarration }}</p>
                <div v-if="currentLevelData?.metric_value" class="ds-metric-pill">{{ currentLevelData.metric_value }}</div>
            </div>
        </div>

        <!-- Sliders -->
        <div class="ds-controls">
            <div v-if="variables.length === 0" class="ds-empty">Belum ada variabel penggeser.</div>

            <div v-for="(v, idx) in variables" :key="idx" class="ds-slider-group">
                <div class="ds-slider-header">
                    <span class="ds-slider-name" :style="{ color: sliderColor(idx), background: sliderTrackColor(idx) }">
                        {{ v.name || `Variabel ${idx + 1}` }}
                    </span>
                    <span class="ds-slider-value">
                        {{ sliderValues[idx] === 1 ? (v.min_label || 'Min') : sliderValues[idx] === 3 ? (v.max_label || 'Max') : 'Sedang' }}
                    </span>
                </div>
                <div class="ds-slider-row">
                    <span class="ds-slider-edge">{{ v.min_label || 'Min' }}</span>
                    <div class="ds-slider-track-wrap">
                        <input
                            type="range"
                            min="1" max="3" step="1"
                            v-model.number="sliderValues[idx]"
                            class="ds-slider"
                            :style="{
                                '--track-color': sliderTrackColor(idx),
                                '--thumb-color': sliderColor(idx),
                            }"
                        />
                        <!-- Step indicators -->
                        <div class="ds-step-dots">
                            <div v-for="s in 3" :key="s" class="ds-dot" :class="{ 'ds-dot-on': sliderValues[idx] >= s }" :style="{ background: sliderValues[idx] >= s ? sliderColor(idx) : '#e5e5e5' }"></div>
                        </div>
                    </div>
                    <span class="ds-slider-edge">{{ v.max_label || 'Max' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.ds-wrap {
    font-family: 'Nunito', sans-serif;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Title */
.ds-title {
    font-size: clamp(1rem, 3vw, 1.25rem);
    font-weight: 900;
    color: #3c3c3c;
    text-align: center;
    letter-spacing: 0.3px;
}

/* Main Area */
.ds-main {
    display: flex;
    gap: 14px;
    align-items: stretch;
}

/* Visual */
.ds-visual {
    flex: 1.4;
    position: relative;
    border-radius: 16px;
    border: 2.5px solid #e5e5e5;
    background: #f7f7f7;
    overflow: hidden;
    min-height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.3s;
}
.ds-visual-danger { border-color: #ff4b4b; animation: danger-pulse 1s ease-in-out infinite; }
@keyframes danger-pulse { 0%,100%{box-shadow:0 0 0 0 rgba(255,75,75,0.2)} 50%{box-shadow:0 0 0 8px rgba(255,75,75,0)} }
.ds-visual-img { width: 100%; height: 100%; object-fit: cover; }
.ds-visual-placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: #bbb; font-weight: 700; font-size: 13px; }
.ds-visual-icon { font-size: 32px; }

.ds-status-badge {
    position: absolute;
    top: 10px; left: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
    border-radius: 99px;
    padding: 4px 12px 4px 8px;
    font-size: 11px;
    font-weight: 900;
    color: #fff;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: background 0.4s ease;
}

/* Narration */
.ds-narration {
    flex: 1;
    border-radius: 16px;
    border: 2.5px solid #e5e5e5;
    background: #f7f7f7;
    padding: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 8px;
}
.ds-narration-badge {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #1cb0f6;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(28,176,246,0.35);
    flex-shrink: 0;
}
.ds-level-name { font-size: 14px; font-weight: 800; color: #3c3c3c; font-style: italic; }
.ds-level-narration { font-size: 12px; font-weight: 700; color: #666; line-height: 1.5; }
.ds-metric-pill {
    background: #dbeafe;
    color: #1d4ed8;
    border-radius: 99px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 800;
    border: 1.5px solid #bfdbfe;
}

/* Sliders */
.ds-controls {
    background: #f7f7f7;
    border-radius: 16px;
    border: 2px solid #e5e5e5;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.ds-empty { text-align: center; color: #aaa; font-weight: 700; font-size: 13px; }
.ds-slider-group { display: flex; flex-direction: column; gap: 8px; }
.ds-slider-header { display: flex; align-items: center; justify-content: space-between; }
.ds-slider-name {
    font-size: 12px;
    font-weight: 800;
    border-radius: 8px;
    padding: 3px 10px;
}
.ds-slider-value { font-size: 12px; font-weight: 800; color: #777; }

.ds-slider-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ds-slider-edge { font-size: 11px; font-weight: 700; color: #aaa; min-width: 28px; text-align: center; }
.ds-slider-track-wrap { flex: 1; display: flex; flex-direction: column; gap: 6px; }

.ds-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 10px;
    background: var(--track-color, #e5e5e5);
    border-radius: 99px;
    outline: none;
    cursor: pointer;
}
.ds-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--thumb-color, #1cb0f6);
    box-shadow: 0 0 0 3px #fff, 0 3px 8px rgba(0,0,0,0.2);
    cursor: pointer;
    transition: transform 0.15s;
}
.ds-slider::-webkit-slider-thumb:active { transform: scale(1.15); }
.ds-slider::-moz-range-thumb {
    width: 24px; height: 24px;
    border: none;
    border-radius: 50%;
    background: var(--thumb-color, #1cb0f6);
    box-shadow: 0 0 0 3px #fff, 0 3px 8px rgba(0,0,0,0.2);
    cursor: pointer;
}

.ds-step-dots {
    display: flex;
    justify-content: space-between;
    padding: 0 11px;
}
.ds-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    transition: background 0.3s;
}

/* Mobile: stack main */
@media (max-width: 520px) {
    .ds-main { flex-direction: column; }
    .ds-visual { min-height: 140px; }
}
</style>
