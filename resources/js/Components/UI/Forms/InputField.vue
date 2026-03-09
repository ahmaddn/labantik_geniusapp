<script setup>
import { computed, ref } from "vue";
import { Eye, EyeOff } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
    type: {
        type: String,
        default: "text",
        validator: (value) =>
            [
                "text",
                "email",
                "password",
                "number",
                "tel",
                "url",
                "search",
            ].includes(value),
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
    icon: {
        type: [String, Object, Function],
        default: "",
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

// State untuk show/hide password
const showPassword = ref(false);

// Computed untuk tipe input (toggle antara password dan text)
const inputType = computed(() => {
    if (props.type === "password" && showPassword.value) {
        return "text";
    }
    return props.type;
});

const updateValue = (event) => {
    emit("update:modelValue", event.target.value);
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const getBorderColorClass = computed(() => {
    const colors = {
        blue: "border-blue-200 focus:border-blue-400",
        green: "border-green-100 focus:border-green-300",
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

        <!-- Input Container -->
        <div class="relative">
            <!-- Left Icon -->
            <component
                v-if="icon"
                :is="icon"
                :size="16"
                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
            />

            <!-- Input Field -->
            <input
                :type="inputType"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @input="updateValue"
                :class="[
                    'w-full px-4 py-3 rounded-2xl border-4 outline-none font-medium transition-colors',
                    icon ? 'pl-12' : '',
                    type === 'password' ? 'pr-12' : '',
                    getBorderColorClass,
                    disabled
                        ? 'bg-gray-100 cursor-not-allowed'
                        : 'bg-white hover:border-opacity-80',
                    error ? 'border-red-300 focus:border-red-400' : '',
                ]"
            />

            <!-- Password Toggle Button -->
            <button
                v-if="type === 'password'"
                type="button"
                @click="togglePasswordVisibility"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                :disabled="disabled"
            >
                <component :is="showPassword ? EyeOff : Eye" :size="18" />
            </button>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
