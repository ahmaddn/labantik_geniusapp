<script setup>
import { ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { CircleCheck, Info, LoaderCircle, Mail, Send } from "lucide-vue-next";
defineProps({ status: String });

const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"), {
        onSuccess: () => {
            toastMessage.value = "Reset link sent to your email!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = "Failed to send reset link!";
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
                <Mail class="text-blue-500 w-8 h-8" />
                Forgot Password
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <Info class="text-gray-400 w-4 h-4" />
                We'll send reset instructions to your email
            </p>
        </div>

        <!-- Status -->
        <div
            v-if="status"
            class="mb-6 p-4 bg-green-100 border-4 border-green-300 rounded-2xl flex items-center gap-2"
        >
            <CircleCheck class="text-green-600 w-5 h-5" />
            <p class="text-green-700 font-bold text-sm">{{ status }}</p>
        </div>

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

            <Button
                type="submit"
                variant="primary"
                size="lg"
                full-width
                :loading="form.processing"
                :icon="form.processing ? LoaderCircle : Send"
            >
                {{ form.processing ? "Sending..." : "Send Reset Link" }}
            </Button>
            <div class="text-center">
                <Link
                    :href="route('login')"
                    class="text-sm font-bold text-blue-600 hover:text-blue-700"
                >
                    Back to Login
                </Link>
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
    </AuthLayout>
</template>
