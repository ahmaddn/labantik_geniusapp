<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: "",
    },
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
    label: {
        type: String,
        default: "",
    },
    name: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    direction: {
        type: String,
        default: "vertical",
        validator: (value) => ["vertical", "horizontal"].includes(value),
    },
    color: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple"].includes(value),
    },
    optionValue: {
        type: String,
        default: "value",
    },
    optionLabel: {
        type: String,
        default: "label",
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const updateValue = (value) => {
    emit("update:modelValue", value);
    emit("change", value);
};

const getColorClass = computed(() => {
    const colors = {
        blue: "accent-blue-500",
        green: "accent-blue-400",
        yellow: "accent-amber-400",
        red: "accent-red-500",
        purple: "accent-indigo-500",
    };
    return colors[props.color];
});
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-bold text-gray-700 mb-3">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Radio Options -->
        <div
            :class="[
                direction === 'horizontal'
                    ? 'flex flex-wrap gap-4'
                    : 'space-y-3',
            ]"
        >
            <label
                v-for="(option, index) in options"
                :key="index"
                :class="[
                    'flex items-start gap-3 cursor-pointer',
                    disabled ? 'opacity-50 cursor-not-allowed' : '',
                ]"
            >
                <input
                    type="radio"
                    :name="name"
                    :value="
                        typeof option === 'object'
                            ? option[optionValue]
                            : option
                    "
                    :checked="
                        modelValue ===
                        (typeof option === 'object'
                            ? option[optionValue]
                            : option)
                    "
                    :disabled="disabled"
                    @change="
                        updateValue(
                            typeof option === 'object'
                                ? option[optionValue]
                                : option,
                        )
                    "
                    :class="[
                        'w-5 h-5 mt-0.5 cursor-pointer transition-all',
                        getColorClass,
                        disabled ? 'cursor-not-allowed' : '',
                    ]"
                />
                <div class="flex-1">
                    <span class="block font-medium text-gray-800">
                        {{
                            typeof option === "object"
                                ? option[optionLabel]
                                : option
                        }}
                    </span>
                    <span
                        v-if="typeof option === 'object' && option.description"
                        class="block text-sm text-gray-500 mt-1"
                    >
                        {{ option.description }}
                    </span>
                </div>
            </label>
        </div>
    </div>
</template>
