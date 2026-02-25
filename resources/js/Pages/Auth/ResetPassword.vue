<script setup>
import { useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.update"));
};
</script>

<template>
    <AuthLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Reset Password</h1>
            <p class="text-slate-400">Enter your new password below</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-3.5 bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                    :class="{
                        'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                            form.errors.email,
                    }"
                    placeholder="Email"
                    required
                />
                <p v-if="form.errors.email" class="mt-2 text-sm text-red-400">
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Password -->
            <div>
                <input
                    v-model="form.password"
                    type="password"
                    class="w-full px-4 py-3.5 bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                    :class="{
                        'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                            form.errors.password,
                    }"
                    placeholder="New Password"
                    required
                />
                <p
                    v-if="form.errors.password"
                    class="mt-2 text-sm text-red-400"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm Password -->
            <div>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-4 py-3.5 bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                    :class="{
                        'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                            form.errors.password_confirmation,
                    }"
                    placeholder="Confirm Password"
                    required
                />
                <p
                    v-if="form.errors.password_confirmation"
                    class="mt-2 text-sm text-red-400"
                >
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-3.5 px-4 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-violet-500/30 hover:shadow-xl hover:shadow-violet-500/40 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-slate-900 transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            >
                <span v-if="!form.processing"> Reset Password </span>
                <span v-else> Processing... </span>
            </button>
        </form>
    </AuthLayout>
</template>
