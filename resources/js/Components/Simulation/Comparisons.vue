<script setup>
import { ref, computed, watch } from 'vue';
import { Check, Info } from 'lucide-vue-next';

const props = defineProps({
    quiz: { type: Object, required: true },
});

const activeGroupIndex = ref(0);
const activeGroup = computed(() => {
    if (!props.quiz?.items || props.quiz.items.length === 0) return null;
    return props.quiz.items[activeGroupIndex.value];
});

const activeItemIndex = ref(0);
const activeItem = computed(() => {
    if (!activeGroup.value?.items || activeGroup.value.items.length === 0) return null;
    return activeGroup.value.items[activeItemIndex.value] || activeGroup.value.items[0];
});

watch(activeGroupIndex, () => {
    activeItemIndex.value = 0;
});

const setActiveItem = (index) => {
    activeItemIndex.value = index;
};
</script>

<template>
    <div class="cmp-wrap" v-if="activeGroup">
        <!-- Header -->
        <div class="cmp-header">
            <h2 class="cmp-title">{{ quiz.title || 'Observasi Perbandingan' }}</h2>
            <p v-if="activeGroup.explanation" class="cmp-desc">{{ activeGroup.explanation }}</p>
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
                <div class="cmp-img-wrap">
                    <img :src="item.image ? `/storage/${item.image}` : '/images/placeholder.jpg'" :alt="item.label" class="cmp-img" />
                    <!-- Active Indicator Badge -->
                    <div v-if="activeItemIndex === idx" class="cmp-active-badge">
                        <Check :size="16" :stroke-width="3.5" color="#fff" />
                    </div>
                </div>
                <h3 class="cmp-card-label" :class="{ 'cmp-card-label-active': activeItemIndex === idx }">
                    {{ item.label || `Opsi ${idx + 1}` }}
                </h3>
            </div>
        </div>

        <!-- Narration & Toggles Area -->
        <div class="cmp-bottom-area">
            <!-- Narration Bubble -->
            <div class="cmp-narration">
                <div class="cmp-narration-icon">
                    <Info :size="20" color="#1cb0f6" :stroke-width="2.5" />
                </div>
                <p class="cmp-narration-text">
                    {{ activeItem?.narration || 'Pilih salah satu gambar untuk melihat penjelasan detailnya.' }}
                </p>
            </div>

            <!-- Toggles List -->
            <div class="cmp-toggles">
                <button 
                    v-for="(item, idx) in activeGroup.items" 
                    :key="'toggle-'+idx"
                    class="cmp-toggle-btn"
                    :class="{ 'cmp-toggle-btn-active': activeItemIndex === idx }"
                    @click="setActiveItem(idx)"
                >
                    <span class="cmp-toggle-text">{{ item.toggle_name || `Tampilan ${idx + 1}` }}</span>
                    <div class="cmp-toggle-switch" :class="{ 'cmp-toggle-switch-on': activeItemIndex === idx }">
                        <div class="cmp-toggle-thumb" :class="{ 'cmp-toggle-thumb-on': activeItemIndex === idx }"></div>
                    </div>
                </button>
            </div>
        </div>
    </div>
    
    <div v-else class="cmp-empty">
        <p>Data perbandingan belum tersedia.</p>
    </div>
</template>

<style scoped>
.cmp-wrap {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Header */
.cmp-header {
    text-align: center;
    margin-bottom: 8px;
}
.cmp-title {
    font-size: 24px;
    font-weight: 900;
    color: #58cc02;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cmp-desc {
    font-size: 15px;
    font-weight: 700;
    color: #64748b;
    margin-top: 4px;
}

/* Cards Grid */
.cmp-grid {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}
.cmp-card {
    flex: 1;
    min-width: 240px;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-bottom-width: 5px;
    border-radius: 20px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.cmp-card:hover {
    transform: translateY(-2px);
    border-color: #58cc02;
}
.cmp-card:active {
    transform: translateY(2px);
    border-bottom-width: 2px;
}
.cmp-card-active {
    border-color: #58cc02;
    background: #f0fdf4;
}

.cmp-img-wrap {
    width: 100%;
    aspect-ratio: 4/3;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    background: #f1f5f9;
}
.cmp-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cmp-active-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #58cc02;
    border: 2.5px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(88,204,2,0.4);
    animation: bounceIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes bounceIn {
    0% { transform: scale(0); }
    100% { transform: scale(1); }
}
.cmp-card-label {
    font-size: 16px;
    font-weight: 800;
    color: #475569;
    text-align: center;
}
.cmp-card-label-active {
    color: #58cc02;
}

/* Bottom Area */
.cmp-bottom-area {
    display: flex;
    gap: 20px;
    align-items: stretch;
    flex-wrap: wrap;
}

/* Narration */
.cmp-narration {
    flex: 1;
    min-width: 280px;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 20px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.cmp-narration-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 0 0 #bae6fd;
    flex-shrink: 0;
}
.cmp-narration-text {
    font-size: 15px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1.5;
    margin-top: 4px;
}

/* Toggles */
.cmp-toggles {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 240px;
    flex-shrink: 0;
}
.cmp-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-bottom-width: 4px;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.cmp-toggle-btn:hover {
    background: #f8fafc;
    border-color: #58cc02;
}
.cmp-toggle-btn:active {
    transform: translateY(2px);
    border-bottom-width: 2px;
}
.cmp-toggle-btn-active {
    border-color: #58cc02;
    background: #f0fdf4;
}
.cmp-toggle-text {
    font-size: 15px;
    font-weight: 800;
    color: #64748b;
}
.cmp-toggle-btn-active .cmp-toggle-text {
    color: #58cc02;
}
.cmp-toggle-switch {
    width: 44px;
    height: 24px;
    border-radius: 99px;
    background: #e2e8f0;
    padding: 3px;
    display: flex;
    align-items: center;
    transition: background 0.3s;
}
.cmp-toggle-switch-on {
    background: #58cc02;
}
.cmp-toggle-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.cmp-toggle-thumb-on {
    transform: translateX(20px);
}

.cmp-empty {
    text-align: center;
    color: #94a3b8;
    font-weight: 800;
    padding: 40px;
    font-size: 16px;
}

/* Mobile */
@media (max-width: 640px) {
    .cmp-card { min-width: 100%; }
    .cmp-bottom-area { flex-direction: column; }
    .cmp-toggles { min-width: 100%; }
}
</style>
