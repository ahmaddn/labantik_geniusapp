<script setup>
import { ref, watch } from 'vue';
import { ArrowRight, Link, CloudRain, Sprout, Lightbulb, MessageSquare } from 'lucide-vue-next';

const props = defineProps({
    quiz: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update-answer']);

// State lokal untuk menyimpan jawaban sebelum di-emit (opsional tapi baik untuk reactivity)
const localAnswers = ref({});

const onInput = (questionId, event) => {
    const value = event.target.value;
    localAnswers.value[questionId] = value;
    emit('update-answer', { questionId, value });
};

// Gambar maskot default jika tidak ada gambar khusus
const mascotImg = "/images/templates/pose_nunjuk.png";

const icons = [Link, CloudRain, Sprout, Lightbulb, MessageSquare];
const getIcon = (index) => {
    return icons[index % icons.length];
};
</script>

<template>
    <div class="reflection-container w-full h-full flex flex-col p-4 md:p-6 overflow-y-auto">
        
        <!-- Header: Judul -->
        <div class="text-center mb-6">
            <h2 class="text-2xl md:text-3xl font-heading font-black text-gray-800 drop-shadow-sm uppercase tracking-wide">
                {{ quiz.title || 'REFLEKSI ILMIAH' }}
            </h2>
        </div>

        <!-- Area Atas: Flowchart & Mascot Kiri -->
        <div class="top-area flex flex-col md:flex-row items-center justify-center gap-6 mb-8 w-full max-w-6xl mx-auto">
            
            <!-- Mascot Kiri -->
            <div class="mascot-left hidden md:flex items-center w-1/4 max-w-[200px]">
                <div class="flex flex-col items-center">
                    <div class="bg-white border-2 border-blue-200 rounded-3xl p-3 text-sm font-medium text-gray-700 shadow-md text-center relative mb-4">
                        {{ quiz.mascot_left_text || 'Ayo perhatikan siklus berikut ini!' }}
                        <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-white border-b-2 border-r-2 border-blue-200 rotate-45"></div>
                    </div>
                    <img :src="mascotImg" class="w-24 object-contain" alt="Si Air" />
                    <span class="font-bold text-blue-600 mt-2">Si Air</span>
                </div>
            </div>

            <!-- Flowchart -->
            <div class="flowchart-container flex-1 flex flex-wrap items-center justify-center gap-2 md:gap-4 w-full">
                <template v-if="quiz.flowchart_data && quiz.flowchart_data.length > 0">
                    <div v-for="(item, idx) in quiz.flowchart_data" :key="'flow-'+idx" class="flex items-center">
                        <!-- Bulatan Flowchart -->
                        <div class="flex flex-col items-center group">
                            <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-white border-4 border-blue-300 shadow-lg flex items-center justify-center overflow-hidden transition-transform transform group-hover:scale-105">
                                <img v-if="item.image" :src="`/storage/${item.image}`" class="w-full h-full object-cover" :alt="item.title" />
                                <span v-else class="text-xs font-bold text-gray-500 text-center px-1">{{ item.title }}</span>
                            </div>
                            <span class="mt-2 font-bold text-gray-800 text-sm md:text-base text-center bg-white/70 px-2 rounded">{{ item.title }}</span>
                        </div>
                        
                        <!-- Panah -->
                        <div v-if="idx < quiz.flowchart_data.length - 1" class="hidden sm:flex flex-col items-center justify-center mx-1 md:mx-3">
                            <ArrowRight class="text-blue-500 w-8 h-8 md:w-10 md:h-10" />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <p class="text-gray-500 italic">Siklus belum tersedia.</p>
                </template>
            </div>
            
            <!-- Mascot Kiri untuk Mobile (muncul di bawah flowchart jika mobile) -->
            <div class="md:hidden flex items-start gap-3 w-full mt-4">
                <img :src="mascotImg" class="w-16 object-contain" alt="Si Air" />
                <div class="bg-white border-2 border-blue-200 rounded-2xl rounded-tl-none p-3 text-xs font-medium text-gray-700 shadow-md">
                    {{ quiz.mascot_left_text || 'Ayo perhatikan siklus berikut ini!' }}
                </div>
            </div>

        </div>

        <!-- Area Bawah: Refleksi Ilmiah -->
        <div class="bottom-area w-full max-w-6xl mx-auto bg-blue-50/80 p-4 md:p-6 rounded-3xl border-2 border-white shadow-sm mt-auto relative">
            <h3 class="text-center font-bold text-xl md:text-2xl text-blue-900 mb-6 drop-shadow-sm">
                Refleksi Ilmiah
            </h3>

            <div class="flex flex-col xl:flex-row gap-6">
                <!-- Grid Cards Pertanyaan -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                    <div 
                        v-for="(q, idx) in quiz.questions" 
                        :key="q.id" 
                        class="question-card bg-white rounded-2xl p-4 shadow-md border-b-4 border-blue-200 relative flex flex-col transition-all hover:shadow-lg"
                    >
                        <!-- Nomor Urut -->
                        <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shadow-md border-2 border-white">
                            {{ idx + 1 }}
                        </div>
                        
                        <!-- Teks Pertanyaan -->
                        <p class="text-sm md:text-base font-bold text-gray-800 text-center mt-3 min-h-[48px]">
                            {{ q.question_text }}
                        </p>
                        
                        <!-- Ikon Penengah -->
                        <div class="flex justify-center my-4 opacity-70">
                            <component :is="getIcon(idx)" class="w-10 h-10 text-blue-500" stroke-width="1.5" />
                        </div>

                        <!-- Textarea Jawaban -->
                        <div class="mt-auto">
                            <textarea 
                                :value="localAnswers[q.id]"
                                @input="(e) => onInput(q.id, e)"
                                class="w-full resize-none border-2 border-gray-200 bg-gray-50 rounded-xl p-3 text-sm focus:outline-none focus:border-blue-400 focus:bg-white transition-colors placeholder-gray-400 h-24"
                                placeholder="Tulis jawabanmu di sini..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Mascot Kanan -->
                <div class="mascot-right hidden xl:flex items-end max-w-[200px] pb-4">
                    <div class="flex items-end gap-3">
                        <div class="bg-white border-2 border-blue-200 rounded-3xl rounded-br-none p-4 text-sm font-medium text-gray-700 shadow-md relative mb-12">
                            {{ quiz.mascot_right_text || 'Isi jawabanmu di kotak ini ya!' }}
                        </div>
                        <img :src="mascotImg" class="w-24 object-contain scale-x-[-1]" alt="Si Air" />
                    </div>
                </div>
            </div>
            
            <!-- Mascot Kanan untuk layar kecil -->
            <div class="xl:hidden flex items-end justify-end mt-6">
                <div class="flex items-end gap-3 max-w-sm">
                    <div class="bg-white border-2 border-blue-200 rounded-2xl rounded-br-none p-3 text-xs md:text-sm font-medium text-gray-700 shadow-md">
                        {{ quiz.mascot_right_text || 'Isi jawabanmu di kotak ini ya!' }}
                    </div>
                    <img :src="mascotImg" class="w-16 md:w-20 object-contain scale-x-[-1]" alt="Si Air" />
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.question-card {
    animation: slideUp 0.4s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
