<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="mb-6">
            <p class="text-gray-600 text-base">
                Perbarui informasi profil dan alamat email akun Anda.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div>
                <label
                    for="name"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Nama
                </label>

                <input
                    id="name"
                    type="text"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <p
                    v-if="form.errors.name"
                    class="mt-2 text-sm text-red-600 font-medium"
                >
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <label
                    for="email"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Email
                </label>

                <input
                    id="email"
                    type="email"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <p
                    v-if="form.errors.email"
                    class="mt-2 text-sm text-red-600 font-medium"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="bg-yellow-100 border-4 border-yellow-300 rounded-2xl p-4"
            >
                <p class="text-sm text-gray-800 font-medium">
                    <i class="pi pi-info-circle mr-2 text-yellow-600"></i>
                    Email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-blue-600 underline hover:text-blue-800 font-bold ml-1"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-3 text-sm font-bold text-green-700 bg-green-100 border-4 border-green-300 rounded-xl p-3"
                >
                    <i class="pi pi-check-circle mr-2"></i>
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-blue-500 text-white px-6 py-3 rounded-xl font-bold border-4 border-blue-600 hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="pi pi-save mr-2"></i>
                    Simpan
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 transform scale-95"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 transform scale-95"
                >
                    <div
                        v-if="form.recentlySuccessful"
                        class="bg-green-500 text-white px-6 py-3 rounded-2xl shadow-playful border-4 border-green-600 font-bold"
                    >
                        <i class="pi pi-check-circle mr-2"></i>
                        Tersimpan!
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>
