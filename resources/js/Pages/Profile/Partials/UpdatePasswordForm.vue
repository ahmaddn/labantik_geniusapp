<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-6">
            <p class="text-gray-600 text-base">
                Pastikan akun Anda menggunakan password yang panjang dan acak
                agar tetap aman.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-5">
            <div>
                <label
                    for="current_password"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Password Saat Ini
                </label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                    autocomplete="current-password"
                />

                <p
                    v-if="form.errors.current_password"
                    class="mt-2 text-sm text-red-600 font-medium"
                >
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Password Baru
                </label>

                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                    autocomplete="new-password"
                />

                <p
                    v-if="form.errors.password"
                    class="mt-2 text-sm text-red-600 font-medium"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Konfirmasi Password Baru
                </label>

                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                    autocomplete="new-password"
                />

                <p
                    v-if="form.errors.password_confirmation"
                    class="mt-2 text-sm text-red-600 font-medium"
                >
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-blue-500 text-white px-6 py-3 rounded-xl font-bold border-4 border-blue-600 hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="pi pi-save mr-2"></i>
                    Simpan Password
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 transform scale-95"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 transform scale-95"
                >
                    <div
                        v-if="form.recentlySuccessful"
                        class="bg-green-500 text-white px-6 py-3 rounded-2xl shadow-playful border-4 border-green-600 font-bold"
                    >
                        <i class="pi pi-check-circle mr-2"></i>
                        Password Berhasil Diperbarui!
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>
