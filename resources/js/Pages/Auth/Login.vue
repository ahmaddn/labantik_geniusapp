<script setup>
import { ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Checkbox from "primevue/checkbox";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <AuthLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1
                class="text-4xl font-heading font-bold text-gray-800 mb-2 flex items-center gap-3"
            >
                <i class="pi pi-sign-in text-blue-500 text-3xl"></i>
                Welcome Back
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <i class="pi pi-lock text-gray-400"></i>
                Please sign in to continue
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

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
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
            </div>

            <!-- Password -->
            <div>
                <label
                    class="block text-sm font-bold text-gray-700 mb-2 font-heading"
                >
                    Password
                </label>

                <div class="relative">
                    <i
                        class="pi pi-lock absolute left-5 top-1/2 -translate-y-1/2 text-blue-400"
                    ></i>
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full pl-14 pr-14 py-4 bg-blue-50 border-4 border-blue-200 rounded-2xl focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        required
                    />

                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-5 top-1/2 -translate-y-1/2"
                    >
                        <i
                            :class="[
                                'pi',
                                showPassword ? 'pi-eye-slash' : 'pi-eye',
                                'text-blue-500',
                            ]"
                        ></i>
                    </button>
                </div>
            </div>

            <!-- Remember -->
            <div class="flex items-center justify-start">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-bold text-blue-600"
                >
                    Forgot password?
                </Link>
            </div>

            <!-- Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 rounded-2xl border-4 border-blue-600 transition-all hover:scale-105 flex items-center justify-center gap-2"
            >
                <i v-if="!form.processing" class="pi pi-sign-in"></i>
                <i v-else class="pi pi-spin pi-spinner"></i>
                {{ form.processing ? "Signing in..." : "Sign In" }}
            </button>
        </form>
    </AuthLayout>
</template>
