<script setup>
import { X } from "lucide-vue-next";

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
        default: "2xl",
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

const closeModal = () => emit("close");
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            @click="closeModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-40"
        />
    </Transition>

    <!-- Modal Wrapper — center secara vertikal dan horizontal -->
    <Transition name="modal">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @click="closeModal"
        >
            <!--
                Card modal:
                - flex flex-col agar header, content, footer bisa dipisah
                - max-h-[90vh] agar tidak melebihi tinggi layar
                - overflow-hidden di card luar agar rounded tetap terjaga
            -->
            <div
                @click.stop
                :class="[
                    'bg-white rounded-3xl w-full border-4 shadow-playful-lg flex flex-col max-h-[90vh]',
                    getMaxWidthClass(maxWidth),
                    getBorderColorClass(borderColor),
                ]"
            >
                <!-- Header — tidak ikut scroll -->
                <div
                    class="flex items-center justify-between px-8 pt-8 pb-6 shrink-0"
                >
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ title }}
                    </h2>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                    >
                        <X :size="20" />
                    </button>
                </div>

                <!-- Content — bagian ini yang scroll kalau konten panjang -->
                <div class="px-8 overflow-y-auto flex-1">
                    <slot />
                </div>

                <!-- Footer — tidak ikut scroll -->
                <div v-if="$slots.footer" class="px-8 pt-4 pb-8 shrink-0">
                    <slot name="footer" />
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
    transform: scale(0.95) translateY(20px);
}
.modal-leave-active {
    transition: all 0.2s ease;
}
.modal-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
}
</style>
