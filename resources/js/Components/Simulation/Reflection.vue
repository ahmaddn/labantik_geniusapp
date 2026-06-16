<script setup>
import { ref, watch } from 'vue';
import * as Icons from 'lucide-vue-next';
import { ArrowRight, MessageCircle, Edit3 } from 'lucide-vue-next';

const props = defineProps({
    quiz: { type: Object, required: true },
    question: { type: Object, required: false },
    modelValue: [String, Number, Array],
});

const emit = defineEmits(['update-answer']);
const studentAnswer = ref(props.modelValue || '');

watch(studentAnswer, (newVal) => {
    emit('update-answer', { value: newVal, questionId: props.question?.id });
});

const getIcon = (name) => {
    return Icons[name] || Icons.Lightbulb;
};

const getImageUrl = (path) => {
    if (!path) return '';
    if (typeof path !== 'string') return '';
    if (path.startsWith('http') || path.startsWith('/')) return path;
    return '/storage/' + path;
};
</script>

<template>
    <div class="ref-wrap">
        <!-- Flowchart Horizontal Scroll -->
        <div class="ref-flow-scroll" v-if="quiz.flowchart_data && quiz.flowchart_data.length > 0">
            <div class="ref-flow">
                <div v-for="(step, idx) in quiz.flowchart_data" :key="idx" class="ref-flow-item">
                    <div class="ref-card">
                        <div class="ref-card-icon" :style="step.image ? 'background:transparent; border:none;' : ''">
                            <img v-if="step.image" :src="getImageUrl(step.image)" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" />
                            <component v-else :is="getIcon(step.fallback_icon)" :size="24" color="#ffc800" :stroke-width="2.5" />
                        </div>
                        <p class="ref-card-text">{{ step.title }}</p>
                    </div>
                    <ArrowRight v-if="idx < quiz.flowchart_data.length - 1" class="ref-arrow" :size="32" color="#cbd5e1" :stroke-width="3" />
                </div>
            </div>
        </div>

        <!-- Question & Input -->
        <div class="ref-bottom">
            <div class="ref-bubble">
                <div class="ref-bubble-icon">
                    <MessageCircle :size="24" color="#1cb0f6" :stroke-width="2.5" />
                </div>
                <div class="ref-bubble-content">
                    <h3 class="ref-q-title">Pertanyaan Refleksi</h3>
                    <p class="ref-q-text">{{ question?.question_text }}</p>
                </div>
            </div>

            <div class="ref-input-wrap">
                <div class="ref-input-header">
                    <Edit3 :size="18" color="#64748b" :stroke-width="2.5" />
                    <span>Tuliskan kesimpulanmu:</span>
                </div>
                <textarea 
                    v-model="studentAnswer" 
                    class="ref-textarea" 
                    placeholder="Menurut saya, hal ini terjadi karena..."
                    rows="4"
                ></textarea>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ref-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Flowchart */
.ref-flow-scroll {
    width: 100%;
    overflow-x: auto;
    padding: 10px 4px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.ref-flow-scroll::-webkit-scrollbar { height: 8px; }
.ref-flow-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }

.ref-flow {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    min-width: max-content;
}
.ref-flow-item {
    display: flex;
    align-items: center;
    gap: 16px;
}
.ref-card {
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-bottom-width: 4px;
    border-radius: 16px;
    padding: 16px;
    width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
}
.ref-card-icon {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #fefce8;
    border: 2px solid #fde047;
    display: flex; align-items: center; justify-content: center;
}
.ref-card-text { font-size: 14px; font-weight: 800; color: #334155; line-height: 1.4; }
.ref-arrow { flex-shrink: 0; }

/* Bottom */
.ref-bottom {
    display: flex;
    gap: 20px;
    align-items: stretch;
}

/* Bubble */
.ref-bubble {
    flex: 1;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}
.ref-bubble-icon {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #ffffff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 0 0 #bae6fd;
    flex-shrink: 0;
}
.ref-bubble-content { flex: 1; }
.ref-q-title { font-size: 14px; font-weight: 900; color: #0284c7; text-transform: uppercase; margin-bottom: 6px; }
.ref-q-text { font-size: 16px; font-weight: 800; color: #0f172a; line-height: 1.5; }

/* Input */
.ref-input-wrap {
    flex: 1.2;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-radius: 20px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ref-input-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 800;
    color: #64748b;
}
.ref-textarea {
    width: 100%;
    background: #f8fafc;
    border: 2px solid #cbd5e1;
    border-radius: 12px;
    padding: 16px;
    font-size: 15px;
    font-weight: 700;
    color: #334155;
    resize: vertical;
    outline: none;
    transition: all 0.2s;
    font-family: inherit;
}
.ref-textarea:focus {
    background: #ffffff;
    border-color: #1cb0f6;
    box-shadow: 0 0 0 4px rgba(28,176,246,0.15);
}
.ref-textarea::placeholder { color: #94a3b8; font-weight: 600; }

/* Mobile */
@media (max-width: 640px) {
    .ref-bottom { flex-direction: column; align-items: center; }
    .ref-card { width: 100%; max-width: 280px; padding: 12px; }
    .ref-card-text { font-size: 14px; }
}
</style>
