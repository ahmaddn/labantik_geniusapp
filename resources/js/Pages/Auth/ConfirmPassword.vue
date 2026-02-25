<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const form = useForm({ password: "" });
const showPassword = ref(false);

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-8">
            <h1
                class="text-4xl font-heading font-bold text-gray-800 mb-2 flex items-center gap-3"
            >
                <i class="pi pi-shield text-blue-500 text-3xl"></i>
                Confirm Password
            </h1>
            <p class="text-gray-600 font-medium flex items-center gap-2">
                <i class="pi pi-lock text-gray-400"></i>
                This is a secure area
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label
                    class="block text-sm font-bold text-gray-700 mb-2 font-heading"
                >
                    Password
                </label>

                <div class="relative">
                    <i
                        class="pi pi-lock absolute left-5 top-1/2 -translate-y-1/2 text-purple-400"
                    ></i>
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full pl-14 pr-14 py-4 bg-purple-50 border-4 border-purple-200 rounded-2xl focus:border-purple-400 focus:ring-4 focus:ring-purple-100"
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
                                'text-purple-500',
                            ]"
                        ></i>
                    </button>
                </div>

                <p
                    v-if="form.errors.password"
                    class="mt-2 text-sm text-red-500 font-bold"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 rounded-2xl border-4 border-blue-600 transition-all hover:scale-105 flex items-center justify-center gap-2"
            >
                <i v-if="!form.processing" class="pi pi-check"></i>
                <i v-else class="pi pi-spin pi-spinner"></i>
                {{ form.processing ? "Confirming..." : "Confirm" }}
            </button>
        </form>
    </AuthLayout>
</template>
