<script setup>
import { ref, computed, watch } from 'vue'
import { CheckCircle2 } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [String, Number],
    default: null,
  },
})

const emit = defineEmits(['update-answer'])

const selectedOption = ref(props.modelValue)
const expandText     = ref(false)
const selectedAnim   = ref(null)

watch(() => props.modelValue, (v) => { selectedOption.value = v })

const handleSelect = (optionId) => {
  selectedOption.value = optionId
  selectedAnim.value   = optionId
  setTimeout(() => { selectedAnim.value = null }, 400)
  emit('update-answer', { questionId: props.question.id, value: optionId })
}

const isSelected = (optionId) => selectedOption.value === optionId

// ── Case study text ───────────────────────────────────────────────
const caseText  = computed(() => props.question?.case_study_text || '')
const isLong    = computed(() => caseText.value.length > 300)
const textShown = computed(() =>
  expandText.value || !isLong.value
    ? caseText.value
    : caseText.value.substring(0, 300) + '…'
)

// ── Option label & color variant ─────────────────────────────────
const OPT_LABELS   = ['A', 'B', 'C', 'D', 'E']
const OPT_VARIANTS = ['a', 'b', 'c', 'd', 'e']
</script>

<template>
  <div class="cs">

    <!-- ── Case study text box ── -->
    <div class="cs-box">
      <div class="cs-box-head">
        <span class="cs-badge">📖 Kasus</span>
      </div>
      <p class="cs-text">{{ textShown }}</p>
      <button v-if="isLong" class="cs-expand" @click="expandText = !expandText">
        {{ expandText ? 'Sembunyikan ▲' : 'Baca Selengkapnya ▼' }}
      </button>
    </div>

    <!-- ── Options — gaya A/B/C/D seperti index.vue ── -->
    <div class="cs-opts">
      <button
        v-for="(option, i) in (question?.options || [])"
        :key="option.id"
        class="opt"
        :class="[
          `opt--${OPT_VARIANTS[i] ?? 'a'}`,
          { 'opt--sel': isSelected(option.id), 'opt--pop': selectedAnim === option.id }
        ]"
        @click="handleSelect(option.id)"
      >
        <span class="opt-key">{{ OPT_LABELS[i] ?? String(i + 1) }}</span>
        <span class="opt-txt">{{ option.option_text || option.text }}</span>
        <CheckCircle2 :size="14" :stroke-width="2.5" class="opt-chk" />
      </button>
    </div>

  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.cs {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
}

/* ── Case text box ── */
.cs-box {
  padding: 13px 14px;
  background: linear-gradient(135deg, #f5f3ff, #f0fdf4);
  border-radius: 12px;
  border-left: 4px solid #8b5cf6;
  display: flex; flex-direction: column; gap: 8px;
}
.cs-box-head { display: flex; align-items: center; }
.cs-badge {
  font-size: 10px; font-weight: 900; color: #6d28d9;
  text-transform: uppercase; letter-spacing: .5px;
}
.cs-text {
  font-size: 13px; line-height: 1.75; color: #475569;
  white-space: pre-wrap; word-break: break-word;
}
.cs-expand {
  align-self: flex-start;
  font-size: 11.5px; font-weight: 800; color: #7c3aed;
  background: none; border: none; cursor: pointer; padding: 0;
  transition: color .15s;
}
.cs-expand:hover { color: #5b21b6; text-decoration: underline; }

/* ── Options grid ── */
.cs-opts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

/* ── Base option button ── */
.opt {
  display: flex; align-items: stretch;
  border-radius: 13px; overflow: hidden;
  border: 2px solid transparent;
  background: #fff;
  cursor: pointer; text-align: left; width: 100%;
  font-family: inherit; font-size: 13px; font-weight: 800; color: #1e293b;
  min-height: 52px;
  box-shadow: 0 2px 0 rgba(0,0,0,.06), 0 3px 10px rgba(0,0,0,.05);
  transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s, border-color .18s;
}
.opt:hover { transform: translateY(-2px); box-shadow: 0 5px 0 rgba(0,0,0,.07), 0 8px 18px rgba(0,0,0,.08); }
.opt--pop  { animation: oPop .3s cubic-bezier(.34,1.56,.64,1) forwards; }
@keyframes oPop {
  0%   { transform: scale(1); }
  45%  { transform: scale(1.04) translateY(-2px); }
  100% { transform: translateY(-2px); }
}

/* ── Variant A — Biru ── */
.opt--a { border-color: #BFDBFE; }
.opt--a .opt-key { background: #DBEAFE; color: #1d4ed8; border-right-color: #BFDBFE; }
.opt--a.opt--sel { border-color: #2563EB; box-shadow: 0 5px 0 #1d4ed8, 0 8px 18px rgba(37,99,235,.2); transform: translateY(-2px); }
.opt--a.opt--sel .opt-key { background: #2563EB; color: #fff; border-right-color: #2563EB; }

/* ── Variant B — Kuning ── */
.opt--b { border-color: #FCD34D; }
.opt--b .opt-key { background: #FEF3C7; color: #B45309; border-right-color: #FCD34D; }
.opt--b.opt--sel { border-color: #D97706; box-shadow: 0 5px 0 #B45309, 0 8px 18px rgba(217,119,6,.2); transform: translateY(-2px); }
.opt--b.opt--sel .opt-key { background: #D97706; color: #fff; border-right-color: #D97706; }

/* ── Variant C — Hijau ── */
.opt--c { border-color: #6EE7B7; }
.opt--c .opt-key { background: #D1FAE5; color: #047857; border-right-color: #6EE7B7; }
.opt--c.opt--sel { border-color: #059669; box-shadow: 0 5px 0 #047857, 0 8px 18px rgba(5,150,105,.2); transform: translateY(-2px); }
.opt--c.opt--sel .opt-key { background: #059669; color: #fff; border-right-color: #059669; }

/* ── Variant D — Merah ── */
.opt--d { border-color: #FDA4AF; }
.opt--d .opt-key { background: #FFE4E6; color: #BE123C; border-right-color: #FDA4AF; }
.opt--d.opt--sel { border-color: #E11D48; box-shadow: 0 5px 0 #BE123C, 0 8px 18px rgba(225,29,72,.2); transform: translateY(-2px); }
.opt--d.opt--sel .opt-key { background: #E11D48; color: #fff; border-right-color: #E11D48; }

/* ── Variant E — Ungu ── */
.opt--e { border-color: #C4B5FD; }
.opt--e .opt-key { background: #EDE9FE; color: #6D28D9; border-right-color: #C4B5FD; }
.opt--e.opt--sel { border-color: #7C3AED; box-shadow: 0 5px 0 #6D28D9, 0 8px 18px rgba(124,58,237,.2); transform: translateY(-2px); }
.opt--e.opt--sel .opt-key { background: #7C3AED; color: #fff; border-right-color: #7C3AED; }

/* ── Key label (A/B/C/D) ── */
.opt-key {
  display: flex; align-items: center; justify-content: center;
  width: 46px; min-width: 46px; align-self: stretch;
  font-family: 'Righteous', cursive; font-size: 16px;
  border-right: 2px solid transparent; flex-shrink: 0;
  transition: background .18s, color .18s, border-color .18s;
}

/* ── Option text ── */
.opt-txt {
  flex: 1; padding: 12px 10px 12px 11px;
  line-height: 1.4; color: #1e293b; font-size: 13px;
  word-break: break-word;
}

/* ── Check icon — muncul saat terpilih ── */
.opt-chk {
  color: #34D399; flex-shrink: 0; margin-right: 10px; align-self: center;
  opacity: 0; transform: scale(0) rotate(-20deg);
  transition: all .22s cubic-bezier(.34,1.56,.64,1);
}
.opt--sel .opt-chk { opacity: 1; transform: scale(1) rotate(0); }

/* ── Mobile ── */
@media (max-width: 600px) {
  .cs-opts { grid-template-columns: 1fr; gap: 8px; }
  .cs-box  { padding: 11px 12px; }
  .cs-text { font-size: 12.5px; }
  .opt-txt { font-size: 12.5px; padding: 10px 9px 10px 10px; }
  .opt-key { width: 40px; min-width: 40px; font-size: 14px; }
}
</style>
