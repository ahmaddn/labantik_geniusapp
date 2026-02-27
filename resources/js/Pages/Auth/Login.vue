<script setup>
import { ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { LogIn, Lock, Mail, CheckCircle, Loader2 } from "lucide-vue-next";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success");

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
        onSuccess: () => {
            toastMessage.value = "Successfully logged in!";
            toastType.value = "success";
            showToast.value = true;
        },
        onError: () => {
            toastMessage.value = "Invalid email or password!";
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
                <LogIn class="text-blue-500 w-8 h-8" />
                Welcome Back
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <Lock class="text-gray-400 w-4 h-4" />
                Please sign in to continue
            </p>
        </div>

        <!-- Status -->
        <div
            v-if="status"
            class="mb-6 p-4 bg-green-100 border-4 border-green-300 rounded-2xl flex items-center gap-2"
        >
            <CheckCircle class="text-green-600 w-5 h-5" />
            <p class="text-green-700 font-bold text-sm">{{ status }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <InputField
                v-model="form.email"
                type="email"
                label="Email Address"
                :icon="Mail"
                required
                :error="form.errors.email"
                border-color="blue"
            />

            <!-- Password -->
            <InputField
                v-model="form.password"
                type="password"
                label="Password"
                :icon="Lock"
                required
                :error="form.errors.password"
                border-color="blue"
            />

            <!-- Remember -->
            <div class="flex items-center justify-start">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-bold text-blue-600 hover:text-blue-700"
                >
                    Forgot password?
                </Link>
            </div>

            <!-- Button -->
            <Button
                type="submit"
                variant="primary"
                size="lg"
                full-width
                :loading="form.processing"
                :icon="form.processing ? Loader2 : null"
            >
                {{ form.processing ? "Signing in..." : "Sign In" }}
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
