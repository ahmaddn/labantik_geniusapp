<script setup>
import { ref, computed, watch } from 'vue';

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
        <div class="dec-title-row">
            <h2 class="dec-title">{{ simulation.title || 'Simulasi Keputusan' }}</h2>
        </div>

        <!-- Images: before → after -->
        <div class="dec-scenes">
            <!-- Before -->
            <div class="dec-scene">
                <div class="dec-scene-label">{{ simulation.initial_state_title || 'Hari Ini' }}</div>
                <div class="dec-scene-img-wrap">
                    <img v-if="simulation.initial_state_image" :src="`/storage/${simulation.initial_state_image}`" class="dec-scene-img" alt="Kondisi Awal" />
                    <div v-else class="dec-scene-placeholder">
                        <span>Gambar Belum Tersedia</span>
                    </div>
                </div>
            </div>

            <!-- Arrow -->
            <div class="dec-arrow">
                <svg viewBox="0 0 40 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 12H36M28 4l10 8-10 8" stroke="#1cb0f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <!-- After -->
            <div class="dec-scene">
                <div class="dec-scene-label">{{ simulation.future_state_title || 'Masa Depan' }}</div>
                <div class="dec-scene-img-wrap" :class="{ 'dec-scene-active': activeOptionIndex !== null }">
                    <Transition name="fade" mode="out-in">
                        <img v-if="currentFutureImage" :key="currentFutureImage" :src="currentFutureImage" class="dec-scene-img" alt="Dampak" />
                        <div v-else class="dec-scene-placeholder">
                            <div class="dec-waiting-dot"></div>
                            <span>Menunggu Pilihanmu…</span>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- Feedback bubble -->
        <div class="dec-bubble-row">
            <div class="dec-bubble">
                <svg class="dec-bubble-icon" viewBox="0 0 24 24" fill="none" stroke="#1cb0f6" stroke-width="2.5" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p class="dec-bubble-text">{{ displayedFeedback }}<span class="dec-cursor" v-if="displayedFeedback">|</span></p>
            </div>
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
                    boxShadow: `0 4px 0 ${getColor(opt.button_color).border}`
                }"
                @click="selectOption(idx)"
            >
                <svg v-if="activeOptionIndex === idx" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ opt.button_label }}
            </button>
        </div>
    </div>

    <div v-else class="dec-empty">Data simulasi belum tersedia.</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.dec-wrap {
    font-family: 'Nunito', sans-serif;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 4px 0;
}

/* Title */
.dec-title-row { text-align: center; }
.dec-title {
    font-size: clamp(1rem, 3vw, 1.35rem);
    font-weight: 900;
    color: #1cb0f6;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Scenes */
.dec-scenes {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
}
.dec-scene { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.dec-scene-label {
    font-size: 12px;
    font-weight: 800;
    color: #777;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    background: #fff;
    border: 2px solid #e5e5e5;
    border-radius: 99px;
    padding: 3px 14px;
}
.dec-scene-img-wrap {
    width: 100%;
    aspect-ratio: 4/3;
    border-radius: 16px;
    border: 3px solid #e5e5e5;
    background: #f7f7f7;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: border-color 0.3s, box-shadow 0.3s;
}
.dec-scene-active {
    border-color: #1cb0f6;
    box-shadow: 0 0 0 3px rgba(28,176,246,0.2);
}
.dec-scene-img { width: 100%; height: 100%; object-fit: cover; }
.dec-scene-placeholder {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    color: #bbb; font-weight: 700; font-size: 13px; text-align: center; padding: 12px;
}
.dec-waiting-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: #dbeafe;
    animation: ping 1.2s ease-in-out infinite;
}
@keyframes ping { 0%,100%{transform:scale(1);opacity:0.7} 50%{transform:scale(1.4);opacity:0.3} }

/* Arrow */
.dec-arrow {
    flex: 0 0 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dec-arrow svg { width: 40px; height: 24px; animation: arrowBounce 1.4s ease-in-out infinite; }
@keyframes arrowBounce { 0%,100%{transform:translateX(0)} 50%{transform:translateX(5px)} }

/* Bubble */
.dec-bubble-row { width: 100%; }
.dec-bubble {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 14px;
    padding: 12px 16px;
}
.dec-bubble-icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; }
.dec-bubble-text {
    font-size: 14px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1.5;
    min-height: 40px;
}
.dec-cursor {
    display: inline-block;
    animation: blink 0.7s step-start infinite;
    margin-left: 2px;
    font-weight: 900;
    color: #1cb0f6;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

/* Buttons */
.dec-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}
.dec-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 22px;
    border-radius: 14px;
    border: 2px solid transparent;
    border-bottom-width: 4px;
    font-family: 'Nunito', sans-serif;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.12s cubic-bezier(0.34,1.56,0.64,1);
}
.dec-btn:hover { filter: brightness(1.07); transform: translateY(-2px); }
.dec-btn:active { transform: translateY(2px); box-shadow: none !important; }
.dec-btn-active { transform: translateY(2px); box-shadow: none !important; filter: brightness(1.05); }

/* Transitions */
.fade-enter-active,.fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from,.fade-leave-to { opacity: 0; }

/* Empty */
.dec-empty { text-align: center; color: #aaa; font-weight: 700; padding: 40px; }

/* Mobile */
@media (max-width: 520px) {
    .dec-scenes { flex-direction: column; gap: 8px; }
    .dec-arrow { transform: rotate(90deg); }
    .dec-scene { width: 100%; }
}
</style>
