<script setup>
import { ref, computed, watch, onMounted, reactive } from 'vue';

const props = defineProps({
    quiz: Object,
    modelValue: [String, Number, Array]
});

const emit = defineEmits(['update-answer']);

// Derived from config
const title = computed(() => props.quiz?.title || 'Simulasi Interaktif');
const variables = computed(() => props.quiz?.variables || []);
const levels = computed(() => props.quiz?.levels || []);

// State
const sliderValues = reactive({});
// Initialize to mid value 2 for each slider (1=min, 2=mid, 3=max)
variables.value.forEach((v, idx) => {
    sliderValues[idx] = 1;
});

// Calculate "dangerScore" generically based on sum of sliders
const dangerScore = computed(() => {
    let sum = 0;
    for (let key in sliderValues) {
        sum += sliderValues[key];
    }
    return sum;
});

// Map dangerScore to level index
const currentLevelData = computed(() => {
    if (levels.value.length === 0) return null;
    
    // We map the sum to the index proportionally
    const maxPossibleScore = variables.value.length * 3;
    const minPossibleScore = variables.value.length * 1;
    
    // If there are no variables, just show first level
    if (maxPossibleScore === 0) return levels.value[0];
    
    // Normalize score between 0 and 1
    let normalized = (dangerScore.value - minPossibleScore) / (maxPossibleScore - minPossibleScore || 1);
    
    // Map to array index
    let maxIndex = levels.value.length - 1;
    let index = Math.round(normalized * maxIndex);
    
    return levels.value[index];
});

// Calculate status for UI generically
const isDanger = computed(() => {
    const maxScore = variables.value.length * 3;
    const threshold = Math.floor(maxScore * 0.8); // Top 20%
    return dangerScore.value >= threshold;
});

const isWarning = computed(() => {
    const maxScore = variables.value.length * 3;
    const minPossibleScore = variables.value.length * 1;
    const thresholdHigh = Math.floor(maxScore * 0.8);
    const thresholdMid = Math.floor((maxScore + minPossibleScore) / 2); // Midpoint
    return dangerScore.value >= thresholdMid && dangerScore.value < thresholdHigh;
});

// UI Styling
const statusColor = computed(() => {
    if (isDanger.value) return 'text-red-600';
    if (isWarning.value) return 'text-yellow-600';
    return 'text-green-600';
});
const statusBg = computed(() => {
    if (isDanger.value) return 'bg-red-100 border-red-300';
    if (isWarning.value) return 'bg-yellow-100 border-yellow-300';
    return 'bg-green-100 border-green-300';
});
const statusText = computed(() => {
    if (isDanger.value) return 'BAHAYA';
    if (isWarning.value) return 'WASPADA';
    return 'AMAN / NORMAL';
});

const levelImage = computed(() => {
    return currentLevelData.value?.image ? `/storage/${currentLevelData.value.image}` : null;
});

const levelNarration = computed(() => {
    return currentLevelData.value?.narration || 'Ayo ubah penggeser di bawah untuk melihat perbedaan dampaknya!';
});

// Update Answer
watch(sliderValues, () => {
    let valString = Object.values(sliderValues).join('-');
    emit('update-answer', { value: valString });
});

onMounted(() => {
    let valString = Object.values(sliderValues).join('-');
    emit('update-answer', { value: valString });
});

</script>

<template>
    <div class="double-slider-container w-full max-w-4xl mx-auto p-4 md:p-8 bg-white/90 backdrop-blur rounded-3xl border-4 border-white/50 shadow-xl overflow-hidden flex flex-col gap-6">
        
        <h2 class="text-2xl md:text-3xl font-extrabold text-center text-slate-800 drop-shadow-sm mb-2">
            {{ title }}
        </h2>

        <!-- Main Display Area -->
        <div class="main-display flex flex-col md:flex-row gap-6">
            
            <!-- Visual Representation -->
            <div class="visual-box flex-1 relative rounded-2xl overflow-hidden border-4 border-slate-200 shadow-inner bg-slate-100 min-h-[300px] flex items-center justify-center transition-all duration-500"
                 :class="{'animate-pulse border-red-400': isDanger}">
                 
                <!-- Image or placeholder -->
                <img v-if="levelImage" :src="levelImage" class="w-full h-full object-cover transition-opacity duration-500 absolute inset-0 z-10" />
                <div v-else class="text-slate-400 font-bold z-10 text-center p-4">Gambar Simulasi<br>(Pilih Level)</div>

                <!-- Status Badge Overlay -->
                <div class="absolute top-4 right-4 z-30 px-4 py-2 rounded-xl font-bold border-2 shadow-lg backdrop-blur-md"
                     :class="statusBg + ' ' + statusColor">
                    Status: {{ statusText }}
                </div>
            </div>

            <!-- Narration Box -->
            <div class="narration-box w-full md:w-1/3 bg-slate-50 border-2 border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full mb-4 flex items-center justify-center text-blue-500 text-4xl shadow-inner relative">
                    💡
                    <div v-if="isDanger" class="absolute -top-2 -right-2 text-3xl animate-bounce">⚠️</div>
                </div>
                <p class="text-slate-700 font-bold text-lg italic mb-2">
                    "{{ currentLevelData?.level_name || 'Amati Perubahan' }}"
                </p>
                <p class="text-slate-600 font-medium leading-relaxed">
                    {{ levelNarration }}
                </p>
                <!-- Dynamic Metric Value Instead of Water Debit -->
                <div v-if="currentLevelData?.metric_value" class="mt-4 inline-block px-4 py-2 bg-blue-50 text-blue-700 font-bold rounded-full border border-blue-200 text-sm">
                    {{ currentLevelData.metric_value }}
                </div>
            </div>
        </div>

        <!-- Controls Area -->
        <div class="controls-area bg-slate-100 p-6 rounded-2xl border-2 border-slate-200 shadow-sm flex flex-col gap-8 mt-2">
            <div v-if="variables.length === 0" class="text-center text-slate-400 font-bold">
                Belum ada variabel penggeser.
            </div>
            
            <!-- Loop through N sliders -->
            <div v-for="(v, idx) in variables" :key="idx" class="slider-group">
                <div class="flex justify-between items-center mb-4">
                    <!-- Alternate colors based on index -->
                    <span class="font-bold text-lg px-3 py-1 rounded-lg"
                          :class="(idx % 2 === 0) ? 'text-blue-800 bg-blue-100' : 'text-green-800 bg-green-100'">
                        {{ v.name || `Variabel ${idx + 1}` }}
                    </span>
                    <span class="font-bold text-slate-500">
                        {{ sliderValues[idx] === 1 ? v.min_label : (sliderValues[idx] === 3 ? v.max_label : 'Sedang') }}
                    </span>
                </div>
                <div class="relative w-full px-2">
                    <input type="range" min="1" max="3" step="1" v-model.number="sliderValues[idx]" 
                           class="custom-slider w-full h-4 rounded-full appearance-none outline-none focus:ring-4"
                           :class="(idx % 2 === 0) ? 'bg-blue-200 focus:ring-blue-300 thumb-blue' : 'bg-green-200 focus:ring-green-300 thumb-green'" />
                    <div class="flex justify-between text-xs font-bold text-slate-400 mt-2 px-1">
                        <span>{{ v.min_label || 'Min' }}</span>
                        <span>{{ v.max_label || 'Max' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Common Thumb styling */
.custom-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 0 4px white, 0 4px 6px -1px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.custom-slider::-moz-range-thumb {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 0 4px white, 0 4px 6px -1px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.custom-slider:active::-webkit-slider-thumb {
    transform: scale(1.1);
}

/* Blue Thumb */
.thumb-blue::-webkit-slider-thumb {
    background: #2563eb; 
}
.thumb-blue::-moz-range-thumb {
    background: #2563eb;
}
.thumb-blue:active::-webkit-slider-thumb {
    background: #1d4ed8;
}

/* Green Thumb */
.thumb-green::-webkit-slider-thumb {
    background: #16a34a; 
}
.thumb-green::-moz-range-thumb {
    background: #16a34a;
}
.thumb-green:active::-webkit-slider-thumb {
    background: #15803d;
}
</style>
