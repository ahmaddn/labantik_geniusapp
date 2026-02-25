<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

defineProps({ status: String });

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <AuthLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1
                class="text-4xl font-heading font-bold text-gray-800 mb-2 flex items-center gap-3"
            >
                <i class="pi pi-envelope text-blue-500 text-3xl"></i>
                Forgot Password
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <i class="pi pi-info-circle text-gray-400"></i>
                We’ll send reset instructions to your email
            </p>
        </div>

        <!-- Status -->
        <div
            v-if="status"
            class="mb-6 p-4 bg-green-100 border-4 border-green-300 rounded-2xl flex items-center gap-2"
        >
            <i class="pi pi-check-circle text-green-600"></i>
            <p class="text-green-700 font-bold text-sm">{{ status }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label
                    class="block text-sm font-bold text-gray-700 mb-2 font-heading"
                >
                    Email Address
                </label>

                <div class="relative">
                    <i
                        class="pi pi-envelope absolute left-5 top-1/2 -translate-y-1/2 text-blue-400"
                    ></i>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full pl-14 pr-5 py-4 bg-blue-50 border-4 border-blue-200 rounded-2xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        required
                    />
                </div>

                <p
                    v-if="form.errors.email"
                    class="mt-2 text-sm text-red-500 font-bold"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 rounded-2xl border-4 border-blue-600 transition-all hover:scale-105 flex items-center justify-center gap-2"
            >
                <i v-if="!form.processing" class="pi pi-send"></i>
                <i v-else class="pi pi-spin pi-spinner"></i>
                {{ form.processing ? "Sending..." : "Send Reset Link" }}
            </button>

            <div class="text-center">
                <Link
                    :href="route('login')"
                    class="text-sm font-bold text-blue-600"
                >
                    Back to Login
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
