<script setup>
import { ref } from 'vue';

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
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        </div>
                        <h3 class="co-name">{{ obj.name }}</h3>
                        <div class="co-tap-hint">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
                            </svg>
                            Ketuk untuk lihat dampak
                        </div>
                    </div>

                    <!-- BACK -->
                    <div class="co-back" :class="obj.is_positive ? 'co-back-green' : 'co-back-red'">
                        <div class="co-back-icon">
                            <svg v-if="obj.is_positive" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <svg v-else width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </div>
                        <h3 class="co-back-name" :class="obj.is_positive ? 'co-back-name-green' : 'co-back-name-red'">{{ obj.name }}</h3>
                        <p class="co-impact">{{ obj.impact_text }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.co-wrap {
    font-family: 'Nunito', sans-serif;
    width: 100%;
    padding: 4px 0;
}

/* Grid */
.co-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px;
}

/* 3D card flip */
.co-card-wrap {
    perspective: 900px;
    cursor: pointer;
    height: 260px;
}
.co-card {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 18px;
}
.co-flipped .co-card { transform: rotateY(180deg); }

/* Shared face styles */
.co-front, .co-back {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    padding: 14px 12px 12px;
    border: 3px solid transparent;
    border-bottom: 5px solid transparent;
    transition: box-shadow 0.2s;
}

/* Front */
.co-front-green {
    border-color: #58cc02;
    border-bottom-color: #46a302;
    background: #fff;
    box-shadow: 0 4px 0 #46a302;
}
.co-front-red {
    border-color: #ff4b4b;
    border-bottom-color: #ea2b2b;
    background: #fff;
    box-shadow: 0 4px 0 #ea2b2b;
}
.co-card-wrap:hover .co-front { filter: brightness(0.97); transform: translateY(-2px); }
.co-card-wrap:active .co-front { transform: translateY(2px); box-shadow: none !important; }

.co-img-wrap {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.co-img { max-width: 110px; max-height: 110px; object-fit: contain; }
.co-img-placeholder {
    width: 80px; height: 80px;
    background: #f7f7f7;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
}
.co-name {
    font-size: 15px;
    font-weight: 800;
    color: #3c3c3c;
    text-align: center;
    margin: 6px 0 4px;
}
.co-tap-hint {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    color: #1cb0f6;
    background: #f0f9ff;
    border: 1.5px solid #bae6fd;
    border-radius: 99px;
    padding: 4px 10px;
    animation: pulseBorder 1.8s ease-in-out infinite;
}
@keyframes pulseBorder { 0%,100%{border-color:#bae6fd} 50%{border-color:#1cb0f6} }

/* Back */
.co-back { transform: rotateY(180deg); }
.co-back-green {
    background: #f0fff0;
    border-color: #58cc02;
    border-bottom-color: #46a302;
    box-shadow: 0 4px 0 #46a302;
}
.co-back-red {
    background: #fff5f5;
    border-color: #ff4b4b;
    border-bottom-color: #ea2b2b;
    box-shadow: 0 4px 0 #ea2b2b;
}
.co-back-icon {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    flex-shrink: 0;
}
.co-back-icon svg { display: block; }
.co-back-icon .co-back-green .co-back-icon svg { stroke: #58cc02; }
.co-back-name { font-size: 14px; font-weight: 800; text-align: center; }
.co-back-name-green { color: #46a302; }
.co-back-name-red { color: #ea2b2b; }
.co-impact {
    font-size: 12px;
    font-weight: 700;
    color: #555;
    text-align: center;
    line-height: 1.5;
    background: rgba(255,255,255,0.7);
    border-radius: 10px;
    padding: 8px;
    overflow-y: auto;
    max-height: 90px;
    width: 100%;
}

/* Mobile: 2-col */
@media (max-width: 480px) {
    .co-grid { grid-template-columns: repeat(2, 1fr); }
    .co-card-wrap { height: 230px; }
}
</style>
