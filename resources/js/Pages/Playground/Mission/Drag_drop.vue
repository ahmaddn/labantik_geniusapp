<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { Layers, XCircle, CheckCircle2, RotateCcw, GripHorizontal } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
    // {
    //   id, question_text,
    //   drag_drop_items:  [{ id, item_text, item_image, correct_group_id }],
    //   drag_drop_groups: [{ id, group_name }]
    // }
  },
  modelValue: {
    type: Object,
    default: () => ({}),
    // { [itemId]: groupId }
  },
})

const emit = defineEmits(['update-answer'])

// ── State ─────────────────────────────────────────────────────
const items   = ref([])
const zones   = ref([])
const placed  = ref({})   // { itemId: groupId } — source of truth for emit
const dragged = ref(null)
const dragOverZone = ref(null)
let tItem = null, tClone = null

// ── Init from question data + existing answers ─────────────────
const init = () => {
  const q = props.question

  items.value = (q.drag_drop_items || []).map(it => ({
    id:             String(it.id),
    label:          it.item_text,
    image:          it.item_image || null,       // path relative to /storage/
    correctGroupId: String(it.correct_group_id),
  }))

  zones.value = (q.drag_drop_groups || []).map(g => ({
    id:    String(g.id),
    label: g.group_name,
    items: [],
  }))

  // Restore from modelValue
  placed.value = {}
  const mv = props.modelValue || {}
  for (const [itemId, groupId] of Object.entries(mv)) {
    placed.value[String(itemId)] = String(groupId)
  }

  // Populate zone.items from placed
  zones.value.forEach(z => (z.items = []))
  for (const item of items.value) {
    const gId = placed.value[item.id]
    if (gId) {
      const zone = zones.value.find(z => z.id === gId)
      if (zone) zone.items.push(item)
    }
  }
}

watch(() => props.question, init, { immediate: true, deep: true })
watch(() => props.modelValue, () => init(), { deep: true })

// ── Derived ───────────────────────────────────────────────────
const unplaced    = computed(() => items.value.filter(i => !placed.value[i.id]))
const placedCount = computed(() => Object.keys(placed.value).length)
const totalCount  = computed(() => items.value.length)
const progressPct = computed(() => Math.round((placedCount.value / Math.max(totalCount.value, 1)) * 100))

const ZONE_COLORS = [
  { bg: '#f0fdf4', border: 'rgba(74,222,128,.5)',   head: '#15803d', badge: '#22c55e', light: '#dcfce7' },
  { bg: '#eff6ff', border: 'rgba(96,165,250,.5)',   head: '#1d4ed8', badge: '#3b82f6', light: '#dbeafe' },
  { bg: '#fdf4ff', border: 'rgba(192,132,252,.5)',  head: '#7e22ce', badge: '#a855f7', light: '#f3e8ff' },
  { bg: '#fff7ed', border: 'rgba(251,191,36,.5)',   head: '#92400e', badge: '#f59e0b', light: '#fef3c7' },
  { bg: '#fff1f2', border: 'rgba(251,113,133,.5)',  head: '#9f1239', badge: '#f43f5e', light: '#ffe4e6' },
  { bg: '#f0f9ff', border: 'rgba(56,189,248,.5)',   head: '#0369a1', badge: '#0ea5e9', light: '#e0f2fe' },
]
const zc = (i) => ZONE_COLORS[i % ZONE_COLORS.length]

// ── Emit ──────────────────────────────────────────────────────
const emitUpdate = () => {
  emit('update-answer', { questionId: props.question.id, value: { ...placed.value } })
}

// ── Place item in zone ────────────────────────────────────────
const placeItem = (item, zone) => {
  // Remove from old zone if already placed
  if (placed.value[item.id]) {
    const oldZone = zones.value.find(z => z.id === placed.value[item.id])
    if (oldZone) oldZone.items = oldZone.items.filter(i => i.id !== item.id)
  }
  placed.value[item.id] = zone.id
  if (!zone.items.find(i => i.id === item.id)) zone.items.push(item)
  emitUpdate()
}

// ── Remove item from zone (return to bank) ─────────────────────
const removeItem = (item, zone) => {
  zone.items = zone.items.filter(i => i.id !== item.id)
  delete placed.value[item.id]
  emitUpdate()
}

// ── Reset ─────────────────────────────────────────────────────
const reset = () => {
  placed.value = {}
  zones.value.forEach(z => (z.items = []))
  emitUpdate()
}

// ── Mouse drag ────────────────────────────────────────────────
const onDragStart = (item) => { dragged.value = item }
const onDragOver  = (e, zoneId) => { e.preventDefault(); dragOverZone.value = zoneId }
const onDragLeave = () => { dragOverZone.value = null }
const onDrop      = (zone) => {
  dragOverZone.value = null
  if (!dragged.value) return
  placeItem(dragged.value, zone)
  dragged.value = null
}

// ── Touch drag ────────────────────────────────────────────────
const onTouchStart = (item, e) => {
  tItem = item
  const r = e.currentTarget.getBoundingClientRect()
  tClone = e.currentTarget.cloneNode(true)
  Object.assign(tClone.style, {
    position: 'fixed', zIndex: '9999', opacity: '.88', pointerEvents: 'none',
    width: r.width + 'px', height: r.height + 'px',
    left: r.left + 'px', top: r.top + 'px',
    transform: 'scale(1.08) rotate(2deg)', borderRadius: '12px',
    boxShadow: '0 12px 32px rgba(29,78,216,.3)', transition: 'none',
  })
  document.body.appendChild(tClone)
}
const onTouchMove = (e) => {
  e.preventDefault()
  if (!tClone) return
  const t = e.touches[0]
  tClone.style.left = t.clientX - 40 + 'px'
  tClone.style.top  = t.clientY - 40 + 'px'
}
const onTouchEnd = (e) => {
  tClone?.remove(); tClone = null
  if (!tItem) return
  const t   = e.changedTouches[0]
  const el  = document.elementsFromPoint(t.clientX, t.clientY).find(el => el.dataset.zoneid)
  if (el) {
    const z = zones.value.find(z => z.id === el.dataset.zoneid)
    if (z) placeItem(tItem, z)
  }
  tItem = null
}

onUnmounted(() => { tClone?.remove() })
</script>

<template>
  <div
    class="dd"
    @touchmove.prevent="onTouchMove"
    @touchend="onTouchEnd"
  >
    <!-- ── Progress ── -->
    <div class="dd-progress">
      <div class="dd-prog-row">
        <span class="dd-prog-label">Sudah ditempatkan</span>
        <span class="dd-prog-count">{{ placedCount }} / {{ totalCount }}</span>
      </div>
      <div class="dd-prog-bar">
        <div class="dd-prog-fill" :style="{ width: progressPct + '%' }"></div>
      </div>
    </div>

    <!-- ── Item Bank ── -->
    <div class="dd-bank">
      <div class="dd-bank-head">
        <GripHorizontal :size="13" :stroke-width="2.3" />
        <span>Gambar Tersedia</span>
        <span class="dd-bank-cnt">{{ unplaced.length }}</span>
      </div>

      <div class="dd-bank-grid">
        <TransitionGroup name="dd-pop">
          <div
            v-for="item in unplaced"
            :key="item.id"
            class="dd-item"
            draggable="true"
            @dragstart="onDragStart(item)"
            @touchstart.prevent="onTouchStart(item, $event)"
          >
            <!-- Image from database (storage) -->
            <div class="dd-item-visual">
              <img
                v-if="item.image"
                :src="`/storage/${item.image}`"
                :alt="item.label"
                class="dd-item-img"
              />
              <!-- Placeholder only if no image at all -->
              <div v-else class="dd-item-placeholder">
                <span class="dd-item-initial">{{ item.label.charAt(0).toUpperCase() }}</span>
              </div>
            </div>
            <span class="dd-item-label">{{ item.label }}</span>
            <GripHorizontal :size="10" :stroke-width="2" color="#cbd5e1" class="dd-item-grip" />
          </div>
        </TransitionGroup>

        <div v-if="unplaced.length === 0" class="dd-bank-done">
          <CheckCircle2 :size="20" color="#22c55e" :stroke-width="2" />
          <span>Semua sudah ditempatkan!</span>
        </div>
      </div>
    </div>

    <!-- ── Drop Zones ── -->
    <div class="dd-zones">
      <div
        v-for="(zone, zi) in zones"
        :key="zone.id"
        class="dd-zone"
        :class="{ 'dd-zone--over': dragOverZone === zone.id }"
        :data-zoneid="zone.id"
        :style="{
          '--zbg':    zc(zi).bg,
          '--zbdr':   zc(zi).border,
          '--zhead':  zc(zi).head,
          '--zbadge': zc(zi).badge,
          '--zlight': zc(zi).light,
        }"
        @dragover="onDragOver($event, zone.id)"
        @dragleave="onDragLeave"
        @drop.prevent="onDrop(zone)"
      >
        <!-- Zone header -->
        <div class="dd-zone-header">
          <div class="dd-zone-icon">
            <Layers :size="14" :stroke-width="2.2" />
          </div>
          <span class="dd-zone-name">{{ zone.label }}</span>
          <span class="dd-zone-count">{{ zone.items.length }}</span>
        </div>

        <!-- Drop body -->
        <div class="dd-zone-body" :data-zoneid="zone.id">
          <TransitionGroup name="dd-place">
            <div
              v-for="item in zone.items"
              :key="item.id"
              class="dd-placed"
              :class="item.correctGroupId === zone.id "
            >
              <!-- Placed item image -->
              <div class="dd-placed-visual">
                <img
                  v-if="item.image"
                  :src="`/storage/${item.image}`"
                  :alt="item.label"
                  class="dd-placed-img"
                />
                <div v-else class="dd-placed-initial">
                  {{ item.label.charAt(0).toUpperCase() }}
                </div>
              </div>
              <span class="dd-placed-label">{{ item.label }}</span>

              <!-- Correct / wrong indicator -->


              <!-- Remove button -->
              <button class="dd-placed-remove" @click="removeItem(item, zone)" title="Kembalikan">
                <XCircle :size="13" />
              </button>
            </div>
          </TransitionGroup>

          <div v-if="zone.items.length === 0" class="dd-zone-hint" :data-zoneid="zone.id">
            <GripHorizontal :size="16" :stroke-width="1.5" />
            <span>Letakkan di sini</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Reset ── -->
    <div class="dd-actions">
      <button class="dd-reset-btn" @click="reset">
        <RotateCcw :size="12" :stroke-width="2.5" />
        Ulangi Soal Ini
      </button>
    </div>
  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.dd { display: flex; flex-direction: column; gap: 12px; }

/* ── Progress ── */
.dd-progress { display: flex; flex-direction: column; gap: 5px; }
.dd-prog-row {
  display: flex; align-items: center; justify-content: space-between;
  font-size: 11px; font-weight: 800; color: #64748b;
}
.dd-prog-count { color: #1d4ed8; }
.dd-prog-bar { height: 5px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
.dd-prog-fill {
  height: 100%; background: linear-gradient(90deg, #60a5fa, #1d4ed8);
  border-radius: 99px; transition: width .35s ease;
  box-shadow: 0 0 6px rgba(29,78,216,.35);
}

/* ── Bank ── */
.dd-bank {
  background: rgba(248,250,252,.9);
  border: 1.5px solid #e2e8f0;
  border-radius: 12px; padding: 10px 12px;
  backdrop-filter: blur(6px);
}
.dd-bank-head {
  display: flex; align-items: center; gap: 6px;
  font-size: 10.5px; font-weight: 900; color: #64748b;
  text-transform: uppercase; letter-spacing: .4px;
  margin-bottom: 10px;
}
.dd-bank-cnt {
  margin-left: auto;
  background: #1d4ed8; color: #fff;
  font-size: 10px; font-weight: 900;
  padding: 1px 8px; border-radius: 99px;
}
.dd-bank-grid {
  display: flex; flex-wrap: wrap; gap: 8px;
  min-height: 80px; align-content: flex-start;
}
.dd-bank-done {
  display: flex; align-items: center; gap: 7px;
  color: #16a34a; font-size: 12px; font-weight: 800; margin: auto;
}

/* ── Item card ── */
.dd-item {
  display: flex; flex-direction: column; align-items: center; gap: 5px;
  background: #fff;
  border: 2px solid rgba(29,78,216,0.1);
  border-radius: 12px; padding: 9px 10px 7px;
  min-width: 80px; max-width: 100px;
  cursor: grab; user-select: none;
  position: relative;
  transition: all .22s cubic-bezier(.34,1.56,.64,1);
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.dd-item:hover {
  transform: scale(1.1) translateY(-4px);
  border-color: #93c5fd;
  box-shadow: 0 10px 22px rgba(29,78,216,.18);
}
.dd-item:active { cursor: grabbing; transform: scale(.97); }

.dd-item-visual { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; }
.dd-item-img {
  width: 100%; height: 100%;
  object-fit: cover;
  border-radius: 8px;
  display: block;
}
.dd-item-placeholder {
  width: 60px; height: 60px;
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}
.dd-item-initial {
  font-size: 22px; font-weight: 900; color: #3b82f6;
  line-height: 1;
}
.dd-item-label {
  font-size: 11px; font-weight: 800; color: #1e293b;
  text-align: center; line-height: 1.3;
  max-width: 80px; word-break: break-word;
}
.dd-item-grip { position: absolute; bottom: 4px; right: 4px; opacity: 0; transition: opacity .15s; }
.dd-item:hover .dd-item-grip { opacity: 1; }

/* ── Zones ── */
.dd-zones {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}
.dd-zone {
  background: var(--zbg);
  border: 2.5px dashed var(--zbdr);
  border-radius: 14px; padding: 11px;
  display: flex; flex-direction: column; gap: 8px;
  transition: all .18s ease;
  backdrop-filter: blur(4px);
}
.dd-zone:hover { transform: scale(1.01); }
.dd-zone--over {
  border-style: solid;
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--zbadge) 20%, transparent), 0 6px 18px rgba(0,0,0,.08);
  transform: scale(1.015);
}

.dd-zone-header { display: flex; align-items: center; gap: 7px; color: var(--zhead); }
.dd-zone-icon {
  width: 30px; height: 30px; border-radius: 8px;
  background: var(--zlight); border: 1.5px solid var(--zbdr);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.dd-zone-name { font-size: 12.5px; font-weight: 900; flex: 1; }
.dd-zone-count {
  min-width: 22px; height: 22px; border-radius: 50%;
  background: var(--zbadge); color: #fff;
  font-size: 10.5px; font-weight: 900;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.dd-zone-body {
  display: flex; flex-wrap: wrap; gap: 6px; flex: 1;
  min-height: 110px; align-content: flex-start;
  background: rgba(255,255,255,.45);
  border: 1.5px dashed rgba(0,0,0,.07);
  border-radius: 10px; padding: 7px;
}
.dd-zone-hint {
  display: flex; flex-direction: column; align-items: center; gap: 4px;
  color: rgba(0,0,0,.2); font-size: 10.5px; font-weight: 800;
  margin: auto; pointer-events: none;
}

/* ── Placed item ── */
.dd-placed {
  display: flex; flex-direction: column; align-items: center; gap: 3px;
  padding: 6px 8px; border-radius: 10px; border: 2px solid;
  min-width: 64px; position: relative;
  animation: dd-popin .28s cubic-bezier(.34,1.56,.64,1);
  background: #f0fdf4; border-color: #4ade80;
}
.dd-placed-visual { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }
.dd-placed-img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; display: block; }
.dd-placed-initial {
  font-size: 16px; font-weight: 900; color: #64748b;
  width: 44px; height: 44px; border-radius: 8px;
  background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
}
.dd-placed-label { font-size: 10px; font-weight: 800; color: #1e293b; text-align: center; line-height: 1.2; }

.dd-placed--ok { background: #f0fdf4; border-color: #4ade80; }
.dd-placed--err { background: #fef2f2; border-color: #fca5a5; }

.dd-placed-status {
  position: absolute; top: -6px; left: -6px;
  width: 17px; height: 17px; border-radius: 50%;
  background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.12);
  display: flex; align-items: center; justify-content: center;
}
.dd-placed--ok  .dd-placed-status { color: #22c55e; }
.dd-placed--err .dd-placed-status { color: #ef4444; }

.dd-placed-remove {
  position: absolute; top: -7px; right: -7px;
  width: 19px; height: 19px; border-radius: 50%; padding: 0;
  background: #fff; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 5px rgba(0,0,0,.12); color: #dc2626;
  opacity: 0; transition: all .18s;
}
.dd-placed:hover .dd-placed-remove { opacity: 1; }
.dd-placed-remove:hover { transform: scale(1.15); }

/* ── Actions ── */
.dd-actions { display: flex; justify-content: flex-end; }
.dd-reset-btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 6px 13px;
  background: rgba(255,255,255,.7); border: 1.5px solid rgba(29,78,216,.2);
  border-radius: 8px;
  font-size: 11.5px; font-weight: 800; color: #1d4ed8;
  cursor: pointer; transition: all .18s;
  backdrop-filter: blur(4px);
}
.dd-reset-btn:hover { background: rgba(255,255,255,.9); border-color: rgba(29,78,216,.35); }

/* ── Transitions ── */
.dd-pop-enter-active { transition: all .25s cubic-bezier(.34,1.56,.64,1); }
.dd-pop-leave-active { transition: all .15s ease; position: absolute; }
.dd-pop-enter-from, .dd-pop-leave-to { opacity: 0; transform: scale(.3); }

.dd-place-enter-active { animation: dd-popin .28s cubic-bezier(.34,1.56,.64,1); }
.dd-place-leave-active { transition: all .15s ease; }
.dd-place-leave-to { opacity: 0; transform: scale(.4); }

@keyframes dd-popin { from { transform: scale(.3) rotate(-8deg); opacity: 0; } to { transform: scale(1) rotate(0); opacity: 1; } }

@media (max-width: 540px) {
  .dd-zones { grid-template-columns: repeat(2, 1fr); gap: 7px; }
  .dd-zone { padding: 9px; }
  .dd-zone-body { min-height: 100px; }
  .dd-item { min-width: 64px; max-width: 90px; }
  .dd-item-visual { width: 52px; height: 52px; }
  .dd-item-placeholder { width: 52px; height: 52px; }
  .dd-placed-visual { width: 38px; height: 38px; }
  .dd-placed-img { width: 38px; height: 38px; }
  .dd-placed-initial { width: 38px; height: 38px; font-size: 13px; }
}
</style>
