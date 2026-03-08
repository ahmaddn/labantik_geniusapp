<script setup>
import { defineProps, defineEmits, watch } from "vue";
import { CheckCircle, XCircle, AlertTriangle, Info, X } from "lucide-vue-next";

const props = defineProps({
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
    dismissable: {
        type: Boolean,
        default: true,
    },
    duration: {
        type: Number,
        default: 3000, // Auto-hide setelah 3 detik (ms)
    },
});

const emit = defineEmits(["close"]);

let autoHideTimeout = null;

// Watch show prop untuk trigger auto-hide
watch(
    () => props.show,
    (newValue) => {
        // Clear timeout sebelumnya
        if (autoHideTimeout) {
            clearTimeout(autoHideTimeout);
            autoHideTimeout = null;
        }

        // Set auto-hide jika show = true dan duration > 0
        if (newValue && props.duration > 0) {
            autoHideTimeout = setTimeout(() => {
                handleClose();
            }, props.duration);
        }
    },
);

const getTypeClasses = (type) => {
    const classes = {
        success: "bg-blue-500 border-blue-600",
        error: "bg-red-500 border-red-600",
        warning: "bg-amber-400 border-amber-500",
        info: "bg-indigo-500 border-indigo-600",
    };
    return classes[type] || classes.success;
};

const getIconComponent = (type) => {
    const icons = {
        success: CheckCircle,
        error: XCircle,
        warning: AlertTriangle,
        info: Info,
    };
    return icons[type] || icons.success;
};

const handleClose = () => {
    // Clear timeout saat manual close
    if (autoHideTimeout) {
        clearTimeout(autoHideTimeout);
        autoHideTimeout = null;
    }
    emit("close");
};
</script>

<template>
    <Transition name="toast">
        <div
            v-if="show"
            :class="[
                'fixed top-6 right-6 text-white px-6 py-3 rounded-2xl shadow-playful border-4 font-bold z-[999] flex items-center gap-3 min-w-[320px] max-w-md',
                getTypeClasses(type),
            ]"
        >
            <!-- Icon -->
            <component
                :is="getIconComponent(type)"
                :size="20"
                class="flex-shrink-0"
            />

            <!-- Message -->
            <span class="flex-1">{{ message }}</span>

            <!-- Close Button -->
            <button
                v-if="dismissable"
                @click="handleClose"
                class="ml-2 hover:bg-white/20 rounded-lg p-1.5 transition-colors focus:outline-none focus:ring-2 focus:ring-white/50 flex-shrink-0"
                aria-label="Close notification"
            >
                <X :size="16" />
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.shadow-playful {
    box-shadow:
        0 10px 25px -5px rgba(0, 0, 0, 0.2),
        0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

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
