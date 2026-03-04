<script setup>
import { ref, computed, watch } from 'vue'
import { Target, CheckCircle2, XCircle, Layers } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
    // shape: {
    //   id,
    //   question_text,
    //   drag_drop_items: [{ id, item_text, item_image, correct_group_id }],
    //   drag_drop_groups: [{ id, group_name }]
    // }
  },
  modelValue: {
    type: Object,
    default: () => ({}),
    // shape: { [itemId]: groupId, ... }
  },
})

const emit = defineEmits(['update-answer'])

// Initialize items from question data
const items = ref([])
const zones = ref([])
const dragged = ref(null)
const answers = ref({ ...props.modelValue })

watch(
  () => props.question,
  () => {
    if (props.question?.drag_drop_items?.length) {
      items.value = props.question.drag_drop_items.map((it) => ({
        id: it.id,
        label: it.item_text,
        image: it.item_image,
        correctGroupId: it.correct_group_id,
        placedInGroupId: answers.value[it.id] || null,
      }))
    }

    if (props.question?.drag_drop_groups?.length) {
      zones.value = props.question.drag_drop_groups.map((g) => ({
        id: g.id,
        label: g.group_name,
        items: [],
      }))

      // Re-populate zones from answers
      items.value.forEach((item) => {
        if (item.placedInGroupId) {
          const zone = zones.value.find((z) => z.id === item.placedInGroupId)
          if (zone && !zone.items.find((i) => i.id === item.id)) {
            zone.items.push(item)
          }
        }
      })
    }
  },
  { immediate: true, deep: true }
)

const unplaced = computed(() =>
  items.value.filter((i) => !answers.value[i.id])
)

const placedCount = computed(() =>
  items.value.filter((i) => answers.value[i.id]).length
)

const totalCount = computed(() => items.value.length)

const progressPct = computed(() =>
  Math.round((placedCount.value / Math.max(totalCount.value, 1)) * 100)
)

const ZONE_COLORS = [
  { bg: '#f0fdf4', border: 'rgba(74,222,128,.45)', head: '#15803d' },
  { bg: '#f0f9ff', border: 'rgba(56,189,248,.45)', head: '#0369a1' },
  { bg: '#fdf4ff', border: 'rgba(192,132,252,.45)', head: '#7e22ce' },
  { bg: '#fff7ed', border: 'rgba(251,191,36,.45)', head: '#92400e' },
]

const zc = (i) => ZONE_COLORS[i % ZONE_COLORS.length]

const onDragStart = (item) => {
  dragged.value = item
}

const onDragOver = (e) => {
  e.preventDefault()
}

const onDrop = (zone) => {
  if (!dragged.value) return

  const item = dragged.value

  // Remove from old zone if placed
  if (answers.value[item.id]) {
    const oldZone = zones.value.find((z) => z.id === answers.value[item.id])
    if (oldZone) {
      oldZone.items = oldZone.items.filter((i) => i.id !== item.id)
    }
  }

  // Add to new zone
  item.placedInGroupId = zone.id
  answers.value[item.id] = zone.id
  zone.items.push(item)
  dragged.value = null

  // Emit update
  emit('update-answer', {
    questionId: props.question.id,
    value: { ...answers.value },
  })
}

const removeItem = (item, zone) => {
  zone.items = zone.items.filter((i) => i.id !== item.id)
  delete answers.value[item.id]
  item.placedInGroupId = null

  // Emit update
  emit('update-answer', {
    questionId: props.question.id,
    value: { ...answers.value },
  })
}

const reset = () => {
  answers.value = {}
  items.value.forEach((i) => (i.placedInGroupId = null))
  zones.value.forEach((z) => (z.items = []))

  emit('update-answer', {
    questionId: props.question.id,
    value: {},
  })
}
</script>

<template>
  <div class="dd-question-container">
    <!-- Question Text -->
    <div class="dd-q-header">
      <h4 class="dd-q-title">{{ props.question?.question_text || 'Seret gambar ke kelompok yang tepat' }}</h4>
      <div class="dd-q-progress">
        <small>{{ placedCount }}/{{ totalCount }}</small>
        <div class="dd-q-bar"
          ><div class="dd-q-fill" :style="{ width: progressPct + '%' }"></div></div>
      </div>
    </div>

    <!-- Bank -->
    <div class="dd-q-bank">
      <div class="dd-q-bank-label">Gambar tersedia</div>
      <div class="dd-q-bank-grid">
        <div
          v-for="item in unplaced"
          :key="item.id"
          class="dd-q-item"
          draggable="true"
          @dragstart="onDragStart(item)"
        >
          <div class="dd-q-item-visual">
            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.label" />
            <div v-else class="dd-q-item-placeholder">
              <Target :size="28" stroke-width="1.4" color="#0284c7" />
            </div>
          </div>
          <span class="dd-q-item-label">{{ item.label }}</span>
        </div>

        <div v-if="unplaced.length === 0" class="dd-q-bank-done">
          <CheckCircle2 :size="20" color="#4ade80" />
          <span>Semua ditempatkan!</span>
        </div>
      </div>
    </div>

    <!-- Zones -->
    <div class="dd-q-zones">
      <div
        v-for="(zone, zi) in zones"
        :key="zone.id"
        class="dd-q-zone"
        :style="`background:${zc(zi).bg}; border-color:${zc(zi).border}`"
        @dragover="onDragOver"
        @drop="onDrop(zone)"
      >
        <div class="dd-q-zone-header" :style="`color:${zc(zi).head}`">
          <div class="dd-q-zone-icon" :style="`background:${zc(zi).border}`">
            <Layers :size="16" stroke-width="2" />
          </div>
          <span class="dd-q-zone-name">{{ zone.label }}</span>
          <span class="dd-q-zone-count" :style="`background:${zc(zi).head}`">{{ zone.items.length }}</span>
        </div>

        <div class="dd-q-zone-body">
          <div
            v-for="item in zone.items"
            :key="item.id"
            class="dd-q-placed"
            :class="item.correctGroupId === zone.id ? 'dd-q-ok' : 'dd-q-err'"
          >
            <div class="dd-q-placed-visual">
              <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.label" />
              <div v-else class="dd-q-placed-ph">
                <Target :size="18" stroke-width="1.4" />
              </div>
            </div>
            <span class="dd-q-placed-label">{{ item.label }}</span>
            <button
              class="dd-q-placed-remove"
              @click="removeItem(item, zone)"
              title="Hapus"
            >
              <XCircle :size="14" />
            </button>
          </div>

          <div v-if="zone.items.length === 0" class="dd-q-zone-hint">
            <Layers :size="16" stroke-width="1.5" />
            <span>Letakkan di sini</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Reset Button -->
    <div class="dd-q-actions">
      <button class="dd-q-reset-btn" @click="reset">Ulangi Soal Ini</button>
    </div>
  </div>
</template>

<style scoped>
.dd-question-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.25rem;
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.dd-q-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.dd-q-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  flex: 1;
  min-width: 200px;
}

.dd-q-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  flex-shrink: 0;
}

.dd-q-bar {
  width: 120px;
  height: 6px;
  background: #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
}

.dd-q-fill {
  height: 100%;
  background: #3b82f6;
  transition: width 0.3s ease;
}

.dd-q-bank {
  padding: 0.9rem;
  background: #f8fafc;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.dd-q-bank-label {
  font-size: 0.75rem;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 0.6rem;
}

.dd-q-bank-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
  min-height: 70px;
  align-items: flex-start;
}

.dd-q-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  padding: 0.6rem;
  background: #fff;
  border-radius: 10px;
  border: 2px solid rgba(2, 132, 199, 0.1);
  cursor: grab;
  user-select: none;
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  min-width: 70px;
  max-width: 80px;
}

.dd-q-item:hover {
  transform: scale(1.08) translateY(-2px);
  border-color: var(--pg-blue-lt, #38bdf8);
  box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
}

.dd-q-item:active {
  cursor: grabbing;
  transform: scale(0.95);
}

.dd-q-item-visual {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dd-q-item-visual img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.dd-q-item-placeholder {
  width: 50px;
  height: 50px;
  background: rgba(2, 132, 199, 0.08);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dd-q-item-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #334155;
  text-align: center;
  line-height: 1.1;
}

.dd-q-bank-done {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #4ade80;
  font-weight: 700;
  font-size: 0.8rem;
  margin: auto;
}

.dd-q-zones {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.9rem;
}

.dd-q-zone {
  border-radius: 12px;
  border: 2.5px dashed;
  padding: 0.9rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  transition: transform 0.15s;
}

.dd-q-zone:hover {
  transform: scale(1.01);
}

.dd-q-zone-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 0.85rem;
}

.dd-q-zone-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dd-q-zone-name {
  flex: 1;
}

.dd-q-zone-count {
  min-width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 900;
  color: #fff;
  flex-shrink: 0;
}

.dd-q-zone-body {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  flex: 1;
  min-height: 70px;
  border-radius: 10px;
  padding: 0.4rem;
  border: 1.5px dashed rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.4);
  align-content: flex-start;
}

.dd-q-zone-hint {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  color: rgba(0, 0, 0, 0.22);
  font-size: 0.7rem;
  font-weight: 700;
  margin: auto;
  pointer-events: none;
}

.dd-q-placed {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem;
  border-radius: 10px;
  border: 2px solid transparent;
  min-width: 58px;
  font-size: 0.65rem;
  font-weight: 700;
  color: #334155;
  position: relative;
  animation: placeIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dd-q-ok {
  background: #dcfce7;
  border-color: #4ade80;
}

.dd-q-err {
  background: #fee2e2;
  border-color: #fca5a5;
}

.dd-q-placed-visual {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dd-q-placed-visual img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 7px;
}

.dd-q-placed-ph {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dd-q-placed-label {
  text-align: center;
  line-height: 1.1;
}

.dd-q-placed-remove {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 20px;
  height: 20px;
  padding: 0;
  background: #fff;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
  color: #dc2626;
  transition: all 0.2s;
}

.dd-q-ok .dd-q-placed-remove {
  color: #4ade80;
}

.dd-q-placed-remove:hover {
  transform: scale(1.1);
}

.dd-q-actions {
  display: flex;
  justify-content: flex-end;
}

.dd-q-reset-btn {
  padding: 0.6rem 1rem;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.dd-q-reset-btn:hover {
  background: #e2e8f0;
  border-color: #94a3b8;
}

@keyframes placeIn {
  from {
    transform: scale(0.4);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

@media (max-width: 640px) {
  .dd-q-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .dd-q-zones {
    grid-template-columns: 1fr;
  }
}
</style>
