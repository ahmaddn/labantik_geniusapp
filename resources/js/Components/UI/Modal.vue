<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    maxWidth: {
        type: String,
        default: "md", // sm, md, lg, xl
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    borderColor: {
        type: String,
        default: "blue", // blue, red, green, yellow
    },
});

const emit = defineEmits(["close"]);

const close = () => {
    if (props.closeable) {
        emit("close");
    }
};

const getMaxWidthClass = (width) => {
    const classes = {
        sm: "max-w-sm",
        md: "max-w-md",
        lg: "max-w-lg",
        xl: "max-w-xl",
    };
    return classes[width] || classes.md;
};

const getBorderColorClass = (color) => {
    const classes = {
        blue: "border-blue-400",
        red: "border-red-400",
        green: "border-green-400",
        yellow: "border-yellow-400",
    };
    return classes[color] || classes.blue;
};
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/40 z-50"
            @click="close"
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
                <h2 class="text-2xl font-bold mb-6 text-gray-800">
                    {{ title }}
                </h2>

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
