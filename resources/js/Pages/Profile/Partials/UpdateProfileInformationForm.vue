<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { User, Mail, Info, CheckCircle, Save } from "lucide-vue-next";

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    form.patch(route("profile.update"), {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = "Profil berhasil diperbarui!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = "Terjadi kesalahan saat memperbarui profil!";
            toastType.value = "error";
            showToast.value = true;
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
                Perbarui informasi profil dan alamat email akun Anda.
            </p>
        </header>

        <form @submit.prevent="updateProfile" class="space-y-5">
            <InputField
                id="name"
                v-model="form.name"
                type="text"
                label="Nama"
                :icon="User"
                required
                autofocus
                autocomplete="name"
                :error="form.errors.name"
                border-color="blue"
            />

            <InputField
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                :icon="Mail"
                required
                autocomplete="username"
                :error="form.errors.email"
                border-color="blue"
            />

            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="bg-yellow-100 border-4 border-yellow-300 rounded-2xl p-4"
            >
                <p
                    class="text-sm text-gray-800 font-medium flex items-center gap-2"
                >
                    <Info class="text-yellow-600 w-4 h-4" />
                    Email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-blue-600 underline hover:text-blue-800 font-bold ml-1"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-3 text-sm font-bold text-green-700 bg-green-100 border-4 border-green-300 rounded-xl p-3 flex items-center gap-2"
                >
                    <CheckCircle class="w-4 h-4" />
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <Button
                    type="submit"
                    variant="primary"
                    size="lg"
                    :icon="Save"
                    :disabled="form.processing"
                >
                    Simpan
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
