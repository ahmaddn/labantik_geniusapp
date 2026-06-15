<script setup>
import { ref } from 'vue';
import { CheckCircle2, XCircle, Hand, Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps({
    quiz: { type: Object, required: true },
});

const clickedObjects = ref(new Set());

const toggleObject = (id) => {
    const newSet = new Set(clickedObjects.value);
    newSet.add(id);
    clickedObjects.value = newSet;
};

const getImageUrl = (path) => {
    if (!path) return '';
    return (path.startsWith('http') || path.startsWith('/')) ? path : `/storage/${path}`;
};
</script>

<template>
    <div class="co-wrap">
        <div class="co-grid">
            <div
                v-for="(obj, index) in quiz.objects"
                :key="obj.id"
                class="co-card-wrap"
                :class="{ 'co-flipped': clickedObjects.has(obj.id) }"
                @click="toggleObject(obj.id)"
            >
                <div class="co-card">
                    <!-- FRONT -->
                    <div class="co-front" :class="obj.is_positive ? 'co-front-green' : 'co-front-red'">
                        <div class="co-img-wrap">
                            <img v-if="obj.image" :src="getImageUrl(obj.image)" class="co-img" :alt="obj.name" />
                            <div v-else class="co-img-placeholder">
                                <ImageIcon :size="32" color="#cbd5e1" :stroke-width="2" />
                            </div>
                        </div>
                        <h3 class="co-name">{{ obj.name }}</h3>
                        <div class="co-tap-hint">
                            <Hand :size="14" :stroke-width="2.5" />
                            Ketuk untuk lihat dampak
                        </div>
                    </div>

                    <!-- BACK -->
                    <div class="co-back" :class="obj.is_positive ? 'co-back-green' : 'co-back-red'">
                        <div class="co-back-icon">
                            <CheckCircle2 v-if="obj.is_positive" :size="32" :stroke-width="2.5" color="#58cc02" />
                            <XCircle v-else :size="32" :stroke-width="2.5" color="#ef4444" />
                        </div>
                        <h3 class="co-back-name" :class="obj.is_positive ? 'co-back-name-green' : 'co-back-name-red'">{{ obj.name }}</h3>
                        <div class="co-impact-wrap">
                            <p class="co-impact">{{ obj.impact_text }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.co-wrap {
    width: 100%;
    padding: 8px 0;
    display: flex;
    justify-content: center;
}

/* Grid */
.co-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    width: 100%;
}

/* 3D card flip */
.co-card-wrap {
    perspective: 1000px;
    cursor: pointer;
    width: 100%;
    max-width: 240px;
    height: 300px;
    flex-shrink: 0;
}
.co-card {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 20px;
}
.co-flipped .co-card { transform: rotateY(180deg); }

/* Shared face styles */
.co-front, .co-back {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: #ffffff;
    transition: box-shadow 0.2s, transform 0.2s;
}

/* Front */
.co-front {
    border: 2px solid #cbd5e1;
    box-shadow: 0 6px 0 0 #cbd5e1;
}
.co-front-green {
    border-color: #58cc02;
    box-shadow: 0 6px 0 0 #58cc02;
}
.co-front-red {
    border-color: #ef4444;
    box-shadow: 0 6px 0 0 #ef4444;
}

.co-card-wrap:hover .co-front { transform: translateY(-4px); box-shadow: 0 10px 0 0 var(--border-color); }
.co-card-wrap:active .co-front { transform: translateY(2px); box-shadow: 0 2px 0 0 var(--border-color); }

.co-front-green { --border-color: #58cc02; }
.co-front-red { --border-color: #ef4444; }

.co-img-wrap {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}
.co-img { max-width: 140px; max-height: 140px; object-fit: contain; }
.co-img-placeholder {
    width: 80px; height: 80px;
    background: #f1f5f9;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
}
.co-name {
    font-size: 18px;
    font-weight: 800;
    color: #334155;
    text-align: center;
    margin-bottom: 12px;
    line-height: 1.2;
}
.co-tap-hint {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 800;
    color: #0ea5e9;
    background: #e0f2fe;
    border: 2px solid #7dd3fc;
    border-radius: 12px;
    padding: 6px 12px;
    animation: pulseHint 2s infinite;
}
@keyframes pulseHint { 0%,100%{transform:scale(1)} 50%{transform:scale(1.05)} }

/* Back */
.co-back { 
    transform: rotateY(180deg);
}
.co-back-green {
    background: #f0fdf4;
    border: 2px solid #58cc02;
    box-shadow: 0 6px 0 0 #58cc02;
}
.co-back-red {
    background: #fef2f2;
    border: 2px solid #ef4444;
    box-shadow: 0 6px 0 0 #ef4444;
}
.co-back-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #ffffff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 12px;
    flex-shrink: 0;
}
.co-back-name { font-size: 18px; font-weight: 800; text-align: center; margin-bottom: 12px; line-height: 1.2; }
.co-back-name-green { color: #58cc02; }
.co-back-name-red { color: #ef4444; }
.co-impact-wrap {
    background: rgba(255,255,255,0.8);
    border-radius: 16px;
    padding: 12px;
    width: 100%;
    flex: 1;
    overflow-y: auto;
    border: 2px solid rgba(0,0,0,0.05);
}
.co-impact {
    font-size: 14px;
    font-weight: 700;
    color: #475569;
    text-align: center;
    line-height: 1.5;
}

/* Scrollbar hidden for impact */
.co-impact-wrap::-webkit-scrollbar { width: 4px; }
.co-impact-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* Mobile */
@media (max-width: 500px) {
    .co-card-wrap { max-width: 100%; height: 260px; }
    .co-img { max-width: 100px; max-height: 100px; }
    .co-name, .co-back-name { font-size: 16px; }
    .co-impact { font-size: 13px; }
    .co-grid { gap: 16px; }
}
</style>
