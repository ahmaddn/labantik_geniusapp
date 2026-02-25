<script setup>
const appName = import.meta.env.VITE_APP_NAME || "Genius";
</script>

<template>
    <div class="min-h-screen flex">
        <!-- LEFT SIDE -->
        <div
            class="hidden lg:flex lg:w-1/2 relative items-center justify-center bg-blue-600 overflow-hidden"
        >
            <!-- Overlay -->
            <div class="absolute inset-0 bg-blue-700/60"></div>

            <!-- Content -->
            <div class="relative z-10 text-center text-white px-12">
                <div class="flex items-center justify-center gap-4 mb-6">
                    <div
                        class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center shadow-lg"
                    >
                        <i class="pi pi-book text-blue-600 text-2xl"></i>
                    </div>
                    <h1 class="text-4xl font-bold">
                        {{ appName }}
                    </h1>
                </div>

                <p class="text-lg text-blue-100 max-w-md mx-auto">
                    Platform pembelajaran interaktif untuk membantu kamu
                    berkembang lebih cepat dan menyenangkan.
                </p>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-white"
        >
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-500 flex items-center justify-center"
                    >
                        <i class="pi pi-book text-white text-xl"></i>
                    </div>
                    <span class="text-blue-600 text-2xl font-bold">
                        {{ appName }}
                    </span>
                </div>

                <slot />

                <!-- Footer -->
                <div class="mt-8 text-center text-sm text-gray-500">
                    Labantik | {{ appName }} © {{ new Date().getFullYear() }}
                </div>
            </div>
        </div>
    </div>
</template>
