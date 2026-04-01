<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { Shield, Lock, Check } from "lucide-vue-next";

const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({ password: "" });

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
        onSuccess: () => {
            toastMessage.value = "Password confirmed successfully!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = "Invalid password!";
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
        <div class="mb-8">
            <h1
                class="text-4xl font-heading font-bold text-gray-800 mb-2 flex items-center gap-3"
            >
                <Shield class="text-blue-500 w-8 h-8" />
                Confirm Password
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <Lock class="text-gray-400 w-4 h-4" />
                This is a secure area
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <InputField
                v-model="form.password"
                type="password"
                label="Password"
                :icon="Lock"
                required
                :error="form.errors.password"
                border-color="blue"
            />

            <Button
                type="submit"
                variant="primary"
                size="lg"
                full-width
                :loading="form.processing"
                :icon="form.processing ? null : Check"
            >
                {{ form.processing ? "Confirming..." : "Confirm" }}
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
