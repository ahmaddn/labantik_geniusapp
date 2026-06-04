<script setup>
import { ref } from 'vue';
import { CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps({
    quiz: { type: Object, required: true },
});

const clickedObjects = ref(new Set());

const toggleObject = (id) => {
    const newSet = new Set(clickedObjects.value);
    if (!newSet.has(id)) {
        newSet.add(id);
    }
    clickedObjects.value = newSet;
};

const getImageUrl = (path) => {
    if (!path) return '';
    return path.startsWith('http') || path.startsWith('/') ? path : `/storage/${path}`;
};
</script>

<template>
    <div class="simulation-clickable-container p-4 w-full h-full flex flex-col items-center justify-center">
        <!-- Title is handled by Template.vue's question-bubble, but we can render the grid nicely -->
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 w-full max-w-6xl mt-4">
            <div 
                v-for="(obj, index) in quiz.objects" 
                :key="obj.id"
                class="clickable-card-wrapper perspective-1000 cursor-pointer"
                @click="toggleObject(obj.id)"
            >
                <div class="clickable-card w-full h-[280px] md:h-[340px] relative preserve-3d transition-transform duration-700"
                     :class="{ 'rotate-y-180': clickedObjects.has(obj.id) }">
                     
                    <!-- Front (Before Click) -->
                    <div class="card-front absolute inset-0 backface-hidden bg-white rounded-3xl border-[6px] shadow-playful flex flex-col items-center justify-center p-6"
                         :class="obj.is_positive ? 'border-green-400' : 'border-red-400'">
                        <div class="flex-1 w-full flex items-center justify-center mb-4">
                            <img v-if="obj.image" :src="getImageUrl(obj.image)" class="max-w-[140px] max-h-[140px] object-contain drop-shadow-md" />
                            <div v-else class="w-32 h-32 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 font-bold">
                                Tanpa Gambar
                            </div>
                        </div>
                        <h3 class="text-xl md:text-2xl font-bold text-center text-gray-800 font-heading mb-2 leading-tight">
                            {{ obj.name }}
                        </h3>
                        <div class="mt-auto px-5 py-2 bg-blue-50 text-blue-600 rounded-full font-bold text-sm animate-pulse border-2 border-blue-200">
                            Pilih untuk melihat dampak
                        </div>
                    </div>
                    
                    <!-- Back (After Click) -->
                    <div class="card-back absolute inset-0 backface-hidden rotate-y-180 bg-white rounded-3xl border-[6px] shadow-playful flex flex-col items-center justify-center p-6 text-center"
                         :class="obj.is_positive ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'">
                        <div class="mb-4 bg-white p-3 rounded-full shadow-sm">
                            <CheckCircle2 v-if="obj.is_positive" class="w-16 h-16 text-green-500" />
                            <XCircle v-else class="w-16 h-16 text-red-500" />
                        </div>
                        <h3 class="text-xl md:text-2xl font-heading font-bold mb-3" :class="obj.is_positive ? 'text-green-700' : 'text-red-700'">
                            {{ obj.name }}
                        </h3>
                        <p class="text-gray-700 font-bold text-sm md:text-base leading-relaxed bg-white/70 p-4 rounded-xl shadow-inner flex-1 overflow-y-auto">
                            {{ obj.impact_text }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-1000 { perspective: 1000px; }
.preserve-3d { transform-style: preserve-3d; }
.backface-hidden { backface-visibility: hidden; }
.rotate-y-180 { transform: rotateY(180deg); }

.shadow-playful {
    box-shadow: 0 10px 0 rgba(0,0,0,0.08);
}
.clickable-card-wrapper {
    transition: all 0.2s ease;
}
.clickable-card-wrapper:hover .card-front {
    transform: translateY(-8px);
    box-shadow: 0 16px 0 rgba(0,0,0,0.12);
}
.clickable-card-wrapper:active .card-front {
    transform: translateY(2px);
    box-shadow: 0 6px 0 rgba(0,0,0,0.1);
}

.font-heading {
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
}
</style>
