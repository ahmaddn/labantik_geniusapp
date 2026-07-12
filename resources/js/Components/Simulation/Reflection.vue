<script setup>
import { ref, watch } from 'vue';
import * as Icons from 'lucide-vue-next';
import { ChevronRight, HelpCircle, Edit3, Award } from 'lucide-vue-next';

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
                        <!-- Step Badge -->
                        <span class="ref-step-badge">Langkah {{ idx + 1 }}</span>
                        
                        <div class="ref-card-icon" :style="step.image ? 'background:transparent; border:none;' : ''">
                            <img v-if="step.image" :src="getImageUrl(step.image)" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" />
                            <component v-else :is="getIcon(step.fallback_icon)" :size="22" color="#eab308" :stroke-width="2.5" />
                        </div>
                        <p class="ref-card-text">{{ step.title }}</p>
                    </div>
                    <!-- Chevron pointing right -->
                    <ChevronRight v-if="idx < quiz.flowchart_data.length - 1" class="ref-arrow-right" :size="24" color="#cbd5e1" :stroke-width="3" />
                </div>
            </div>
        </div>

        <!-- Question & Input Section (Side by side on desktop, stacked on mobile) -->
        <div class="ref-bottom-row">
            <!-- Left Side: Refleksi Question Bubble -->
            <div class="ref-bubble-side">
                <div class="ref-bubble">
                    <div class="ref-bubble-icon">
                        <HelpCircle :size="24" color="#1cb0f6" :stroke-width="2.5" />
                    </div>
                    <div class="ref-bubble-content">
                        <span class="ref-badge-top">Tantangan Berpikir</span>
                        <h3 class="ref-q-title">Mari Refleksikan</h3>
                        <p class="ref-q-text">{{ question?.question_text }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Creative Input Box -->
            <div class="ref-input-side">
                <div class="ref-input-wrap">
                    <div class="ref-input-header">
                        <div class="ref-header-circle">
                            <Edit3 :size="16" color="#3b82f6" :stroke-width="2.5" />
                        </div>
                        <span class="ref-header-title">Tuliskan kesimpulan hebatmu:</span>
                    </div>
                    <textarea 
                        v-model="studentAnswer" 
                        class="ref-textarea" 
                        placeholder="Menurut saya, hal ini bisa terjadi karena..."
                        rows="4"
                    ></textarea>
                    
                    <div class="ref-footer-hint">
                        <Award :size="13" color="#10b981" :stroke-width="2.5" />
                        <span>Tuliskan dengan kalimatmu sendiri, kamu pasti bisa!</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.ref-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 28px;
    font-family: 'Nunito', sans-serif;
}

/* Flowchart Scroll Container */
.ref-flow-scroll {
    width: 100%;
    overflow-x: auto;
    padding: 12px 4px 16px;
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

/* Flowchart Step Card */
.ref-card {
    background: #ffffff;
    border: 3px solid #e2e8f0;
    border-bottom-width: 6px;
    border-radius: 24px;
    padding: 20px 16px 16px;
    width: 180px;
    height: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 8px;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    transition: transform 0.2s, border-color 0.2s;
    flex-shrink: 0;
}
.ref-card:hover {
    transform: translateY(-2px);
    border-color: #bae6fd;
}

.ref-step-badge {
    position: absolute;
    top: -12px;
    background: #1cb0f6;
    color: white;
    font-size: 10px;
    font-weight: 900;
    padding: 3px 10px;
    border-radius: 12px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(28, 176, 246, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ref-card-icon {
    width: 44px; 
    height: 44px;
    border-radius: 50%;
    background: #fefce8;
    border: 2.5px solid #fef08a;
    display: flex; 
    align-items: center; 
    justify-content: center;
    box-shadow: 0 3px 0 0 #fef08a;
    flex-shrink: 0;
}
.ref-card-text { 
    font-size: 13px; 
    font-weight: 800; 
    color: #475569; 
    line-height: 1.3; 
}
.ref-arrow-right { 
    flex-shrink: 0; 
    animation: bounceArrowRight 1.5s infinite ease-in-out;
}

@keyframes bounceArrowRight {
    0%, 100% { transform: translateX(0); opacity: 0.7; }
    50% { transform: translateX(4px); opacity: 1; }
}

/* Bottom Split Layout */
.ref-bottom-row {
    display: flex;
    gap: 24px;
    align-items: stretch;
    width: 100%;
}

.ref-bubble-side {
    flex: 1;
    display: flex;
}

.ref-input-side {
    flex: 1.2;
    display: flex;
}

/* Mascot Speak Bubble */
.ref-bubble {
    width: 100%;
    background: #f0f9ff;
    border: 3px solid #bae6fd;
    border-bottom-width: 6px;
    border-radius: 28px;
    padding: 24px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(2, 132, 199, 0.05);
}

.ref-bubble-icon {
    width: 46px; 
    height: 46px;
    border-radius: 50%;
    background: #ffffff;
    border: 2.5px solid #bae6fd;
    display: flex; 
    align-items: center; 
    justify-content: center;
    box-shadow: 0 3px 0 0 #bae6fd;
    flex-shrink: 0;
}

.ref-bubble-content { 
    flex: 1; 
}

.ref-badge-top {
    font-size: 10px;
    font-weight: 900;
    color: #0284c7;
    background: #e0f2fe;
    padding: 2px 8px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ref-q-title { 
    font-size: 16px; 
    font-weight: 900; 
    color: #0369a1; 
    margin: 8px 0 4px;
}

.ref-q-text { 
    font-size: 15px; 
    font-weight: 800; 
    color: #334155; 
    line-height: 1.5; 
}

/* Input Card */
.ref-input-wrap {
    width: 100%;
    background: #ffffff;
    border: 3px solid #e2e8f0;
    border-bottom-width: 6px;
    border-radius: 28px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.ref-input-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ref-header-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #bfdbfe;
}

.ref-header-title {
    font-size: 14px;
    font-weight: 900;
    color: #1e293b;
}

.ref-textarea {
    width: 100%;
    background: #f8fafc;
    border: 3px solid #cbd5e1;
    border-radius: 18px;
    padding: 16px;
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    resize: none;
    outline: none;
    transition: all 0.25s;
    font-family: inherit;
    line-height: 1.5;
}

.ref-textarea:focus {
    background: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.15);
}

.ref-textarea::placeholder { 
    color: #94a3b8; 
    font-weight: 600; 
}

.ref-footer-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    color: #059669;
    background: #ecfdf5;
    padding: 6px 12px;
    border-radius: 12px;
    border: 1px solid #a7f3d0;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .ref-wrap {
        gap: 20px;
    }
    
    .ref-bottom-row { 
        flex-direction: column; 
        gap: 20px; 
    }
    
    .ref-bubble-side,
    .ref-input-side {
        width: 100%;
    }
    
    .ref-flow-scroll {
        padding: 10px 4px 14px;
    }
    
    .ref-flow-item {
        gap: 12px;
    }
    
    .ref-card {
        width: 140px;
        height: 120px;
        padding: 16px 8px 8px;
    }
    
    .ref-card-icon {
        width: 38px;
        height: 38px;
        border-width: 2px;
    }
    
    .ref-card-text {
        font-size: 11px;
    }
    
    .ref-arrow-right {
        width: 16px;
        height: 16px;
    }
}
</style>
