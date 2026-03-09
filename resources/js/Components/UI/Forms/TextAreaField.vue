<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        default: "",
    },
    modelValue: {
        type: [String, Number],
        default: "",
    },
    placeholder: {
        type: String,
        default: "",
    },
    rows: {
        type: Number,
        default: 4,
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: "",
    },
    maxlength: {
        type: Number,
        default: null,
    },
    showCount: {
        type: Boolean,
        default: false,
    },
    borderColor: {
        type: String,
        default: "blue",
        validator: (value) =>
            [
                "blue",
                "green",
                "yellow",
                "red",
                "purple",
                "gray",
                "orange",
            ].includes(value),
    },
});

const emit = defineEmits(["update:modelValue"]);

const textareaRef = ref(null);
const characterCount = computed(() => props.modelValue?.length || 0);

const updateValue = (event) => {
    emit("update:modelValue", event.target.value);
};

const getBorderColorClass = computed(() => {
    const colors = {
        blue: "border-blue-200 focus:border-blue-400",
        green: "border-blue-100 focus:border-blue-300",
        yellow: "border-amber-200 focus:border-amber-400",
        red: "border-red-200 focus:border-red-400",
        purple: "border-indigo-200 focus:border-indigo-400",
        gray: "border-gray-200 focus:border-gray-400",
        orange: "border-amber-300 focus:border-amber-500",
    };
    return colors[props.borderColor];
});
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-bold text-gray-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Textarea -->
        <textarea
            ref="textareaRef"
            :value="modelValue"
            :placeholder="placeholder"
            :rows="rows"
            :disabled="disabled"
            :maxlength="maxlength"
            @input="updateValue"
            :class="[
                'w-full px-4 py-3 rounded-2xl border-4 outline-none font-medium transition-colors resize-none',
                getBorderColorClass,
                disabled ? 'opacity-50 cursor-not-allowed bg-gray-100' : '',
                error ? 'border-red-300 focus:border-red-400' : '',
            ]"
        ></textarea>

        <!-- Character Count -->
        <div
            v-if="showCount || maxlength"
            class="mt-1.5 flex justify-between items-center text-xs"
        >
            <p
                v-if="error"
                class="text-red-500 font-medium flex items-center gap-1"
            >
                <span class="inline-block w-3.5 h-3.5">⚠</span>
                {{ error }}
            </p>
            <span v-else></span>

            <span
                v-if="showCount || maxlength"
                :class="[
                    'font-medium',
                    maxlength && characterCount >= maxlength * 0.9
                        ? 'text-red-500'
                        : 'text-gray-400',
                ]"
            >
                {{ characterCount }}{{ maxlength ? `/${maxlength}` : "" }}
            </span>
        </div>

        <!-- Error Message (tanpa count) -->
        <p
            v-else-if="error"
            class="mt-1.5 text-xs text-red-500 font-medium flex items-center gap-1"
        >
            <span class="inline-block w-3.5 h-3.5">⚠</span>
            {{ error }}
        </p>
    </div>
</template>
