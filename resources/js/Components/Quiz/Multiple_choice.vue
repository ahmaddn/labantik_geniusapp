<script setup>
import { ref, watch } from 'vue'
import { CheckCircle2 } from 'lucide-vue-next'
import { useSfx } from '@/Composable/useSfx'

const props = defineProps({
  question:   { type: Object, required: true },
  modelValue: { type: [String, Number], default: null },
})

const emit = defineEmits(['update-answer'])

const { playPop } = useSfx()
const selected     = ref(props.modelValue)
const selectedAnim = ref(null)

watch(() => props.modelValue, (v) => { selected.value = v })

const isSelected = (optionId) => selected.value === optionId

const handleSelect = (optionId) => {
  playPop()
  selected.value     = optionId
  selectedAnim.value = optionId
  setTimeout(() => { selectedAnim.value = null }, 400)
  emit('update-answer', { questionId: props.question.id, value: optionId })
}

const OPT_LABELS   = ['A', 'B', 'C', 'D', 'E']
const OPT_VARIANTS = ['a', 'b', 'c', 'd', 'e']
</script>

<template>
  <div class="mc">
    <div class="mc-opts">
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
        <span class="opt-body">
          <img
            v-if="option.option_image"
            :src="`/storage/${option.option_image}`"
            :alt="option.option_text || option.text"
            class="opt-img"
          />
          <span class="opt-txt">{{ option.option_text || option.text }}</span>
        </span>
        <CheckCircle2 :size="14" :stroke-width="2.5" class="opt-chk" />
      </button>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.mc { 
  display: flex; 
  flex-direction: column; 
  gap: 12px; 
  width: 100%; 
  font-family: 'Nunito', sans-serif;
}

.mc-opts { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 16px; 
}

/* ── Base option ── */
.opt {
  display: flex; 
  align-items: stretch;
  border-radius: 18px; 
  overflow: hidden;
  border: 3px solid transparent; 
  background: #ffffff;
  cursor: pointer; 
  text-align: left; 
  width: 100%;
  font-family: inherit; 
  min-height: 60px; 
  min-width: 0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
  transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s, border-color .18s;
  position: relative;
  outline: none;
}
.opt:hover { 
  transform: translateY(-2px); 
  box-shadow: 0 6px 16px rgba(0,0,0,0.04);
}
.opt--pop  { 
  animation: oPop .3s cubic-bezier(.34,1.56,.64,1) forwards; 
}
@keyframes oPop {
  0%   { transform: scale(1); }
  45%  { transform: scale(1.03) translateY(-2px); }
  100% { transform: translateY(-2px); }
}

/* ── Variants A–E ── */
.opt--a { border-color: #cbd5e1; border-bottom-width: 5px; }
.opt--a .opt-key { background: #f1f5f9; color: #475569; border-right: 2px solid #cbd5e1; }
.opt--a.opt--sel { border-color: #3b82f6; border-bottom-width: 5px; background: #eff6ff; }
.opt--a.opt--sel .opt-key { background: #3b82f6; color: #ffffff; border-right-color: #3b82f6; }

.opt--b { border-color: #fde047; border-bottom-width: 5px; }
.opt--b .opt-key { background: #fef9c3; color: #a16207; border-right: 2px solid #fde047; }
.opt--b.opt--sel { border-color: #eab308; border-bottom-width: 5px; background: #fefce8; }
.opt--b.opt--sel .opt-key { background: #eab308; color: #ffffff; border-right-color: #eab308; }

.opt--c { border-color: #86efac; border-bottom-width: 5px; }
.opt--c .opt-key { background: #dcfce7; color: #15803d; border-right: 2px solid #86efac; }
.opt--c.opt--sel { border-color: #22c55e; border-bottom-width: 5px; background: #f0fdf4; }
.opt--c.opt--sel .opt-key { background: #22c55e; color: #ffffff; border-right-color: #22c55e; }

.opt--d { border-color: #fca5a5; border-bottom-width: 5px; }
.opt--d .opt-key { background: #fee2e2; color: #b91c1c; border-right: 2px solid #fca5a5; }
.opt--d.opt--sel { border-color: #ef4444; border-bottom-width: 5px; background: #fef2f2; }
.opt--d.opt--sel .opt-key { background: #ef4444; color: #ffffff; border-right-color: #ef4444; }

.opt--e { border-color: #c4b5fd; border-bottom-width: 5px; }
.opt--e .opt-key { background: #ede9fe; color: #6d28d9; border-right: 2px solid #c4b5fd; }
.opt--e.opt--sel { border-color: #8b5cf6; border-bottom-width: 5px; background: #f5f3ff; }
.opt--e.opt--sel .opt-key { background: #8b5cf6; color: #ffffff; border-right-color: #8b5cf6; }

/* ── Key label ── */
.opt-key {
  display: flex; 
  align-items: center; 
  justify-content: center;
  width: 48px; 
  min-width: 48px; 
  align-self: stretch;
  font-size: 16px;
  font-weight: 900;
  flex-shrink: 0;
  transition: background .18s, color .18s, border-color .18s;
}

/* ── Body ── */
.opt-body {
  flex: 1; 
  min-width: 0;
  display: flex; 
  align-items: center; 
  gap: 10px;
  padding: 12px 14px;
}
.opt-img {
  width: 46px; 
  height: 46px; 
  object-fit: contain;
  border-radius: 8px; 
  flex-shrink: 0;
}
.opt-txt {
  flex: 1; 
  min-width: 0;
  font-size: 14px; 
  font-weight: 800; 
  color: #334155;
  line-height: 1.4; 
  word-break: break-word; 
  white-space: normal;
}

.opt--sel .opt-txt {
  color: #1e293b;
}

/* ── Checkmark ── */
.opt-chk {
  color: #22c55e; 
  flex-shrink: 0; 
  margin-right: 12px; 
  align-self: center;
  opacity: 0; 
  transform: scale(0) rotate(-20deg);
  transition: all .22s cubic-bezier(.34,1.56,.64,1);
}
.opt--sel .opt-chk { 
  opacity: 1; 
  transform: scale(1) rotate(0); 
}

/* ── Mobile ── */
@media (max-width: 640px) {
  .mc-opts {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .opt {
    min-height: 52px;
  }
  .opt-key { 
    width: 42px; 
    min-width: 42px; 
    font-size: 14px; 
  }
  .opt-txt { 
    font-size: 13px; 
  }
  .opt-body { 
    padding: 10px 12px; 
  }
}
</style>
