<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { KeyRound, Info, Mail, Lock } from "lucide-vue-next";

const props = defineProps({
    email: String,
    token: String,
});

const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onSuccess: () => {
            toastMessage.value = "Password reset successfully!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = "Failed to reset password!";
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
    <AuthLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1
                class="text-4xl font-heading font-bold text-gray-800 mb-2 flex items-center gap-3"
            >
                <KeyRound class="text-blue-500 w-8 h-8" />
                Reset Password
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <Info class="text-gray-400 w-4 h-4" />
                Enter your new password below
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">
            <InputField
                v-model="form.email"
                type="email"
                label="Email Address"
                :icon="Mail"
                required
                :error="form.errors.email"
                border-color="blue"
            />

            <InputField
                v-model="form.password"
                type="password"
                label="New Password"
                :icon="Lock"
                placeholder="New Password"
                required
                :error="form.errors.password"
                border-color="blue"
            />

            <InputField
                v-model="form.password_confirmation"
                type="password"
                label="Confirm Password"
                :icon="Lock"
                placeholder="Confirm Password"
                required
                :error="form.errors.password_confirmation"
                border-color="blue"
            />

            <Button
                type="submit"
                variant="primary"
                size="lg"
                full-width
                :loading="form.processing"
                :icon="form.processing ? null : KeyRound"
            >
                {{ form.processing ? "Processing..." : "Reset Password" }}
            </Button>
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
    </AuthLayout>
</template>
