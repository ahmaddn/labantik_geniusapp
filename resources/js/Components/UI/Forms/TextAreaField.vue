<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "",
    },
    label: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    rows: {
        type: Number,
        default: 3,
    },
    borderColor: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple", "gray"].includes(
                value,
            ),
    },
});

const emit = defineEmits(["update:modelValue"]);

const updateValue = (event) => {
    emit("update:modelValue", event.target.value);
};

const getBorderColorClass = computed(() => {
    const colors = {
        blue: "border-blue-200 focus:border-blue-400",
        green: "border-green-200 focus:border-green-400",
        yellow: "border-yellow-200 focus:border-yellow-400",
        red: "border-red-200 focus:border-red-400",
        purple: "border-purple-200 focus:border-purple-400",
        gray: "border-gray-200 focus:border-gray-400",
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

        <!-- Textarea Field -->
        <textarea
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :rows="rows"
            @input="updateValue"
            :class="[
                'w-full px-4 py-3 rounded-2xl border-4 outline-none font-medium transition-colors resize-none',
                getBorderColorClass,
                disabled
                    ? 'bg-gray-100 cursor-not-allowed'
                    : 'bg-white hover:border-opacity-80',
                error ? 'border-red-300 focus:border-red-400' : '',
            ]"
        ></textarea>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
