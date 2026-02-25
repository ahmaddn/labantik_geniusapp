<script setup>
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <p class="text-gray-600 text-base mb-4">
                Setelah akun Anda dihapus, semua sumber daya dan data akan
                dihapus secara permanen. Sebelum menghapus akun, silakan unduh
                data atau informasi yang ingin Anda simpan.
            </p>
        </header>

        <button
            @click="confirmUserDeletion"
            class="bg-red-500 text-white px-6 py-3 rounded-xl font-bold border-4 border-red-600 hover:scale-105 transition-transform"
        >
            <i class="pi pi-trash mr-2"></i>
            Hapus Akun
        </button>

        <!-- Modal Backdrop -->
        <Transition name="fade">
            <div
                v-if="confirmingUserDeletion"
                class="fixed inset-0 bg-black/40 z-50"
                @click="closeModal"
            ></div>
        </Transition>

        <!-- Modal Content -->
        <Transition name="modal">
            <div
                v-if="confirmingUserDeletion"
                class="fixed inset-0 flex items-center justify-center z-50"
            >
                <div
                    @click.stop
                    class="bg-white rounded-3xl p-8 w-full max-w-md border-4 border-red-400 shadow-playful-lg"
                >
                    <!-- Header -->
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="bg-red-100 p-3 rounded-2xl border-2 border-red-300"
                        >
                            <i
                                class="pi pi-exclamation-triangle text-red-600 text-2xl"
                            ></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">
                            Konfirmasi Penghapusan
                        </h2>
                    </div>

                    <!-- Alert Box -->
                    <div
                        class="bg-red-50 border-4 border-red-200 rounded-2xl p-4 mb-6"
                    >
                        <p class="text-gray-700 font-medium text-sm">
                            Apakah Anda yakin ingin menghapus akun Anda? Setelah
                            akun dihapus, semua sumber daya dan data akan
                            dihapus secara permanen.
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-6">
                        <label
                            for="password"
                            class="block text-sm font-bold text-gray-700 mb-2"
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="w-full px-4 py-3 rounded-2xl border-4 border-red-200 focus:border-red-400 outline-none font-medium"
                            placeholder="Masukkan password Anda"
                            @keyup.enter="deleteUser"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-2 text-sm text-red-600 font-medium"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3">
                        <button
                            @click="closeModal"
                            class="px-5 py-2 rounded-xl font-bold border-4 border-gray-300 hover:scale-105 transition-transform"
                        >
                            Batal
                        </button>

                        <button
                            @click="deleteUser"
                            :class="{
                                'opacity-50 cursor-not-allowed':
                                    form.processing,
                            }"
                            :disabled="form.processing"
                            class="bg-red-500 text-white px-5 py-2 rounded-xl font-bold border-4 border-red-600 hover:scale-105 transition-transform disabled:hover:scale-100"
                        >
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </section>
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
