<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <div class="p-5">
            <!-- Toolbar Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 mb-8"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                >
                    <!-- Kiri -->
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <i class="pi pi-user text-blue-600 text-2xl"></i>
                        </div>

                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Profil Pengguna
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola informasi akun dan keamanan Anda
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Profile Information Card -->
                <div
                    class="bg-white rounded-3xl p-6 md:p-8 shadow-playful border-4 border-blue-200 transition-all"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <i class="pi pi-id-card text-blue-600 text-xl"></i>
                        </div>
                        <h2
                            class="text-xl font-heading font-bold text-gray-800"
                        >
                            Informasi Profil
                        </h2>
                    </div>
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Update Password Card -->
                <div
                    class="bg-white rounded-3xl p-6 md:p-8 shadow-playful border-4 border-blue-200 transition-all"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="bg-green-100 p-3 rounded-2xl border-2 border-green-300"
                        >
                            <i class="pi pi-lock text-green-600 text-xl"></i>
                        </div>
                        <h2
                            class="text-xl font-heading font-bold text-gray-800"
                        >
                            Perbarui Password
                        </h2>
                    </div>
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account Card -->
                <div
                    class="bg-white rounded-3xl p-6 md:p-8 shadow-playful border-4 border-blue-200 transition-all"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="bg-red-100 p-3 rounded-2xl border-2 border-red-300"
                        >
                            <i
                                class="pi pi-exclamation-triangle text-red-600 text-xl"
                            ></i>
                        </div>
                        <h2
                            class="text-xl font-heading font-bold text-gray-800"
                        >
                            Hapus Akun
                        </h2>
                    </div>
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
