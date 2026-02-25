<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const stats = ref([
    {
        id: 1,
        title: "Total Courses",
        value: "24",
        change: "+12%",
        trend: "up",
        icon: "pi-book",
        bgColor: "bg-purple-100",
        iconBg: "bg-purple-400",
        borderColor: "border-purple-300",
        textColor: "text-purple-600",
    },
    {
        id: 2,
        title: "Active Students",
        value: "1,248",
        change: "+8%",
        trend: "up",
        icon: "pi-users",
        bgColor: "bg-blue-100",
        iconBg: "bg-blue-400",
        borderColor: "border-blue-300",
        textColor: "text-blue-600",
    },
    {
        id: 3,
        title: "Assignments",
        value: "89",
        change: "15 pending",
        trend: "neutral",
        icon: "pi-file-edit",
        bgColor: "bg-orange-100",
        iconBg: "bg-orange-400",
        borderColor: "border-orange-300",
        textColor: "text-orange-600",
    },
    {
        id: 4,
        title: "Completion Rate",
        value: "87%",
        change: "+5%",
        trend: "up",
        icon: "pi-chart-line",
        bgColor: "bg-green-100",
        iconBg: "bg-green-400",
        borderColor: "border-green-300",
        textColor: "text-green-600",
    },
]);
</script>

<template>
    <AppLayout>
        <!-- Welcome Section -->
        <div
            class="bg-blue-500 rounded-3xl p-6 md:p-8 mb-8 shadow-playful-lg border-4 border-blue-600 overflow-hidden"
        >
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <!-- Kiri -->
                <div>
                    <h1
                        class="text-2xl md:text-3xl lg:text-4xl font-heading font-bold text-white mb-2 flex items-center gap-3"
                    >
                        <i
                            class="pi pi-user text-white text-2xl md:text-3xl"
                        ></i>
                        Selamat Datang Kembali, {{ $page.props.auth.user.name }}
                    </h1>

                    <p
                        class="text-blue-100 text-base md:text-lg font-medium flex items-center gap-2"
                    >
                        <i class="pi pi-lightbulb text-blue-200"></i>
                        Siap mengelola kegiatan hari ini?
                    </p>
                </div>

                <!-- Kanan -->
                <div class="flex lg:justify-end">
                    <button
                        class="bg-white text-blue-600 px-5 md:px-6 py-3 rounded-2xl font-bold shadow-playful hover:scale-105 transition-transform border-4 border-blue-300 flex items-center gap-2"
                    >
                        <i class="pi pi-plus"></i>
                        Tambah Modul
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                v-for="stat in stats"
                :key="stat.id"
                class="rounded-3xl p-6 shadow-playful border-4 transition-all hover:scale-105 hover:shadow-playful-lg"
                :class="[stat.bgColor, stat.borderColor]"
            >
                <div class="flex items-start justify-between mb-4">
                    <div
                        class="w-14 h-14 rounded-2xl flex items-center justify-center border-4 border-white shadow-md"
                        :class="stat.iconBg"
                    >
                        <i
                            :class="['pi', stat.icon, 'text-white text-2xl']"
                        ></i>
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold border-2"
                        :class="
                            stat.trend === 'up'
                                ? 'bg-green-200 text-green-700 border-green-400'
                                : 'bg-yellow-200 text-yellow-700 border-yellow-400'
                        "
                    >
                        {{ stat.change }}
                    </span>
                </div>

                <h3 class="text-gray-600 text-sm font-bold mb-1 font-heading">
                    {{ stat.title }}
                </h3>

                <p
                    class="text-4xl font-heading font-bold"
                    :class="stat.textColor"
                >
                    {{ stat.value }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
