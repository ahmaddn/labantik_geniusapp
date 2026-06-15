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

</script>

<template>
  <div class="cs">

    <!-- ── Case study text box ── -->
    <div class="cs-box">
      <div class="cs-box-head">
        <span class="cs-badge">Kasus</span>
      </div>
      <p class="cs-text">{{ textShown }}</p>
      <button v-if="isLong" class="cs-expand" @click="expandText = !expandText">
        {{ expandText ? 'Sembunyikan' : 'Baca Selengkapnya' }}
      </button>
    </div>

    <!-- ── Options ── -->
    <div class="cs-opts">
      <button
        v-for="(option, i) in (question?.options || [])"
        :key="option.id"
        class="opt"
        :class="[
          { 'opt--sel': isSelected(option.id), 'opt--pop': selectedAnim === option.id }
        ]"
        @click="handleSelect(option.id)"
      >
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
  padding: 16px;
  background: #fff;
  border-radius: 16px;
  border: 2px solid #e5e5e5;
  display: flex; flex-direction: column; gap: 8px;
  box-shadow: 0 4px 0 #e5e5e5;
  margin-bottom: 8px;
}
.cs-box-head { display: flex; align-items: center; }
.cs-badge {
  font-size: 13px; font-weight: 800; color: #1cb0f6;
  text-transform: uppercase; letter-spacing: 1px;
}
.cs-text {
  font-size: 15px; line-height: 1.6; color: #4b4b4b; font-weight: 600;
  white-space: pre-wrap; word-break: break-word;
}
.cs-expand {
  align-self: flex-start;
  font-size: 13px; font-weight: 800; color: #1cb0f6;
  background: none; border: none; cursor: pointer; padding: 0;
  transition: color .15s;
}
.cs-expand:hover { color: #1899d6; text-decoration: underline; }

/* ── Options grid ── */
.cs-opts {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

/* ── Base option button ── */
.opt {
  display: flex; align-items: stretch;
  border-radius: 16px; overflow: hidden;
  border: 2px solid #e5e5e5;
  background: #fff;
  cursor: pointer; text-align: left; width: 100%;
  font-family: inherit; min-height: 52px;
  border-bottom-width: 4px;
  transition: all .15s cubic-bezier(.34,1.56,.64,1);
}
.opt:hover:not(.opt--sel) { background: #f7f7f7; }
.opt:active:not(:disabled) { transform: translateY(2px); border-bottom-width: 2px; }
.opt--pop  { animation: oPop .3s cubic-bezier(.34,1.56,.64,1) forwards; }
@keyframes oPop {
  0%   { transform: scale(1); }
  45%  { transform: scale(1.02) translateY(-2px); }
  100% { transform: translateY(-2px); border-bottom-width: 2px; }
}

/* ── Selected State ── */
.opt--sel {
  border-color: #84d8ff;
  border-bottom-color: #38bdf8;
  background: #ddf4ff;
}
.opt--sel .opt-txt {
  color: #1cb0f6;
}

/* ── Option text ── */
.opt-txt {
  flex: 1; padding: 14px 16px;
  line-height: 1.4; color: #4b4b4b; font-size: 15px; font-weight: 700;
  word-break: break-word;
}

/* ── Check icon ── */
.opt-chk {
  color: #1cb0f6; flex-shrink: 0; margin-right: 16px; align-self: center;
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
