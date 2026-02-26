<script setup>
import { computed } from "vue";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "secondary",
                "success",
                "warning",
                "danger",
                "info",
                "light",
                "dark",
            ].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },
    icon: {
        type: String,
        default: "",
    },
    iconPosition: {
        type: String,
        default: "left",
        validator: (value) => ["left", "right"].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    fullWidth: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: "button",
        validator: (value) => ["button", "submit", "reset"].includes(value),
    },
});

const emit = defineEmits(["click"]);

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit("click", event);
    }
};

const getVariantClass = computed(() => {
    const variants = {
        primary:
            "bg-blue-500 text-white border-blue-600 hover:bg-blue-600 active:bg-blue-700",
        secondary:
            "bg-gray-500 text-white border-gray-600 hover:bg-gray-600 active:bg-gray-700",
        success:
            "bg-green-500 text-white border-green-600 hover:bg-green-600 active:bg-green-700",
        warning:
            "bg-yellow-400 text-white border-yellow-500 hover:bg-yellow-500 active:bg-yellow-600",
        danger: "bg-red-400 text-white border-red-500 hover:bg-red-500 active:bg-red-600",
        info: "bg-cyan-500 text-white border-cyan-600 hover:bg-cyan-600 active:bg-cyan-700",
        light: "bg-white text-gray-800 border-gray-300 hover:bg-gray-50 active:bg-gray-100",
        dark: "bg-gray-800 text-white border-gray-900 hover:bg-gray-900 active:bg-black",
    };
    return variants[props.variant];
});

const getSizeClass = computed(() => {
    const sizes = {
        sm: "px-4 py-2 text-sm rounded-xl",
        md: "px-5 py-2 text-base rounded-xl",
        lg: "px-6 py-3 text-base rounded-2xl",
        xl: "px-8 py-4 text-lg rounded-2xl",
    };
    return sizes[props.size];
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        @click="handleClick"
        :class="[
            'font-bold border-4 transition-transform inline-flex items-center justify-center gap-2',
            getSizeClass,
            getVariantClass,
            fullWidth ? 'w-full' : '',
            disabled || loading
                ? 'opacity-50 cursor-not-allowed'
                : 'hover:scale-105 active:scale-95 cursor-pointer',
        ]"
    >
        <!-- Loading Spinner -->
        <i v-if="loading" class="pi pi-spinner pi-spin"></i>

        <!-- Left Icon -->
        <i
            v-else-if="icon && iconPosition === 'left'"
            :class="['pi', icon]"
        ></i>

        <!-- Slot Content -->
        <slot></slot>

        <!-- Right Icon -->
        <i v-if="icon && iconPosition === 'right'" :class="['pi', icon]"></i>
    </button>
</template>
