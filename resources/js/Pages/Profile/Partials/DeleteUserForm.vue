<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/Components/UI/Button.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";

const showConfirmDialog = ref(false);
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    showConfirmDialog.value = true;
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmDialog.value = false;
            toastMessage.value = "Akun berhasil dihapus!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = "Terjadi kesalahan saat menghapus akun!";
            toastType.value = "error";
            showToast.value = true;
        },
        onFinish: () => form.reset(),
    });
};

const cancelDeletion = () => {
    showConfirmDialog.value = false;
    form.clearErrors();
    form.reset();
};

const handleToastClose = () => {
    showToast.value = false;
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

        <Button
            variant="danger"
            size="lg"
            icon="pi-trash"
            @click="confirmUserDeletion"
        >
            Hapus Akun
        </Button>

        <!-- Confirm Dialog -->
        <ConfirmDialog
            :show="showConfirmDialog"
            title="Konfirmasi Penghapusan"
            message="Apakah Anda yakin ingin menghapus akun Anda? Setelah akun dihapus, semua sumber daya dan data akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan!"
            confirm-text="Ya, Hapus Akun"
            cancel-text="Batal"
            @confirm="deleteUser"
            @cancel="cancelDeletion"
        />

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
