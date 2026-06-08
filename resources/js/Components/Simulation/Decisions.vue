<script setup>
import { ref, computed, watch } from 'vue';
import { ArrowLeft, ArrowRight } from "lucide-vue-next";

const props = defineProps({
    quiz: {
        type: Object,
        required: true,
    },
});

// data `quiz` di sini berisi referensi ke `simulation_decision`
// Namun di Template.vue, data `configs.decisions` dilempar ke `mission.simulation_decisions`
// Tapi `Template.vue` saat ini belum meload itu dari Controller/Resource untuk playground.
// Wait, Template.vue mendapatkan data mission, lalu di steps di generate dari mission.quizzes
// Jika "Simulasi Keputusan" menggunakan tabel sendiri dan bukan "quizzes", kita butuh menyuntikkannya ke `mission.quizzes` di Controller atau menggunakannya sebagai komponen khusus.
// Mari asumsikan bahwa PlaygroundMissionController menyuntikkan `simulation_decisions` ke dalam `mission.quizzes` sebagai tipe 'simulation_decision', 
// ATAU data aslinya ada di `props.quiz` di mana `quiz.items` menyimpan opsi-opsinya.
// Kita perlu menyesuaikan dengan data format yang masuk.

// Anggap data masuk melalui `props.quiz` yang sudah dibentuk oleh resource/controller.
const simulation = computed(() => {
    // Apabila data dikemas di dalam `quiz.data` atau properties sejenis.
    return props.quiz || {};
});

const activeOptionIndex = ref(null);
const mascotImgDefault = "/images/templates/pose_nunjuk.png";

const currentMascotImg = computed(() => {
    if (simulation.value.character_image) {
        return `/storage/${simulation.value.character_image}`;
    }
    return mascotImgDefault;
});

const currentFutureImage = computed(() => {
    if (activeOptionIndex.value !== null && simulation.value.options && simulation.value.options[activeOptionIndex.value]) {
        return `/storage/${simulation.value.options[activeOptionIndex.value].future_state_image}`;
    }
    return null;
});

const currentFeedbackMessage = computed(() => {
    if (activeOptionIndex.value !== null && simulation.value.options && simulation.value.options[activeOptionIndex.value]) {
        return simulation.value.options[activeOptionIndex.value].feedback_message;
    }
    return "Pilih salah satu tindakan di bawah untuk melihat apa yang akan terjadi di masa depan.";
});

const displayedFeedback = ref("");
let typingInterval = null;

const startTyping = (text) => {
    if (typingInterval) clearInterval(typingInterval);
    displayedFeedback.value = "";
    if (!text) return;

    let index = 0;
    typingInterval = setInterval(() => {
        displayedFeedback.value += text.charAt(index);
        index++;
        if (index >= text.length) {
            clearInterval(typingInterval);
        }
    }, 30);
};

watch(currentFeedbackMessage, (newVal) => {
    startTyping(newVal);
}, { immediate: true });

const selectOption = (index) => {
    activeOptionIndex.value = index;
    
    // Create a subtle vibration effect on the mascot container if supported
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
};

// Map button colors
const getColorClass = (colorStr) => {
    switch (colorStr) {
        case 'green': return 'bg-green-500 hover:bg-green-600 border-green-600 text-white';
        case 'yellow': return 'bg-yellow-400 hover:bg-yellow-500 border-yellow-500 text-gray-800';
        case 'red': return 'bg-red-500 hover:bg-red-600 border-red-600 text-white';
        case 'blue': return 'bg-blue-500 hover:bg-blue-600 border-blue-600 text-white';
        default: return 'bg-gray-500 hover:bg-gray-600 border-gray-600 text-white';
    }
};

</script>

<template>
    <div class="decision-container w-full h-full flex flex-col p-4 md:p-8" v-if="simulation">
        <!-- Header -->
        <div class="text-center mb-6">
            <h2 class="text-xl md:text-3xl font-heading font-black text-blue-900 drop-shadow-sm uppercase tracking-wide">
                {{ simulation.title || 'SIMULASI KEPUTUSAN' }}
            </h2>
        </div>

        <!-- Images Grid -->
        <div class="flex-1 flex flex-col max-w-6xl mx-auto w-full">
            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4 md:gap-6 items-center justify-center mb-6 w-full flex-1">
                
                <!-- Initial State (Left) -->
                <div class="flex flex-col items-center w-full h-full">
                    <h3 class="font-bold text-gray-800 text-lg md:text-xl mb-3 bg-white px-6 py-2 rounded-full shadow-sm border-2 border-gray-200">
                        {{ simulation.initial_state_title || 'HARI INI' }}
                    </h3>
                    <div class="relative rounded-3xl overflow-hidden border-4 border-white bg-gray-100 shadow-xl w-full flex-1 min-h-[250px] flex items-center justify-center">
                        <img 
                            v-if="simulation.initial_state_image"
                            :src="`/storage/${simulation.initial_state_image}`" 
                            alt="Status Awal"
                            class="absolute inset-0 w-full h-full object-cover"
                        />
                        <div v-else class="text-gray-400 font-bold">Gambar Tidak Tersedia</div>
                    </div>
                </div>

                <!-- Animated Arrow Middle -->
                <div class="hidden md:flex flex-col items-center justify-center z-10 px-2">
                    <div class="bg-white p-3 rounded-full shadow-lg border-4 border-blue-100 animate-bounce transition-all duration-300">
                        <ArrowRight class="w-8 h-8 text-blue-500" />
                    </div>
                </div>

                <!-- Future State (Right) -->
                <div class="flex flex-col items-center w-full h-full relative">
                    <h3 class="font-bold text-gray-800 text-lg md:text-xl mb-3 bg-white px-6 py-2 rounded-full shadow-sm border-2 border-gray-200">
                        {{ simulation.future_state_title || 'MASA DEPAN' }}
                    </h3>
                    <div class="relative rounded-3xl overflow-hidden border-4 border-white bg-gray-100 shadow-xl w-full flex-1 min-h-[250px] flex items-center justify-center transition-all duration-500 transform" :class="activeOptionIndex !== null ? 'ring-4 ring-blue-300 scale-[1.02]' : ''">
                        <transition name="fade" mode="out-in">
                            <img 
                                v-if="currentFutureImage"
                                :key="currentFutureImage"
                                :src="currentFutureImage" 
                                alt="Status Masa Depan"
                                class="absolute inset-0 w-full h-full object-cover"
                            />
                            <div v-else class="absolute inset-0 w-full h-full object-cover backdrop-blur-md bg-white/50 flex flex-col items-center justify-center p-6 text-center text-gray-500">
                                <div class="w-16 h-16 mb-4 rounded-full bg-blue-100 flex items-center justify-center animate-pulse">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full animate-ping"></div>
                                </div>
                                <span class="font-bold text-lg animate-pulse">Menunggu Keputusanmu...</span>
                            </div>
                        </transition>
                    </div>
                </div>

            </div>

            <!-- Bottom Section: Mascot & Buttons -->
            <div class="mt-auto relative w-full pt-4 md:pt-10">
                <!-- Mascot & Bubble Positioned -->
                <div class="flex flex-col md:flex-row items-end gap-4 w-full">
                    
                    <div class="flex items-end gap-4 z-10 w-full md:w-auto relative mb-4 md:mb-0 md:-ml-8 lg:-ml-12">
                        <img :src="currentMascotImg" class="w-24 md:w-36 object-contain drop-shadow-xl animate-float" alt="Mascot" />
                        
                        <div class="narration-bubble absolute bottom-full left-16 md:left-24 mb-4 bg-white border-4 border-blue-300 rounded-3xl rounded-bl-none p-4 md:p-6 shadow-xl w-[280px] md:w-[400px]">
                            <div class="absolute -left-4 bottom-0 w-6 h-6 bg-white border-l-4 border-b-4 border-blue-300 transform rotate-45 translate-y-1/2 translate-x-2"></div>
                            <p class="text-gray-700 font-bold text-sm md:text-base leading-relaxed whitespace-pre-wrap min-h-[3rem]">
                                {{ displayedFeedback }}<span class="animate-ping font-black text-blue-500 ml-1">_</span>
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex-1 flex flex-wrap justify-center md:justify-end gap-3 md:gap-4 items-center mb-2 z-20">
                        <button 
                            v-for="(opt, idx) in simulation.options" 
                            :key="'opt-'+idx"
                            @click="selectOption(idx)"
                            class="action-btn px-6 py-3 rounded-2xl border-b-4 font-bold text-base md:text-lg transition-all shadow-md active:border-b-0 active:translate-y-1"
                            :class="[
                                getColorClass(opt.button_color),
                                activeOptionIndex === idx ? 'ring-4 ring-offset-2 ring-blue-300 scale-105' : 'hover:scale-105 opacity-90 hover:opacity-100'
                            ]"
                        >
                            {{ opt.button_label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="w-full h-full flex items-center justify-center">
        <p class="text-gray-500 font-bold text-xl">Data simulasi belum tersedia.</p>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.narration-bubble {
    animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes bounceIn {
    0% { opacity: 0; transform: scale(0.8) translateY(20px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}
</style>
