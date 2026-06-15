<script setup>
import { ref, watch } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';

const props = defineProps({
    quiz: { type: Object, required: true },
});
const emit = defineEmits(['update-answer']);

const localAnswers = ref({});
const onInput = (questionId, event) => {
    localAnswers.value[questionId] = event.target.value;
    emit('update-answer', { questionId, value: event.target.value });
};

const iconColors = ['#1cb0f6', '#58cc02', '#ffc800', '#ff4b4b', '#a855f7'];
const bgColors   = ['#f0f9ff', '#f0fff0', '#fffbeb', '#fff5f5', '#faf5ff'];
const bdColors   = ['#bae6fd', '#bbf7d0', '#fde68a', '#fecaca', '#e9d5ff'];
</script>

<template>
    <div class="ref-wrap">

        <!-- Flowchart Row -->
        <div class="ref-flow" v-if="quiz.flowchart_data && quiz.flowchart_data.length > 0">
            <div class="ref-flow-inner">
                <template v-for="(item, idx) in quiz.flowchart_data" :key="'flow-'+idx">
                    <!-- Node -->
                    <div class="ref-node">
                        <div class="ref-node-circle">
                            <img v-if="item.image" :src="`/storage/${item.image}`" class="ref-node-img" :alt="item.title" />
                            <component v-else-if="LucideIcons[item.fallback_icon]" :is="LucideIcons[item.fallback_icon]" class="ref-node-icon" />
                            <span v-else class="ref-node-text">{{ item.fallback_icon || item.title }}</span>
                        </div>
                        <span class="ref-node-label">{{ item.title }}</span>
                    </div>

                    <!-- Arrow -->
                    <div v-if="idx < quiz.flowchart_data.length - 1" class="ref-arrow">
                        <ArrowRight :size="20" color="#1cb0f6" :stroke-width="2.5" />
                    </div>
                </template>
            </div>
        </div>

        <!-- Section title -->
        <div class="ref-section-title">
            <div class="ref-section-line"></div>
            <span>Refleksi Ilmiah</span>
            <div class="ref-section-line"></div>
        </div>

        <!-- Question Cards -->
        <div class="ref-questions">
            <div
                v-for="(q, idx) in quiz.questions"
                :key="q.id"
                class="ref-qcard"
                :style="{
                    background: bgColors[idx % bgColors.length],
                    borderColor: bdColors[idx % bdColors.length],
                }"
            >
                <!-- Number badge -->
                <div class="ref-qnum" :style="{ background: iconColors[idx % iconColors.length] }">{{ idx + 1 }}</div>

                <!-- Question text -->
                <p class="ref-qtext">{{ q.question_text }}</p>

                <!-- Textarea -->
                <div class="ref-textarea-wrap" :style="{ borderColor: bdColors[idx % bdColors.length] }">
                    <textarea
                        :value="localAnswers[q.id]"
                        @input="(e) => onInput(q.id, e)"
                        class="ref-textarea"
                        placeholder="Tulis jawabanmu di sini..."
                        rows="3"
                    ></textarea>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.ref-wrap {
    font-family: 'Nunito', sans-serif;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Flowchart */
.ref-flow {
    background: #fff;
    border: 2px solid #e5e5e5;
    border-radius: 16px;
    padding: 16px 12px;
    overflow-x: auto;
}
.ref-flow-inner {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: fit-content;
    margin: 0 auto;
    justify-content: center;
}

/* Node */
.ref-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.ref-node-circle {
    width: 64px; height: 64px;
    border-radius: 50%;
    border: 3px solid #bae6fd;
    background: #fff;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 10px rgba(28,176,246,0.15);
    transition: transform 0.2s;
}
.ref-node-circle:hover { transform: scale(1.08); }
.ref-node-img { width: 100%; height: 100%; object-fit: cover; }
.ref-node-icon { width: 28px; height: 28px; color: #1cb0f6; }
.ref-node-text { font-size: 10px; font-weight: 800; color: #666; text-align: center; padding: 2px; }
.ref-node-label {
    font-size: 11px;
    font-weight: 800;
    color: #3c3c3c;
    text-align: center;
    max-width: 72px;
    line-height: 1.2;
}

/* Arrow */
.ref-arrow { flex-shrink: 0; display: flex; align-items: center; }

/* Section Title */
.ref-section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #777;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.ref-section-line { flex: 1; height: 1.5px; background: #e5e5e5; border-radius: 2px; }

/* Questions */
.ref-questions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
}
.ref-qcard {
    position: relative;
    border-radius: 16px;
    border: 2px solid;
    border-bottom: 5px solid;
    padding: 18px 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: box-shadow 0.2s;
    animation: slideUp 0.35s ease-out;
}
.ref-qcard:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
@keyframes slideUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

.ref-qnum {
    position: absolute;
    top: -10px; left: 14px;
    width: 24px; height: 24px;
    border-radius: 50%;
    color: #fff;
    font-size: 12px;
    font-weight: 900;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    border: 2.5px solid #fff;
}

.ref-qtext {
    font-size: 13px;
    font-weight: 800;
    color: #3c3c3c;
    line-height: 1.5;
    min-height: 40px;
}

/* Textarea */
.ref-textarea-wrap {
    border-radius: 12px;
    border: 2px solid;
    overflow: hidden;
    background: rgba(255,255,255,0.85);
    transition: box-shadow 0.2s;
}
.ref-textarea-wrap:focus-within {
    box-shadow: 0 0 0 3px rgba(28,176,246,0.15);
    border-color: #1cb0f6 !important;
}
.ref-textarea {
    width: 100%;
    padding: 10px 12px;
    font-family: 'Nunito', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #3c3c3c;
    background: transparent;
    border: none;
    outline: none;
    resize: none;
    line-height: 1.5;
}
.ref-textarea::placeholder { color: #bbb; font-weight: 600; }

/* Mobile */
@media (max-width: 480px) {
    .ref-questions { grid-template-columns: 1fr; }
    .ref-node-circle { width: 52px; height: 52px; }
}
</style>
