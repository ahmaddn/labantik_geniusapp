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

const statusColor = computed(() => isDanger.value ? '#ef4444' : isWarning.value ? '#eab308' : '#58cc02');
const statusBg = computed(() => isDanger.value ? '#fef2f2' : isWarning.value ? '#fefce8' : '#f0fdf4');
const statusBorder = computed(() => isDanger.value ? '#fca5a5' : isWarning.value ? '#fde047' : '#86efac');
const statusLabel = computed(() => isDanger.value ? 'BAHAYA' : isWarning.value ? 'WASPADA' : 'AMAN');
const levelImage = computed(() => currentLevelData.value?.image ? `/storage/${currentLevelData.value.image}` : null);
const levelNarration = computed(() => currentLevelData.value?.narration || 'Ayo ubah penggeser untuk melihat dampaknya!');

const sliderColor = (idx) => (['#1cb0f6', '#58cc02', '#f97316', '#a855f7'][idx % 4]);
const sliderTrackColor = (idx) => (['#bae6fd', '#bbf7d0', '#fdba74', '#e9d5ff'][idx % 4]);

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
                        <ImageIcon :size="48" color="#cbd5e1" :stroke-width="1.5" />
                        <span>Gambar Belum Tersedia</span>
                    </div>
                </transition>

                <SimulationEffects :effect="currentLevelData?.animation_effect || 'none'" />

                <!-- Status badge -->
                <div class="ds-status-badge" :style="{ background: statusBg, borderColor: statusBorder, color: statusColor }">
                    <AlertTriangle v-if="isDanger" :size="16" :stroke-width="3" />
                    <AlertCircle v-else-if="isWarning" :size="16" :stroke-width="3" />
                    <CheckCircle2 v-else :size="16" :stroke-width="3" />
                    {{ statusLabel }}
                </div>
            </div>

            <!-- Narration panel -->
            <div class="ds-narration">
                <div class="ds-narration-badge">
                    <MessageCircle :size="24" color="#1cb0f6" :stroke-width="2.5" />
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
                        <div class="ds-step-dots">
                            <div v-for="s in 3" :key="s" class="ds-dot" :class="{ 'ds-dot-on': sliderValues[idx] >= s }" :style="{ background: sliderValues[idx] >= s ? sliderColor(idx) : '#cbd5e1' }"></div>
                        </div>
                    </div>
                    <span class="ds-slider-edge">{{ v.max_label || 'Max' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ds-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Title */
.ds-title {
    font-size: 24px;
    font-weight: 900;
    color: #58cc02;
    text-transform: uppercase;
    text-align: center;
    letter-spacing: 0.5px;
}

/* Main Area */
.ds-main {
    display: flex;
    gap: 20px;
    align-items: stretch;
}

/* Visual */
.ds-visual {
    flex: 1.5;
    position: relative;
    border-radius: 20px;
    border: 2px solid #cbd5e1;
    border-bottom-width: 5px;
    background: #f1f5f9;
    overflow: hidden;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.3s;
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
.ds-visual-img { width: 100%; height: 100%; object-fit: cover; z-index: 10; position: absolute; top: 0; left: 0; }
.ds-visual-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #94a3b8; font-weight: 800; font-size: 14px; position: absolute; width: 100%; height: 100%; }

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


.ds-status-badge {
    position: absolute;
    top: 12px; right: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-radius: 99px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 900;
    border: 2px solid;
    z-index: 30;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

/* Narration */
.ds-narration {
    flex: 1;
    border-radius: 20px;
    border: 2px solid #bae6fd;
    background: #f0f9ff;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 12px;
}
.ds-narration-badge {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: #ffffff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 0 0 #bae6fd;
    flex-shrink: 0;
}
.ds-level-name { font-size: 16px; font-weight: 800; color: #0369a1; font-style: italic; }
.ds-level-narration { font-size: 14px; font-weight: 700; color: #475569; line-height: 1.5; }
.ds-metric-pill {
    background: #ffffff;
    color: #1cb0f6;
    border-radius: 99px;
    padding: 6px 16px;
    font-size: 14px;
    font-weight: 800;
    border: 2px solid #bae6fd;
}

/* Sliders */
.ds-controls {
    background: #ffffff;
    border-radius: 20px;
    border: 2px solid #cbd5e1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.ds-empty { text-align: center; color: #94a3b8; font-weight: 800; font-size: 15px; }
.ds-slider-group { display: flex; flex-direction: column; gap: 10px; }
.ds-slider-header { display: flex; align-items: center; justify-content: space-between; }
.ds-slider-name {
    font-size: 13px;
    font-weight: 900;
    border-radius: 8px;
    padding: 4px 12px;
}
.ds-slider-value { font-size: 13px; font-weight: 800; color: #64748b; }

.ds-slider-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ds-slider-edge { font-size: 12px; font-weight: 800; color: #94a3b8; min-width: 32px; text-align: center; }
.ds-slider-track-wrap { flex: 1; display: flex; flex-direction: column; gap: 8px; position: relative; }

.ds-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 12px;
    background: var(--track-color, #e2e8f0);
    border-radius: 99px;
    outline: none;
    cursor: pointer;
}
.ds-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--thumb-color, #1cb0f6);
    box-shadow: 0 0 0 4px #fff, 0 4px 6px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.1s;
}
.ds-slider::-webkit-slider-thumb:active { transform: scale(1.15); }
.ds-slider::-moz-range-thumb {
    width: 28px; height: 28px;
    border: none;
    border-radius: 50%;
    background: var(--thumb-color, #1cb0f6);
    box-shadow: 0 0 0 4px #fff, 0 4px 6px rgba(0,0,0,0.15);
    cursor: pointer;
}

.ds-step-dots {
    display: flex;
    justify-content: space-between;
    padding: 0 14px;
}
.ds-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    transition: background 0.3s;
}

/* Mobile */
@media (max-width: 640px) {
    .ds-main { flex-direction: column; }
    .ds-visual { min-height: 180px; }
}
</style>
