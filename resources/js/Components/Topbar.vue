<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    Menu,
    ChevronUp,
    ChevronDown,
    User,
    Settings,
    LogOut,
} from "lucide-vue-next";

defineProps({
    sidebarOpen: Boolean,
});

defineEmits(["toggle-sidebar"]);

const page = usePage();

const showProfileMenu = ref(false);
const showNotifications = ref(false);

const userInitials = computed(() => {
    const name = page.props.auth.user.name || "";
    return name
        .split(" ")
        .map((word) => word[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});

// Click away directive
const vClickAway = {
    mounted(el, binding) {
        el.clickAwayEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        setTimeout(() => {
            document.addEventListener("click", el.clickAwayEvent);
        }, 100);
    },
    unmounted(el) {
        document.removeEventListener("click", el.clickAwayEvent);
    },
};

const toggleProfileMenu = (event) => {
    event.stopPropagation();
    showProfileMenu.value = !showProfileMenu.value;
    showNotifications.value = false;
};
</script>

<template>
    <header
        class="sticky top-0 z-20 bg-white border-b-4 border-blue-200 shadow-md transition-all duration-300"
    >
        <div class="flex items-center justify-between px-4 sm:px-6 py-3">
            <!-- Left Side: Hamburger Menu & Search -->
            <div class="flex items-center gap-4 flex-1">
                <!-- Hamburger Menu -->
                <button
                    @click.stop="$emit('toggle-sidebar')"
                    class="w-12 h-12 flex items-center justify-center rounded-2xl bg-blue-100 hover:bg-blue-200 text-blue-600 transition-all hover:scale-110 border-2 border-blue-300"
                    type="button"
                >
                    <Menu :size="20" />
                </button>
            </div>

            <!-- Right Side: Actions & Profile -->
            <div class="flex items-center gap-3">
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button
                        @click.stop="toggleProfileMenu"
                        type="button"
                        class="flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-2 rounded-2xl bg-blue-100 hover:bg-blue-200 transition-all hover:scale-105 border-2 border-blue-300"
                    >
                        <div
                            class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-playful border-4 border-blue-200"
                        >
                            {{ userInitials }}
                        </div>
                        <div class="hidden lg:block text-left">
                            <p
                                class="text-sm font-bold text-gray-800 font-heading"
                            >
                                {{ $page.props.auth.user.name }}
                            </p>
                            <p class="text-xs text-gray-600 font-medium">
                                {{ $page.props.auth.user.role }}
                            </p>
                        </div>
                        <component
                            :is="showProfileMenu ? ChevronUp : ChevronDown"
                            :size="16"
                            class="text-blue-600 hidden sm:block transition-transform duration-200"
                        />
                    </button>

                    <!-- Profile Dropdown Menu -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 -translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 -translate-y-2"
                    >
                        <div
                            v-if="showProfileMenu"
                            v-click-away="() => (showProfileMenu = false)"
                            @click.stop
                            class="absolute right-0 mt-3 w-64 bg-white rounded-3xl shadow-playful-lg border-4 border-blue-200 overflow-hidden"
                        >
                            <!-- User Info -->
                            <div
                                class="px-5 py-4 border-b-4 border-blue-100 bg-blue-50"
                            >
                                <p
                                    class="text-sm font-bold text-gray-900 font-heading"
                                >
                                    {{ $page.props.auth.user.name }}
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ $page.props.auth.user.email }}
                                </p>
                            </div>

                            <!-- Menu Items -->
                            <div class="py-2">
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-3 px-5 py-3 hover:bg-blue-50 text-gray-700 transition-all"
                                    @click="showProfileMenu = false"
                                >
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-blue-100 border-2 border-blue-300 flex items-center justify-center"
                                    >
                                        <User
                                            class="text-blue-600"
                                            :size="16"
                                        />
                                    </div>
                                    <span class="text-sm font-bold font-heading"
                                        >My Profile</span
                                    >
                                </Link>
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-3 px-5 py-3 hover:bg-purple-50 text-gray-700 transition-all"
                                    @click="showProfileMenu = false"
                                >
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-purple-100 border-2 border-purple-300 flex items-center justify-center"
                                    >
                                        <Settings
                                            class="text-purple-600"
                                            :size="16"
                                        />
                                    </div>
                                    <span class="text-sm font-bold font-heading"
                                        >Settings</span
                                    >
                                </Link>
                            </div>

                            <!-- Logout -->
                            <div class="border-t-4 border-blue-100">
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="w-full flex items-center gap-3 px-5 py-3 hover:bg-red-50 text-red-600 transition-all"
                                    @click="showProfileMenu = false"
                                >
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-red-100 border-2 border-red-300 flex items-center justify-center"
                                    >
                                        <LogOut :size="16" />
                                    </div>
                                    <span class="text-sm font-bold font-heading"
                                        >Logout</span
                                    >
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </header>
</template>
