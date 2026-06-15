<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    quiz: {
        type: Object,
        required: true,
    },
});

// quiz.items berisi array dari perbandingan.
// Karena struktur data kita sekarang dinamis:
// quiz.items = [ { id, explanation, items: [ { toggle_name, label, narration, image } ] } ]

const activeGroupIndex = ref(0);
const activeGroup = computed(() => {
    if (!props.quiz?.items || props.quiz.items.length === 0) return null;
    return props.quiz.items[activeGroupIndex.value];
});

// State untuk mengatur item mana yang sedang aktif (diklik/ditoggle)
const activeItemIndex = ref(0);
const activeItem = computed(() => {
    if (!activeGroup.value?.items || activeGroup.value.items.length === 0) return null;
    return activeGroup.value.items[activeItemIndex.value] || activeGroup.value.items[0];
});

// Reset active item ketika group berubah
watch(activeGroupIndex, () => {
    activeItemIndex.value = 0;
});

const setActiveItem = (index) => {
    activeItemIndex.value = index;
};

// Gambar maskot default
const mascotImg = "/images/templates/pose_nunjuk.png";

</script>

<template>
    <div class="comparisons-container w-full h-full flex flex-col p-4 md:p-8" v-if="activeGroup">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl md:text-4xl font-heading font-black text-green-800 drop-shadow-sm uppercase tracking-wide">
                {{ quiz.title || 'OBSERVASI PERBANDINGAN' }}
            </h2>
            <p v-if="activeGroup.explanation" class="text-gray-600 mt-2 max-w-3xl mx-auto font-medium">
                {{ activeGroup.explanation }}
            </p>
        </div>

        <!-- Images Grid (munculkan dari kiri ke kanan) -->
        <div class="flex-1 flex flex-col justify-center max-w-6xl mx-auto w-full">
            <div 
                class="grid gap-6 items-end justify-center mb-8 w-full"
                :style="{ gridTemplateColumns: `repeat(auto-fit, minmax(280px, 1fr))` }"
            >
                <div 
                    v-for="(item, idx) in activeGroup.items" 
                    :key="idx"
                    class="comparison-item cursor-pointer transition-all duration-300 transform"
                    :class="{ 
                        'scale-105 ring-4 ring-green-400 shadow-xl': activeItemIndex === idx,
                        'opacity-70 hover:opacity-100 hover:scale-100 shadow-md': activeItemIndex !== idx 
                    }"
                    @click="setActiveItem(idx)"
                >
                    <h3 class="text-center font-bold text-gray-800 text-lg md:text-xl mb-3">
                        {{ item.label || `Opsi ${idx + 1}` }}
                    </h3>
                    <div class="relative rounded-2xl overflow-hidden border-4 border-white bg-white">
                        <img 
                            :src="item.image ? `/storage/${item.image}` : '/images/placeholder.jpg'" 
                            :alt="item.label"
                            class="w-full h-48 md:h-64 object-cover object-center"
                        />
                        <!-- Active Indicator -->
                        <div v-if="activeItemIndex === idx" class="absolute top-2 right-2 bg-green-500 text-white p-1.5 rounded-full shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Mascot Narration & Toggles -->
            <div class="flex flex-col md:flex-row items-end justify-between gap-6 mt-auto bg-white/60 p-4 md:p-6 rounded-3xl backdrop-blur-sm border-2 border-white/50 shadow-sm w-full">
                
                <!-- Mascot & Narration Bubble -->
                <div class="flex items-end gap-4 w-full md:w-2/3">
                    <img :src="mascotImg" class="w-24 md:w-32 object-contain drop-shadow-md z-10" alt="Mascot" />
                    <div class="narration-bubble flex-1 bg-white border-2 border-blue-200 rounded-3xl rounded-bl-none p-4 md:p-6 shadow-md relative min-h-[100px] flex items-center">
                        <!-- Tail -->
                        <div class="absolute -left-3 bottom-0 w-6 h-6 bg-white border-l-2 border-b-2 border-blue-200 transform rotate-45 translate-y-1/2"></div>
                        
                        <p class="text-gray-700 font-medium text-sm md:text-base leading-relaxed">
                            {{ activeItem?.narration || 'Pilih salah satu gambar untuk melihat penjelasan detailnya.' }}
                        </p>
                    </div>
                </div>

                <!-- Toggles / Buttons -->
                <div class="flex flex-col gap-3 w-full md:w-auto min-w-[250px]">
                    <button 
                        v-for="(item, idx) in activeGroup.items" 
                        :key="'toggle-'+idx"
                        @click="setActiveItem(idx)"
                        class="toggle-btn w-full flex items-center justify-between px-5 py-3 rounded-full border-2 transition-all font-bold shadow-sm"
                        :class="[
                            activeItemIndex === idx 
                                ? 'bg-white border-green-500 text-green-700' 
                                : 'bg-gray-50 border-gray-200 text-gray-500 hover:bg-white hover:border-green-300'
                        ]"
                    >
                        <span class="truncate pr-2">{{ item.toggle_name || `Tampilan ${idx + 1}` }}</span>
                        
                        <!-- Custom Toggle Switch Icon -->
                        <div 
                            class="w-12 h-6 rounded-full p-1 flex items-center transition-colors duration-300 relative"
                            :class="activeItemIndex === idx ? 'bg-green-500' : 'bg-gray-300'"
                        >
                            <div 
                                class="w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform duration-300"
                                :class="activeItemIndex === idx ? 'translate-x-6' : 'translate-x-0'"
                            ></div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div v-else class="w-full h-full flex items-center justify-center">
        <p class="text-gray-500 font-bold text-xl">Data perbandingan belum tersedia.</p>
    </div>
</template>

<style scoped>
.comparison-item {
    border-radius: 1rem;
}
.narration-bubble {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.toggle-btn:active {
    transform: scale(0.98);
}
</style>
