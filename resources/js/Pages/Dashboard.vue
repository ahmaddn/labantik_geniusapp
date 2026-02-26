<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/UI/Card.vue";

const stats = ref([
    {
        id: 1,
        title: "Total Courses",
        value: "24",
        change: "+12%",
        trend: "up",
        icon: "pi-book",
        iconColor: "purple",
        borderColor: "purple",
    },
    {
        id: 2,
        title: "Active Students",
        value: "1,248",
        change: "+8%",
        trend: "up",
        icon: "pi-users",
        iconColor: "blue",
        borderColor: "blue",
    },
    {
        id: 3,
        title: "Assignments",
        value: "89",
        change: "15 pending",
        trend: "neutral",
        icon: "pi-file-edit",
        iconColor: "orange",
        borderColor: "orange",
    },
    {
        id: 4,
        title: "Completion Rate",
        value: "87%",
        change: "+5%",
        trend: "up",
        icon: "pi-chart-line",
        iconColor: "green",
        borderColor: "green",
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

        <!-- Stats menggunakan Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card
                v-for="stat in stats"
                :key="stat.id"
                variant="playful"
                :title="stat.title"
                :icon="stat.icon"
                :icon-color="stat.iconColor"
                :border-color="stat.borderColor"
                :badge="stat.change"
                :badge-color="stat.trend === 'up' ? 'green' : 'yellow'"
                :hoverable="true"
            >
                <!-- Stat Value -->
                <p
                    class="text-4xl font-heading font-bold"
                    :class="`text-${stat.iconColor}-600`"
                >
                    {{ stat.value }}
                </p>
            </Card>
        </div>
    </AppLayout>
</template>
