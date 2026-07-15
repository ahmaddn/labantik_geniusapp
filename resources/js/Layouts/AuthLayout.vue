<script setup>
const appName = import.meta.env.VITE_APP_NAME || "Geniuss";
import { Book, School } from "lucide-vue-next";
</script>

<template>
    <div class="min-h-screen flex">
        <!-- LEFT SIDE -->
        <div
            class="hidden lg:block lg:w-1/2 bg-cover bg-center"
            style="background-image: url(&quot;/images/auth-bg.png&quot;)"
        ></div>

        <!-- RIGHT SIDE -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-white"
        >
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div
                        v-if="$page.props.global_settings?.platform_logo"
                        class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm p-1 border border-gray-100"
                    >
                        <img
                            :src="$page.props.global_settings.platform_logo"
                            alt="Logo"
                            class="w-full h-full object-contain"
                        />
                    </div>
                    <div
                        v-else
                        class="w-12 h-12 rounded-2xl bg-blue-500 flex items-center justify-center"
                    >
                        <Book class="text-white w-6 h-6" />
                    </div>
                    <span class="text-blue-600 text-2xl font-bold">
                        {{
                            $page.props.global_settings?.platform_name ||
                            appName
                        }}
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
