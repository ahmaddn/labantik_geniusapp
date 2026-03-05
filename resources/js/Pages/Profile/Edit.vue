<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import Card from "@/Components/UI/Card.vue";
import { Head } from "@inertiajs/vue3";
import { User, Lock, AlertTriangle } from "lucide-vue-next";

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
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <User class="text-blue-600 w-6 h-6" />
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
                <Card
                    variant="playful"
                    title="Informasi Profil"
                    :icon="User"
                    icon-color="blue"
                    border-color="blue"
                    :hoverable="false"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </Card>

                <!-- Update Password Card -->
                <Card
                    variant="playful"
                    title="Perbarui Password"
                    :icon="Lock"
                    icon-color="green"
                    border-color="green"
                    :hoverable="false"
                >
                    <UpdatePasswordForm />
                </Card>

                <!-- Delete Account Card -->
                <Card
                    variant="playful"
                    title="Hapus Akun"
                    :icon="AlertTriangle"
                    icon-color="red"
                    border-color="red"
                    :hoverable="false"
                >
                    <DeleteUserForm />
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
