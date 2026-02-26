<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: "",
    },
    description: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    color: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple"].includes(value),
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const updateValue = (event) => {
    const checked = event.target.checked;
    emit("update:modelValue", checked);
    emit("change", checked);
};

const getColorClass = computed(() => {
    const colors = {
        blue: "accent-blue-500",
        green: "accent-green-500",
        yellow: "accent-yellow-500",
        red: "accent-red-500",
        purple: "accent-purple-500",
    };
    return colors[props.color];
});
</script>

<template>
    <label
        :class="[
            'flex items-start gap-3 cursor-pointer',
            disabled ? 'opacity-50 cursor-not-allowed' : '',
        ]"
    >
        <input
            type="checkbox"
            :checked="modelValue"
            :disabled="disabled"
            @change="updateValue"
            :class="[
                'w-5 h-5 mt-0.5 rounded border-2 cursor-pointer transition-all',
                getColorClass,
                disabled ? 'cursor-not-allowed' : '',
            ]"
        />
        <div class="flex-1">
            <span v-if="label" class="block font-medium text-gray-800">
                {{ label }}
            </span>
            <span v-if="description" class="block text-sm text-gray-500 mt-1">
                {{ description }}
            </span>
        </div>
    </label>
</template>
