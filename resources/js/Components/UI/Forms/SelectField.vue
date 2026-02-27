<script setup>
import { computed } from "vue";
import { ChevronDown } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: [String, Number],
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
    placeholder: {
        type: String,
        default: "Pilih opsi",
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
    icon: {
        type: [String, Object, Function],
        default: "",
    },
    borderColor: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple", "gray"].includes(
                value,
            ),
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

const updateValue = (event) => {
    const value = event.target.value;
    emit("update:modelValue", value);
    emit("change", value);
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

        <!-- Select Container -->
        <div class="relative">
            <!-- Icon -->
            <component
                v-if="icon"
                :is="icon"
                :size="16"
                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
            />

            <!-- Select Field -->
            <select
                :value="modelValue"
                :disabled="disabled"
                :required="required"
                @change="updateValue"
                :class="[
                    'w-full px-4 py-3 rounded-2xl border-4 outline-none font-medium transition-colors appearance-none cursor-pointer',
                    icon ? 'pl-12' : '',
                    'pr-12',
                    getBorderColorClass,
                    disabled
                        ? 'bg-gray-100 cursor-not-allowed'
                        : 'bg-white hover:border-opacity-80',
                    error ? 'border-red-300 focus:border-red-400' : '',
                ]"
            >
                <option value="" disabled>{{ placeholder }}</option>
                <option
                    v-for="(option, index) in options"
                    :key="index"
                    :value="
                        typeof option === 'object'
                            ? option[optionValue]
                            : option
                    "
                >
                    {{
                        typeof option === "object"
                            ? option[optionLabel]
                            : option
                    }}
                </option>
            </select>

            <!-- Dropdown Arrow -->
            <ChevronDown
                :size="16"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
            />
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
