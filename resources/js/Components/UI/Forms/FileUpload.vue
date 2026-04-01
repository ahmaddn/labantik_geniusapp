<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        default: "",
    },
    accept: {
        type: String,
        default: "*/*",
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    buttonText: {
        type: String,
        default: "Choose File",
    },
    buttonColor: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple"].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["change"]);

const fileInput = ref(null);

const handleChange = (event) => {
    emit("change", event);
};

const triggerFileInput = () => {
    if (!props.disabled && fileInput.value) {
        fileInput.value.click();
    }
};

const getButtonColorClass = computed(() => {
    const colors = {
        blue: "file:bg-blue-500 hover:file:bg-blue-600",
        green: "file:bg-green-500 hover:file:bg-green-600",
        yellow: "file:bg-yellow-500 hover:file:bg-yellow-600",
        red: "file:bg-red-500 hover:file:bg-red-600",
        purple: "file:bg-purple-500 hover:file:bg-purple-600",
    };
    return colors[props.buttonColor];
});

const getBorderColorClass = computed(() => {
    const colors = {
        blue: "border-blue-200 focus:border-blue-400",
        green: "border-green-200 focus:border-green-400",
        yellow: "border-yellow-200 focus:border-yellow-400",
        red: "border-red-200 focus:border-red-400",
        purple: "border-purple-200 focus:border-purple-400",
    };
    return colors[props.buttonColor];
});
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-bold text-gray-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- File Input -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            :multiple="multiple"
            :disabled="disabled"
            @change="handleChange"
            :class="[
                'w-full px-4 py-3 rounded-2xl border-4 outline-none font-medium transition-colors',
                'file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-white file:font-bold file:cursor-pointer',
                getBorderColorClass,
                getButtonColorClass,
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                error ? 'border-red-300 focus:border-red-400' : '',
            ]"
        />

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
