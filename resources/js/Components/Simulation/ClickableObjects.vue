<script setup>
import { ref } from 'vue';
import { CheckCircle2, XCircle, Hand, Image as ImageIcon, Sparkles, AlertTriangle, MessageSquare } from 'lucide-vue-next';
import { useSfx } from '@/Composable/useSfx';

const props = defineProps({
    quiz: { type: Object, required: true },
});

const { playPop } = useSfx();
const clickedObjects = ref([]);

const toggleObject = (id) => {
    playPop();
    if (clickedObjects.value.includes(id)) {
        clickedObjects.value = clickedObjects.value.filter(itemId => itemId !== id);
    } else {
        clickedObjects.value.push(id);
    }
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
                :key="'co-'+index"
                class="co-card-wrap"
                :class="[
                    { 'co-flipped': clickedObjects.includes(index) },
                    obj.is_positive ? 'theme-pos' : 'theme-neg'
                ]"
                @click="toggleObject(index)"
            >
                <div class="co-card">

                    <!-- ======== FRONT ======== -->
                    <div class="co-front">
                        <!-- top color bar -->
                        <div class="co-topbar">
                            <span class="co-type-label">
                                <Sparkles v-if="obj.is_positive" :size="12" :stroke-width="2.5" class="inline-block mr-1 align-middle" />
                                <AlertTriangle v-else :size="12" :stroke-width="2.5" class="inline-block mr-1 align-middle" />
                                <span class="align-middle">{{ obj.is_positive ? 'Positif' : 'Negatif' }}</span>
                            </span>
                            <span class="co-card-no">{{ String(index + 1).padStart(2,'0') }}</span>
                        </div>

                        <!-- name -->
                        <div class="co-namebox">
                            <h3 class="co-name">{{ obj.name }}</h3>
                        </div>

                        <!-- image -->
                        <div class="co-imgbox">
                            <img
                                v-if="obj.image"
                                :src="getImageUrl(obj.image)"
                                class="co-img"
                                :alt="obj.name"
                            />
                            <div v-else class="co-img-placeholder">
                                <ImageIcon :size="40" :stroke-width="1.5" />
                            </div>
                        </div>

                        <!-- tap hint -->
                        <div class="co-tap-hint">
                            <Hand :size="13" :stroke-width="2.5" class="inline-block align-middle mr-1" />
                            <span class="align-middle">Ketuk untuk lihat dampak!</span>
                        </div>

                        <!-- decorative dots -->
                        <div class="co-dots">
                            <span></span><span></span><span></span>
                        </div>
                    </div>

                    <!-- ======== BACK ======== -->
                    <div class="co-back">
                        <!-- top bar -->
                        <div class="co-topbar">
                            <span class="co-type-label">
                                <Sparkles v-if="obj.is_positive" :size="12" :stroke-width="2.5" class="inline-block mr-1 align-middle" />
                                <AlertTriangle v-else :size="12" :stroke-width="2.5" class="inline-block mr-1 align-middle" />
                                <span class="align-middle">{{ obj.is_positive ? 'Dampak Positif' : 'Dampak Negatif' }}</span>
                            </span>
                        </div>

                        <!-- name -->
                        <div class="co-namebox">
                            <h3 class="co-name">{{ obj.name }}</h3>
                        </div>

                        <!-- icon result -->
                        <div class="co-result-icon">
                            <CheckCircle2 v-if="obj.is_positive" :size="36" :stroke-width="2.5" />
                            <XCircle v-else :size="36" :stroke-width="2.5" />
                        </div>

                        <!-- impact text box -->
                        <div class="co-impact-box">
                            <p class="co-impact-label">
                                <MessageSquare :size="11" :stroke-width="2.5" class="inline-block mr-1 align-middle" />
                                <span class="align-middle">Dampaknya:</span>
                            </p>
                            <p class="co-impact-text">{{ obj.impact_text }}</p>
                        </div>

                        <!-- footer hint -->
                        <div class="co-back-hint">
                            <Hand :size="12" :stroke-width="2.5" class="inline-block align-middle mr-1" />
                            <span class="align-middle">Ketuk lagi untuk kembali</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

/* ─── Wrapper ─────────────────────────────── */
.co-wrap {
    width: 100%;
    padding: 12px 0;
    display: flex;
    justify-content: center;
    font-family: 'Nunito', sans-serif;
}

.co-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    width: 100%;
}

/* ─── 3D Flip Container ───────────────────── */
.co-card-wrap {
    perspective: 1000px;
    cursor: pointer;
    width: 240px;
    height: 360px;
    flex-shrink: 0;
    transition: transform 0.25s;
}
.co-card-wrap:hover { transform: translateY(-5px); }

.co-card {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
}
.co-flipped .co-card { transform: rotateY(180deg); }

/* ─── Shared face ─────────────────────────── */
.co-front, .co-back {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    padding: 14px;
    gap: 10px;
    overflow: hidden;
}

/* ─── Theme: Positive (soft emerald/green) ────── */
.theme-pos .co-front,
.theme-pos .co-back {
    background: #f0fdf4;
    border: 3px solid #10b981;
    box-shadow: 0 6px 0 0 #10b981, 0 12px 24px rgba(16,185,129,0.12);
}
.theme-pos .co-topbar   { background: #d1fae5; border: 1.5px solid #a7f3d0; color: #065f46; }
.theme-pos .co-imgbox   { border-color: #a7f3d0; background: #ffffff; }
.theme-pos .co-tap-hint { background: #d1fae5; border-color: #a7f3d0; color: #065f46; }
.theme-pos .co-dots span { background: #a7f3d0; }
.theme-pos .co-impact-box { background: #ffffff; border-color: #e2e8f0; }
.theme-pos .co-result-icon { color: #10b981; }
.theme-pos .co-back-hint  { color: #065f46; }
.theme-pos .co-name { color: #065f46; }
.theme-pos .co-type-label { color: #065f46; }
.theme-pos .co-card-no { color: #065f46; }

/* ─── Theme: Negative (soft rose/red) ─── */
.theme-neg .co-front,
.theme-neg .co-back {
    background: #fef2f2;
    border: 3px solid #f87171;
    box-shadow: 0 6px 0 0 #f87171, 0 12px 24px rgba(248,113,113,0.12);
}
.theme-neg .co-topbar   { background: #fee2e2; border: 1.5px solid #fca5a5; color: #7f1d1d; }
.theme-neg .co-imgbox   { border-color: #fca5a5; background: #ffffff; }
.theme-neg .co-tap-hint { background: #fee2e2; border-color: #fca5a5; color: #7f1d1d; }
.theme-neg .co-dots span { background: #fca5a5; }
.theme-neg .co-impact-box { background: #ffffff; border-color: #e2e8f0; }
.theme-neg .co-result-icon { color: #ef4444; }
.theme-neg .co-back-hint  { color: #7f1d1d; }
.theme-neg .co-name { color: #7f1d1d; }
.theme-neg .co-type-label { color: #7f1d1d; }
.theme-neg .co-card-no { color: #7f1d1d; }

/* ─── Top bar ─────────────────────────────── */
.co-topbar {
    border-radius: 12px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.co-type-label {
    font-size: 12.5px;
    font-weight: 900;
    letter-spacing: 0.3px;
}
.co-card-no {
    font-size: 12px;
    font-weight: 900;
    opacity: 0.7;
}

/* ─── Name box ────────────────────────────── */
.co-namebox {
    text-align: center;
    flex-shrink: 0;
}
.co-name {
    font-size: 17px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0;
    text-align: center;
}

/* ─── Image box ───────────────────────────── */
.co-imgbox {
    flex: 1;
    border-radius: 16px;
    border: 2.5px solid;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 0;
}
.co-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
}
.co-img-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    opacity: 0.35;
}

/* ─── Tap hint ────────────────────────────── */
.co-tap-hint {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 800;
    border-radius: 12px;
    border: 2px solid;
    padding: 6px 10px;
    flex-shrink: 0;
    animation: bounceHint 2s ease-in-out infinite;
}
@keyframes bounceHint {
    0%,100% { transform: scale(1); }
    50%      { transform: scale(1.04); }
}

/* ─── Decorative dots ─────────────────────── */
.co-dots {
    display: flex;
    justify-content: center;
    gap: 5px;
    flex-shrink: 0;
}
.co-dots span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

/* ─── Back face ───────────────────────────── */
.co-back { transform: rotateY(180deg); }

/* ─── Result icon ─────────────────────────── */
.co-result-icon {
    display: flex;
    justify-content: center;
    flex-shrink: 0;
}

/* ─── Impact box ──────────────────────────── */
.co-impact-box {
    flex: 1;
    border-radius: 16px;
    border: 2px solid;
    padding: 12px 14px;
    overflow-y: auto;
    min-height: 0;
}
.co-impact-box::-webkit-scrollbar { width: 4px; }
.co-impact-box::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 4px; }

.co-impact-label {
    font-size: 10px;
    font-weight: 900;
    color: #6b7280;
    margin: 0 0 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.co-impact-text {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    line-height: 1.55;
    margin: 0;
}

/* ─── Back hint ───────────────────────────── */
.co-back-hint {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 800;
    opacity: 0.75;
    flex-shrink: 0;
}

/* ─── Mobile ──────────────────────────────── */
@media (max-width: 500px) {
    .co-card-wrap { width: calc(50% - 10px); height: 290px; }
    .co-name { font-size: 14px; }
    .co-impact-text { font-size: 12px; }
    .co-grid { gap: 14px; }
    .co-front, .co-back { padding: 10px; gap: 8px; border-radius: 20px; }
    .co-tap-hint { font-size: 11px; padding: 4px 8px; border-radius: 10px; }
    .co-topbar { padding: 4px 8px; border-radius: 10px; }
    .co-type-label { font-size: 11px; }
    .co-card-no { font-size: 11px; }
}
</style>
