<script setup>
import { ref, computed, watch } from 'vue';
import { ArrowRight, ArrowDown, MessageCircle } from 'lucide-vue-next';

const props = defineProps({
    quiz: { type: Object, required: true },
});

const simulation = computed(() => props.quiz || {});
const activeOptionIndex = ref(null);

const currentFutureImage = computed(() => {
    if (activeOptionIndex.value !== null && simulation.value.options?.[activeOptionIndex.value]) {
        return `/storage/${simulation.value.options[activeOptionIndex.value].future_state_image}`;
    }
    return null;
});

const currentFeedbackMessage = computed(() => {
    if (activeOptionIndex.value !== null && simulation.value.options?.[activeOptionIndex.value]) {
        return simulation.value.options[activeOptionIndex.value].feedback_message;
    }
    return 'Pilih salah satu tindakan di bawah untuk melihat apa yang akan terjadi!';
});

const displayedFeedback = ref('');
let typingInterval = null;
const startTyping = (text) => {
    if (typingInterval) clearInterval(typingInterval);
    displayedFeedback.value = '';
    if (!text) return;
    let i = 0;
    typingInterval = setInterval(() => {
        displayedFeedback.value += text.charAt(i++);
        if (i >= text.length) clearInterval(typingInterval);
    }, 28);
};

watch(currentFeedbackMessage, (v) => startTyping(v), { immediate: true });

const selectOption = (index) => {
    activeOptionIndex.value = index;
    if (navigator.vibrate) navigator.vibrate(40);
};

const colorMap = {
    green:  { bg: '#58cc02', border: '#46a302', text: '#fff' },
    yellow: { bg: '#ffc800', border: '#e6b400', text: '#3c3c3c' },
    red:    { bg: '#ff4b4b', border: '#ea2b2b', text: '#fff' },
    blue:   { bg: '#1cb0f6', border: '#1899d6', text: '#fff' },
};
const getColor = (c) => colorMap[c] || colorMap.blue;
</script>

<template>
    <div class="dec-wrap" v-if="simulation">
        <!-- Title -->
        <div class="dec-header">
            <h2 class="dec-title">{{ simulation.title || 'Simulasi Keputusan' }}</h2>
        </div>

        <!-- Before -> After -->
        <div class="dec-scenes">
            <!-- Before -->
            <div class="dec-scene">
                <div class="dec-scene-badge">{{ simulation.initial_state_title || 'Hari Ini' }}</div>
                <div class="dec-scene-card">
                    <img v-if="simulation.initial_state_image" :src="`/storage/${simulation.initial_state_image}`" class="dec-scene-img" alt="Kondisi Awal" />
                    <div v-else class="dec-scene-empty">Gambar Belum Tersedia</div>
                </div>
            </div>

            <!-- Arrow -->
            <div class="dec-arrow">
                <ArrowRight class="hidden sm:block" :size="32" color="#cbd5e1" :stroke-width="3" />
                <ArrowDown class="sm:hidden" :size="32" color="#cbd5e1" :stroke-width="3" />
            </div>

            <!-- After -->
            <div class="dec-scene">
                <div class="dec-scene-badge">{{ simulation.future_state_title || 'Masa Depan' }}</div>
                <div class="dec-scene-card" :class="{ 'dec-scene-active': activeOptionIndex !== null }">
                    <Transition name="fade" mode="out-in">
                        <img v-if="currentFutureImage" :key="currentFutureImage" :src="currentFutureImage" class="dec-scene-img" alt="Dampak" />
                        <div v-else class="dec-scene-waiting">
                            <div class="dec-waiting-pulse"></div>
                            <span>Menunggu Keputusan...</span>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- Narration and Buttons -->
        <div class="dec-bottom">
            <!-- Bubble -->
            <div class="dec-bubble">
                <div class="dec-bubble-icon">
                    <MessageCircle :size="24" color="#1cb0f6" :stroke-width="2.5" />
                </div>
                <p class="dec-bubble-text">
                    {{ displayedFeedback }}<span class="dec-cursor" v-if="displayedFeedback">|</span>
                </p>
            </div>

            <!-- Buttons -->
            <div class="dec-btns">
                <button
                    v-for="(opt, idx) in simulation.options"
                    :key="'btn-'+idx"
                    class="dec-btn"
                    :class="{ 'dec-btn-active': activeOptionIndex === idx }"
                    :style="{
                        background: getColor(opt.button_color).bg,
                        borderColor: getColor(opt.button_color).border,
                        color: getColor(opt.button_color).text,
                        boxShadow: activeOptionIndex === idx ? 'none' : `0 4px 0 0 ${getColor(opt.button_color).border}`
                    }"
                    @click="selectOption(idx)"
                >
                    {{ opt.button_label }}
                </button>
            </div>
        </div>
    </div>
    <div v-else class="dec-empty">Data simulasi belum tersedia.</div>
</template>

<style scoped>
.dec-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Header */
.dec-header { text-align: center; }
.dec-title {
    font-size: 24px;
    font-weight: 900;
    color: #1cb0f6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Scenes */
.dec-scenes {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
}
.dec-scene {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.dec-scene-badge {
    font-size: 14px;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    padding: 6px 20px;
    border-radius: 99px;
}
.dec-scene-card {
    width: 100%;
    aspect-ratio: 4/3;
    background: #f1f5f9;
    border: 2px solid #cbd5e1;
    border-bottom-width: 5px;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}
.dec-scene-active {
    border-color: #1cb0f6;
    box-shadow: 0 0 0 4px rgba(28,176,246,0.15);
}
.dec-scene-img { width: 100%; height: 100%; object-fit: cover; }
.dec-scene-empty { font-weight: 800; color: #94a3b8; font-size: 14px; }
.dec-scene-waiting {
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    color: #94a3b8; font-weight: 800; font-size: 14px; text-align: center; padding: 20px;
}
.dec-waiting-pulse {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: #e0f2fe;
    border: 4px solid #bae6fd;
    animation: pingPulse 1.5s ease-in-out infinite;
}
@keyframes pingPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.15)} }

/* Arrow */
.dec-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: arrowBounceX 1.5s infinite;
}
@keyframes arrowBounceX { 0%,100%{transform:translateX(0)} 50%{transform:translateX(6px)} }
@keyframes arrowBounceY { 0%,100%{transform:translateY(0)} 50%{transform:translateY(6px)} }

/* Bottom */
.dec-bottom {
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: center;
    margin-top: 8px;
}

/* Bubble */
.dec-bubble {
    width: 100%;
    max-width: 600px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 20px;
    padding: 16px 20px;
}
.dec-bubble-icon {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: #ffffff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 0 0 #bae6fd;
    flex-shrink: 0;
}
.dec-bubble-text {
    font-size: 16px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1.5;
    min-height: 48px;
}
.dec-cursor {
    display: inline-block;
    animation: blink 0.8s step-start infinite;
    color: #1cb0f6;
    font-weight: 900;
}
@keyframes blink { 50%{opacity:0} }

/* Buttons */
.dec-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    width: 100%;
}
.dec-btn {
    padding: 14px 24px;
    border-radius: 16px;
    font-size: 15px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.15s ease;
    border-style: solid;
    border-width: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
}
.dec-btn:hover { filter: brightness(1.05); transform: translateY(-2px); }
.dec-btn:active, .dec-btn-active {
    transform: translateY(4px);
    box-shadow: none !important;
}

/* Transitions */
.fade-enter-active,.fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from,.fade-leave-to { opacity: 0; }

.dec-empty { text-align: center; color: #94a3b8; font-weight: 800; padding: 40px; font-size: 16px; }

/* Mobile */
@media (max-width: 640px) {
    .dec-scenes { flex-direction: column; }
    .dec-scene { width: 100%; }
    .dec-arrow { animation: arrowBounceY 1.5s infinite; padding: 4px 0; }
    .dec-btns { flex-direction: column; width: 100%; }
    .dec-btn { width: 100%; }
}
</style>
