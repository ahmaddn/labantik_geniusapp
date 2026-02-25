<script setup>
import { defineProps } from "vue";

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: "",
    },
    type: {
        type: String,
        default: "success", // success, error, warning, info
        validator: (value) =>
            ["success", "error", "warning", "info"].includes(value),
    },
});

const getTypeClasses = (type) => {
    const classes = {
        success: "bg-green-500 border-green-600",
        error: "bg-red-500 border-red-600",
        warning: "bg-yellow-500 border-yellow-600",
        info: "bg-blue-500 border-blue-600",
    };
    return classes[type] || classes.success;
};
</script>

<template>
    <Transition name="toast">
        <div
            v-if="show"
            :class="[
                'fixed top-6 right-6 text-white px-6 py-3 rounded-2xl shadow-playful border-4 font-bold z-[999]',
                getTypeClasses(type),
            ]"
        >
            {{ message }}
        </div>
    </Transition>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}
</style>
