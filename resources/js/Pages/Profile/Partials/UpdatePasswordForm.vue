<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { Lock, Save } from "lucide-vue-next";

const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toastMessage.value = "Password berhasil diperbarui!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                toastMessage.value = "Password baru tidak valid!";
                toastType.value = "error";
                showToast.value = true;
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                toastMessage.value = "Password saat ini salah!";
                toastType.value = "error";
                showToast.value = true;
            }
        },
    });
};

const handleToastClose = () => {
    showToast.value = false;
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
            <InputField
                id="current_password"
                v-model="form.current_password"
                type="password"
                label="Password Saat Ini"
                :icon="Lock"
                autocomplete="current-password"
                :error="form.errors.current_password"
                border-color="blue"
            />

            <InputField
                id="password"
                v-model="form.password"
                type="password"
                label="Password Baru"
                :icon="Lock"
                autocomplete="new-password"
                :error="form.errors.password"
                border-color="blue"
            />

            <InputField
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Konfirmasi Password Baru"
                :icon="Lock"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
                border-color="blue"
            />

            <div class="flex items-center gap-4 pt-4">
                <Button
                    type="submit"
                    variant="primary"
                    size="lg"
                    :icon="Save"
                    :disabled="form.processing"
                >
                    Simpan Password
                </Button>
            </div>
        </form>

        <!-- Toast with Auto-Hide -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            :dismissable="true"
            :duration="3000"
            @close="handleToastClose"
        />
    </section>
</template>
