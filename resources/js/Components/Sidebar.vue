<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    Home,
    Palette,
    GraduationCap,
    BookOpen,
    Users,
    BarChart,
    X,
} from "lucide-vue-next";

defineProps({
    open: Boolean,
    isMobile: Boolean,
});

defineEmits(["close"]);

const page = usePage();
const appName = import.meta.env.VITE_APP_NAME || "Learning Hub";

const userInitials = computed(() => {
    const name = page.props.auth.user.name || "";
    return name
        .split(" ")
        .map((word) => word[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});

const isActive = (routeName) => {
    return route().current(routeName);
};

const menuItems = [
    {
        name: "Dashboard",
        route: "dashboard",
        icon: Home,
        bgColor: "bg-blue-100",
        iconColor: "text-blue-600",
        borderColor: "border-blue-300",
        activeColor: "bg-blue-500",
    },
    {
        name: "Template Desain Modul",
        route: "admin.templates.index",
        icon: Palette,
        bgColor: "bg-blue-100",
        iconColor: "text-blue-600",
        borderColor: "border-blue-300",
        activeColor: "bg-blue-500",
    },
    {
        name: "Modul Pembelajaran",
        route: "admin.modules.index",
        icon: GraduationCap,
        bgColor: "bg-blue-100",
        iconColor: "text-blue-600",
        borderColor: "border-blue-300",
        activeColor: "bg-blue-500",
    },
    {
        name: "Kelas",
        route: "admin.classes.index",
        icon: BookOpen,
        bgColor: "bg-blue-100",
        iconColor: "text-blue-600",
        borderColor: "border-blue-300",
        activeColor: "bg-blue-500",
    },
    {
        name: "Pengguna",
        route: "admin.users.index",
        icon: Users,
        bgColor: "bg-blue-100",
        iconColor: "text-blue-600",
        borderColor: "border-blue-300",
        activeColor: "bg-blue-500",
    },
    {
        name: "Laporan & Riwayat",
        route: "admin.reports.index",
        icon: BarChart,
        bgColor: "bg-blue-100",
        iconColor: "text-blue-600",
        borderColor: "border-blue-300",
        activeColor: "bg-blue-500",
    },
];
</script>

<template>
    <aside
        class="fixed top-0 left-0 h-screen bg-white border-r-4 border-blue-200 z-40 transition-all duration-300 shadow-xl"
        :class="[
            isMobile
                ? open
                    ? 'translate-x-0 w-[280px]'
                    : '-translate-x-full w-[280px]'
                : open
                  ? 'w-[280px]'
                  : 'w-[90px]',
        ]"
    >
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div
                class="flex items-center h-20 px-6 border-b-4 border-blue-100 bg-blue-50"
                :class="open || isMobile ? 'justify-between' : 'justify-center'"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3"
                >
                    <div
                        class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center"
                    >
                        <BookOpen class="text-white" :size="20" />
                    </div>

                    <span
                        v-if="open || isMobile"
                        class="text-xl font-bold text-blue-600"
                    >
                        {{ appName }}
                    </span>
                </Link>

                <!-- Close Button Mobile -->
                <button
                    v-if="isMobile"
                    @click="$emit('close')"
                    class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-red-100 text-red-600"
                >
                    <X :size="18" />
                </button>
            </div>

            <!-- Menu -->
            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-3">
                <Link
                    v-for="item in menuItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-4 py-3 rounded-2xl transition-all border-2"
                    :class="[
                        isActive(item.route)
                            ? item.activeColor +
                              ' text-white border-transparent'
                            : 'bg-white hover:bg-gray-50 border-gray-200',
                        open || isMobile ? 'px-4' : 'justify-center',
                    ]"
                >
                    <div
                        class="w-11 h-11 flex items-center justify-center rounded-xl border-2"
                        :class="
                            isActive(item.route)
                                ? 'bg-white/20 border-white/30'
                                : item.bgColor + ' ' + item.borderColor
                        "
                    >
                        <component
                            :is="item.icon"
                            :size="20"
                            :class="[
                                isActive(item.route)
                                    ? 'text-white'
                                    : item.iconColor,
                            ]"
                        />
                    </div>

                    <span
                        v-if="open || isMobile"
                        class="font-bold text-base"
                        :class="
                            isActive(item.route)
                                ? 'text-white'
                                : 'text-gray-700'
                        "
                    >
                        {{ item.name }}
                    </span>
                </Link>
            </nav>
        </div>
    </aside>
</template>
