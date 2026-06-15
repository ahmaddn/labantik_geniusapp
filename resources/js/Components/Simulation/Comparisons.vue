<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    quiz: { type: Object, required: true },
});

const activeGroupIndex = ref(0);
const activeGroup = computed(() => props.quiz?.items?.[activeGroupIndex.value] || null);
const activeItemIndex = ref(0);
const activeItem = computed(() => activeGroup.value?.items?.[activeItemIndex.value] || activeGroup.value?.items?.[0] || null);

watch(activeGroupIndex, () => { activeItemIndex.value = 0; });

const setActiveItem = (idx) => { activeItemIndex.value = idx; };
</script>

<template>
    <div class="cmp-wrap" v-if="activeGroup">
        <!-- Title -->
        <div class="cmp-title-row">
            <h2 class="cmp-title">{{ quiz.title || 'Observasi Perbandingan' }}</h2>
            <p v-if="activeGroup.explanation" class="cmp-sub">{{ activeGroup.explanation }}</p>
        </div>

        <!-- Image Cards Grid -->
        <div class="cmp-grid">
            <div
                v-for="(item, idx) in activeGroup.items"
                :key="idx"
                class="cmp-card"
                :class="{ 'cmp-card-active': activeItemIndex === idx }"
                @click="setActiveItem(idx)"
            >
                <!-- Active check badge -->
                <div class="cmp-check" :class="{ 'cmp-check-on': activeItemIndex === idx }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <div class="cmp-img-wrap">
                    <img
                        :src="item.image ? `/storage/${item.image}` : '/images/placeholder.jpg'"
                        :alt="item.label"
                        class="cmp-img"
                    />
                </div>

                <div class="cmp-label">{{ item.label || `Opsi ${idx + 1}` }}</div>
            </div>
        </div>

        <!-- Narration / Toggle Section -->
        <div class="cmp-bottom">
            <!-- Narration bubble -->
            <div class="cmp-narration">
                <div class="cmp-narration-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#1cb0f6" stroke-width="2.5" stroke-linecap="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <p class="cmp-narration-text">
                    {{ activeItem?.narration || 'Pilih salah satu gambar untuk melihat penjelasannya.' }}
                </p>
            </div>

            <!-- Toggle buttons -->
            <div class="cmp-toggles">
                <button
                    v-for="(item, idx) in activeGroup.items"
                    :key="'toggle-'+idx"
                    class="cmp-toggle-btn"
                    :class="{ 'cmp-toggle-on': activeItemIndex === idx }"
                    @click="setActiveItem(idx)"
                >
                    <span>{{ item.toggle_name || `Tampilan ${idx + 1}` }}</span>
                    <div class="cmp-toggle-switch" :class="{ 'cmp-toggle-switch-on': activeItemIndex === idx }">
                        <div class="cmp-toggle-thumb" :class="{ 'cmp-toggle-thumb-on': activeItemIndex === idx }"></div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div v-else class="cmp-empty">Data perbandingan belum tersedia.</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.cmp-wrap {
    font-family: 'Nunito', sans-serif;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Title */
.cmp-title-row { text-align: center; }
.cmp-title {
    font-size: clamp(1rem, 3vw, 1.35rem);
    font-weight: 900;
    color: #58cc02;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.cmp-sub { font-size: 13px; font-weight: 700; color: #777; margin-top: 4px; }

/* Image Grid */
.cmp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}
.cmp-card {
    position: relative;
    border-radius: 16px;
    border: 3px solid #e5e5e5;
    border-bottom: 5px solid #e5e5e5;
    background: #fff;
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.34,1.56,0.64,1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 12px 12px 14px;
}
.cmp-card:hover { border-color: #58cc02; transform: translateY(-2px); }
.cmp-card:active { transform: translateY(2px); border-bottom-width: 3px; }
.cmp-card-active {
    border-color: #58cc02;
    border-bottom-color: #46a302;
    background: #f0fff0;
    transform: translateY(2px);
    border-bottom-width: 3px;
}

.cmp-check {
    position: absolute;
    top: 10px; right: 10px;
    width: 28px; height: 28px;
    border-radius: 50%;
    border: 2.5px solid #e5e5e5;
    background: #f7f7f7;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s ease;
}
.cmp-check svg { width: 14px; height: 14px; stroke: #aaa; }
.cmp-check-on {
    background: #58cc02;
    border-color: #46a302;
    box-shadow: 0 2px 8px rgba(88,204,2,0.35);
}
.cmp-check-on svg { stroke: #fff; }

.cmp-img-wrap { width: 100%; border-radius: 12px; overflow: hidden; background: #f7f7f7; aspect-ratio: 4/3; }
.cmp-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cmp-label { font-size: 14px; font-weight: 800; color: #3c3c3c; text-align: center; }

/* Bottom */
.cmp-bottom {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: stretch;
}
.cmp-narration {
    flex: 1;
    min-width: 200px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 14px;
    padding: 12px 14px;
}
.cmp-narration-icon { width: 22px; height: 22px; flex-shrink: 0; margin-top: 2px; }
.cmp-narration-icon svg { width: 100%; height: 100%; }
.cmp-narration-text { font-size: 13px; font-weight: 700; color: #0369a1; line-height: 1.6; }

.cmp-toggles {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 180px;
    flex: 0 0 auto;
}
.cmp-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 12px;
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #e5e5e5;
    background: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #777;
    cursor: pointer;
    transition: all 0.15s ease;
}
.cmp-toggle-btn:hover { border-color: #58cc02; color: #3c3c3c; }
.cmp-toggle-btn:active { transform: translateY(2px); border-bottom-width: 2px; }
.cmp-toggle-on { border-color: #58cc02; border-bottom-color: #46a302; color: #3c3c3c; }

.cmp-toggle-switch {
    width: 38px; height: 22px;
    border-radius: 99px;
    background: #e5e5e5;
    padding: 3px;
    display: flex; align-items: center;
    transition: background 0.2s;
    flex-shrink: 0;
}
.cmp-toggle-switch-on { background: #58cc02; }
.cmp-toggle-thumb {
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    transition: transform 0.2s;
}
.cmp-toggle-thumb-on { transform: translateX(16px); }

.cmp-empty { text-align: center; color: #aaa; font-weight: 700; padding: 40px; }

/* Mobile: stack cards 2-col */
@media (max-width: 480px) {
    .cmp-grid { grid-template-columns: repeat(2, 1fr); }
    .cmp-bottom { flex-direction: column; }
    .cmp-toggles { flex-direction: row; flex-wrap: wrap; }
    .cmp-toggle-btn { flex: 1; min-width: 120px; }
}
</style>
