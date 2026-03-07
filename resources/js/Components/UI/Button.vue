<script setup>
import { computed } from "vue";
import { Loader2 } from "lucide-vue-next";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "secondary",
                "success",
                "blueLight",
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
        type: [Object, Function],
        default: null,
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
            "bg-blue-100 text-blue-700 border-blue-200 hover:bg-blue-200 active:bg-blue-300",
        success:
            "bg-green-400 text-white border-green-500 hover:bg-green-500 active:bg-green-600",
        blueLight:
            "bg-blue-400 text-white border-blue-500 hover:bg-blue-500 active:bg-blue-600",
        warning:
            "bg-amber-500 text-white border-amber-600 hover:bg-amber-600 active:bg-amber-600",
        danger: "bg-red-400 text-white border-red-500 hover:bg-red-500 active:bg-red-600",
        info: "bg-indigo-400 text-white border-indigo-500 hover:bg-indigo-500 active:bg-indigo-600",
        light: "bg-white text-blue-700 border-blue-200 hover:bg-blue-50 active:bg-blue-100",
        dark: "bg-blue-800 text-white border-blue-900 hover:bg-blue-900 active:bg-blue-950",
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

const iconSize = computed(() => {
    const sizes = { sm: 14, md: 16, lg: 18, xl: 20 };
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
        <!-- Left Icon -->
        <component
            v-if="icon && iconPosition === 'left'"
            :is="icon"
            :size="iconSize"
            :class="loading ? 'animate-spin' : ''"
        />

        <!-- Slot -->
        <slot />

        <!-- Right Icon -->
        <component
            v-if="icon && iconPosition === 'right'"
            :is="icon"
            :size="iconSize"
            :class="loading ? 'animate-spin' : ''"
        />
    </button>
</template>
