<script setup>
import { ref, computed, watch } from 'vue'
import { CheckCircle2, Circle } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
    // shape: { id, question_text, case_study_text, options: [{ id, option_text }] }
  },
  modelValue: {
    type: [String, Number],
    default: null,
  },
})

const emit = defineEmits(['update-answer'])

const selectedOption = ref(props.modelValue)
const expandText = ref(false)

watch(() => props.modelValue, (newVal) => {
  selectedOption.value = newVal
})

const handleSelect = (optionId) => {
  selectedOption.value = optionId
  emit('update-answer', {
    questionId: props.question.id,
    value: optionId,
  })
}

const textPreview = computed(() => {
  const text = props.question?.case_study_text || ''
  if (expandText.value) return text
  return text.length > 300 ? text.substring(0, 300) + '...' : text
})
</script>

<template>
  <div class="cs-container">
    <!-- Case Study Text -->
    <div class="cs-text-box">
      <div class="cs-text-header">
        <span class="cs-badge">📖 Kasus</span>
      </div>
      <div class="cs-text-content">
        <p>{{ textPreview }}</p>
      </div>
      <button
        v-if="(props.question?.case_study_text || '').length > 300"
        class="cs-expand-btn"
        @click="expandText = !expandText"
      >
        {{ expandText ? 'Sembunyikan' : 'Baca Selengkapnya' }}
      </button>
    </div>

    <!-- Question -->
    <div class="cs-question">
      <p class="cs-question-text">{{ props.question?.question_text || 'Pertanyaan tidak ada' }}</p>
    </div>

    <!-- Options -->
    <div class="cs-options">
      <button
        v-for="option in props.question?.options || []"
        :key="option.id"
        class="cs-option"
        :class="{ selected: selectedOption === option.id }"
        @click="handleSelect(option.id)"
      >
        <div class="cs-radio">
          <Circle v-if="selectedOption !== option.id" :size="19" :stroke-width="1.8" />
          <CheckCircle2 v-else :size="19" :stroke-width="1.8" />
        </div>
        <span class="cs-label">{{ option.option_text || option.text }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.cs-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.5rem;
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.cs-text-box {
  flex: 1;
  padding: 1rem;
  background: linear-gradient(135deg, #f0fdf4, #f5f3ff);
  border-radius: 12px;
  border-left: 4px solid #8b5cf6;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.cs-text-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cs-badge {
  font-size: 0.8rem;
  font-weight: 800;
  color: #6d28d9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cs-text-content {
  flex: 1;
}

.cs-text-content p {
  font-size: 0.95rem;
  line-height: 1.7;
  color: #475569;
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.cs-expand-btn {
  align-self: flex-start;
  padding: 0.5rem 0.9rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #7c3aed;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: color 0.2s;
}

.cs-expand-btn:hover {
  color: #6d28d9;
  text-decoration: underline;
}

.cs-question {
  margin-top: 0.5rem;
}

.cs-question-text {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  line-height: 1.5;
}

.cs-options {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.cs-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.95rem;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  font-family: inherit;
  text-align: left;
}

.cs-option:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  transform: translateX(4px);
}

.cs-option.selected {
  background: #ede9fe;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.cs-radio {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #64748b;
}

.cs-option.selected .cs-radio {
  color: #8b5cf6;
}

.cs-label {
  font-size: 0.93rem;
  font-weight: 600;
  color: #334155;
}

.cs-option.selected .cs-label {
  color: #1e293b;
  font-weight: 700;
}
</style>
