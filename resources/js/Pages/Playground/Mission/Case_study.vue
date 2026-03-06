<script setup>
import { ref, computed, watch } from 'vue'
import { CheckCircle2, Circle } from 'lucide-vue-next'

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
const expandText = ref(false)

watch(() => props.modelValue, (v) => { selectedOption.value = v })

const handleSelect = (optionId) => {
  selectedOption.value = optionId
  emit('update-answer', { questionId: props.question.id, value: optionId })
}

const caseText = computed(() => props.question?.case_study_text || '')
const isLong   = computed(() => caseText.value.length > 300)
const textShown = computed(() =>
  expandText.value || !isLong.value
    ? caseText.value
    : caseText.value.substring(0, 300) + '…'
)
</script>

<template>
  <div class="cs">
    <!-- Case study text box -->
    <div class="cs-box">
      <div class="cs-box-head">
        <span class="cs-badge">📖 Kasus</span>
      </div>
      <p class="cs-text">{{ textShown }}</p>
      <button v-if="isLong" class="cs-expand" @click="expandText = !expandText">
        {{ expandText ? 'Sembunyikan ▲' : 'Baca Selengkapnya ▼' }}
      </button>
    </div>

    <!-- Options -->
    <div class="cs-options">
      <button
        v-for="option in (question?.options || [])"
        :key="option.id"
        class="cs-opt"
        :class="{ 'cs-opt--on': selectedOption === option.id }"
        @click="handleSelect(option.id)"
      >
        <div class="cs-radio">
          <CheckCircle2 v-if="selectedOption === option.id" :size="18" :stroke-width="2" />
          <Circle       v-else                               :size="18" :stroke-width="1.8" />
        </div>
        <span class="cs-opt-label">{{ option.option_text || option.text }}</span>
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

/* ── Options ── */
.cs-options {
  display: flex; flex-direction: column; gap: 7px;
}
.cs-opt {
  display: flex; align-items: center; gap: 10px;
  padding: 11px 13px;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer; text-align: left;
  width: 100%;
  font-family: inherit;
  transition: all .2s cubic-bezier(.34,1.56,.64,1);
}
.cs-opt:hover { background: #f1f5f9; border-color: #c4b5fd; transform: translateX(3px); }
.cs-opt--on {
  background: #ede9fe; border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139,92,246,.12);
}
.cs-radio {
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #94a3b8;
}
.cs-opt--on .cs-radio { color: #8b5cf6; }
.cs-opt-label {
  font-size: 13px; font-weight: 600; color: #334155; line-height: 1.4;
  flex: 1;
}
.cs-opt--on .cs-opt-label { color: #1e293b; font-weight: 700; }

/* ── Responsive ── */
@media (max-width: 480px) {
  .cs-box { padding: 11px 12px; }
  .cs-text { font-size: 12.5px; }
  .cs-opt { padding: 10px 11px; gap: 8px; }
  .cs-opt-label { font-size: 12.5px; }
}
</style>
