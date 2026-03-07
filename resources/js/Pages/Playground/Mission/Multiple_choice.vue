<script setup>
import { ref, computed, watch } from 'vue'
import { CheckSquare2, Square, CheckCircle2, Circle } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [String, Number, Array],
    default: null,
  },
})

const emit = defineEmits(['update-answer'])

const correctCount = computed(() => {
  if (!props.question?.options) return 1
  return props.question.options.filter(o => o.is_correct).length
})

const isMultiple = computed(() => correctCount.value > 1)

const selectedOptions = ref([])

watch(() => props.modelValue, (newVal) => {
  if (isMultiple.value) {
    selectedOptions.value = Array.isArray(newVal) ? newVal : (newVal ? [newVal] : [])
  } else {
    selectedOptions.value = newVal ? [newVal] : []
  }
}, { immediate: true })

const isSelected = (optionId) => selectedOptions.value.includes(optionId)

const handleSelect = (optionId) => {
  if (isMultiple.value) {
    const idx = selectedOptions.value.indexOf(optionId)
    if (idx === -1) {
      if (selectedOptions.value.length < correctCount.value) {
        selectedOptions.value = [...selectedOptions.value, optionId]
      } else {
        selectedOptions.value = [...selectedOptions.value.slice(1), optionId]
      }
    } else {
      selectedOptions.value = selectedOptions.value.filter(id => id !== optionId)
    }
    emit('update-answer', { questionId: props.question.id, value: [...selectedOptions.value] })
  } else {
    selectedOptions.value = [optionId]
    emit('update-answer', { questionId: props.question.id, value: optionId })
  }
}
</script>

<template>
  <div class="mc-container">
    <div v-if="isMultiple" class="mc-hint">
      <span class="mc-hint-badge">
        Pilih {{ correctCount }} jawaban yang benar
        <span class="mc-hint-count">({{ selectedOptions.length }}/{{ correctCount }})</span>
      </span>
    </div>

    <div class="mc-options">
      <button
        v-for="option in props.question?.options || []"
        :key="option.id"
        class="mc-option"
        :class="{ selected: isSelected(option.id), multiple: isMultiple }"
        @click="handleSelect(option.id)"
      >
        <div class="mc-radio">
          <template v-if="isMultiple">
            <CheckSquare2 v-if="isSelected(option.id)" :size="20" :stroke-width="1.8" />
            <Square v-else :size="20" :stroke-width="1.8" />
          </template>
          <template v-else>
            <CheckCircle2 v-if="isSelected(option.id)" :size="20" :stroke-width="1.8" />
            <Circle v-else :size="20" :stroke-width="1.8" />
          </template>
        </div>

        <div class="mc-content">
          <img
            v-if="option.option_image"
            :src="`/storage/${option.option_image}`"
            :alt="option.option_text || option.text"
            class="mc-img"
          />
          <span class="mc-label">{{ option.option_text || option.text }}</span>
        </div>
      </button>
    </div>
  </div>
</template>

<style scoped>
.mc-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
}

.mc-hint {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.mc-hint-badge {
  display: inline-flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  color: #7c3aed;
  background: rgba(124, 58, 237, 0.08);
  border: 1.5px solid rgba(124, 58, 237, 0.2);
  border-radius: 50px;
  padding: 4px 12px;
  word-break: break-word;
}

.mc-hint-count {
  font-weight: 900;
  color: #6d28d9;
  white-space: nowrap;
}

.mc-options {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  width: 100%;
  min-width: 0;
}

.mc-option {
  display: flex;
  align-items: flex-start; /* top-align agar icon tidak lari saat teks panjang wrap */
  gap: 0.75rem;
  padding: 0.9rem 1rem;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  font-family: inherit;
  text-align: left;
  width: 100%;
  box-sizing: border-box;
  min-width: 0;
  overflow: hidden; /* cegah konten option keluar */
}

.mc-option:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  transform: translateX(4px);
}

.mc-option.selected {
  background: #dbeafe;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.mc-option.multiple.selected {
  background: #ede9fe;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.mc-radio {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #64748b;
  margin-top: 1px; /* sejajar visual dengan baris pertama teks */
}

.mc-option.selected .mc-radio {
  color: #3b82f6;
}

.mc-option.multiple.selected .mc-radio {
  color: #8b5cf6;
}

.mc-content {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  flex: 1;
  min-width: 0; /* ← kunci utama agar flex child bisa shrink */
  overflow: hidden;
}

.mc-img {
  width: 48px;
  height: 48px;
  object-fit: contain;
  border-radius: 6px;
  flex-shrink: 0;
}

.mc-label {
  font-size: 0.93rem;
  font-weight: 600;
  color: #334155;
  line-height: 1.5;
  /* ↓ teks panjang turun ke baris baru, tidak terpotong */
  word-break: break-word;
  overflow-wrap: anywhere;
  white-space: normal;
  flex: 1;
  min-width: 0;
}

.mc-option.selected .mc-label {
  color: #1e293b;
  font-weight: 700;
}

/* ── Responsive ── */
@media (max-width: 480px) {
  .mc-option {
    padding: 0.75rem 0.85rem;
    gap: 0.6rem;
  }
  .mc-label {
    font-size: 0.875rem;
  }
  .mc-hint-badge {
    font-size: 0.72rem;
    padding: 4px 10px;
  }
}
</style>    
