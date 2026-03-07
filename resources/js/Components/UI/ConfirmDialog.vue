<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "Konfirmasi",
    },
    message: {
        type: String,
        required: true,
    },
    confirmText: {
        type: String,
        default: "Ya, Hapus",
    },
    cancelText: {
        type: String,
        default: "Batal",
    },
    confirmButtonClass: {
        type: String,
        default: "bg-red-500 border-red-600",
    },
});

const emit = defineEmits(["confirm", "cancel"]);

const confirm = () => {
    emit("confirm");
};

const cancel = () => {
    emit("cancel");
};
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/40 z-50"
            @click="cancel"
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
                class="bg-white rounded-3xl p-8 w-full max-w-sm border-4 border-blue-200 shadow-playful-lg text-center"
            >
                <h2 class="text-xl font-bold mb-4 text-gray-800">
                    {{ title }}
                </h2>

                <p class="text-gray-600 mb-6">
                    {{ message }}
                </p>

                <div class="flex justify-center gap-4">
                    <button
                        @click="cancel"
                        class="px-5 py-2 rounded-xl font-bold border-4 border-gray-300 hover:scale-105 transition-transform"
                    >
                        {{ cancelText }}
                    </button>

                    <button
                        @click="confirm"
                        :class="[
                            'text-white px-5 py-2 rounded-xl font-bold border-4 hover:scale-105 transition-transform',
                            confirmButtonClass,
                        ]"
                    >
                        {{ confirmText }}
                    </button>
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
