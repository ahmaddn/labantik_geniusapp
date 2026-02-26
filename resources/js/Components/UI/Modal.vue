<script setup>
import { watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "",
    },
    maxWidth: {
        type: String,
        default: "2xl", // default: max-w-2xl
        validator: (value) =>
            ["sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl"].includes(
                value,
            ),
    },
    borderColor: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple"].includes(value),
    },
});

const emit = defineEmits(["close"]);

const getMaxWidthClass = (size) => {
    const sizes = {
        sm: "max-w-sm",
        md: "max-w-md",
        lg: "max-w-lg",
        xl: "max-w-xl",
        "2xl": "max-w-2xl",
        "3xl": "max-w-3xl",
        "4xl": "max-w-4xl",
        "5xl": "max-w-5xl",
    };
    return sizes[size];
};

const getBorderColorClass = (color) => {
    const colors = {
        blue: "border-blue-200",
        green: "border-green-200",
        yellow: "border-yellow-200",
        red: "border-red-200",
        purple: "border-purple-200",
    };
    return colors[color];
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            @click="closeModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-40"
        ></div>
    </Transition>

    <!-- Modal Content -->
    <Transition name="modal">
        <div
            v-if="show"
            class="fixed inset-0 flex items-center justify-center z-50 p-4"
        >
            <div
                @click.stop
                :class="[
                    'bg-white rounded-3xl p-8 w-full border-4 shadow-playful-lg',
                    getMaxWidthClass(maxWidth),
                    getBorderColorClass(borderColor),
                ]"
            >
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ title }}
                    </h2>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="pi pi-times text-xl"></i>
                    </button>
                </div>

                <!-- Content Slot -->
                <slot></slot>

                <!-- Footer Slot -->
                <div v-if="$slots.footer" class="mt-6">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.shadow-playful-lg {
    box-shadow:
        0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 8px 10px -6px rgba(0, 0, 0, 0.1),
        0 0 0 4px rgba(59, 130, 246, 0.1);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-enter-active {
    transition: all 0.3s ease;
}

.modal-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
}

.modal-leave-active {
    transition: all 0.2s ease;
}

.modal-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
}
</style>
