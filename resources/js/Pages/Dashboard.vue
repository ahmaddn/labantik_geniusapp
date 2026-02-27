<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/UI/Card.vue";
import {
    BookOpen,
    Users,
    FileEdit,
    TrendingUp,
    User,
    Lightbulb,
    Plus,
} from "lucide-vue-next";

const stats = ref([
    {
        id: 1,
        title: "Total Courses",
        value: "24",
        change: "+12%",
        trend: "up",
        icon: BookOpen,
        iconColor: "purple",
        borderColor: "purple",
    },
    {
        id: 2,
        title: "Active Students",
        value: "1,248",
        change: "+8%",
        trend: "up",
        icon: Users,
        iconColor: "blue",
        borderColor: "blue",
    },
    {
        id: 3,
        title: "Assignments",
        value: "89",
        change: "15 pending",
        trend: "neutral",
        icon: FileEdit,
        iconColor: "orange",
        borderColor: "orange",
    },
    {
        id: 4,
        title: "Completion Rate",
        value: "87%",
        change: "+5%",
        trend: "up",
        icon: TrendingUp,
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
                        <User class="text-white w-8 h-8 md:w-9 md:h-9" />
                        Selamat Datang Kembali, {{ $page.props.auth.user.name }}
                    </h1>

                    <p
                        class="text-blue-100 text-base md:text-lg font-medium flex items-center gap-2"
                    >
                        <Lightbulb class="text-blue-200 w-5 h-5" />
                        Siap mengelola kegiatan hari ini?
                    </p>
                </div>

                <!-- Kanan -->
                <div class="flex lg:justify-end">
                    <button
                        class="bg-white text-blue-600 px-5 md:px-6 py-3 rounded-2xl font-bold shadow-playful hover:scale-105 transition-transform border-4 border-blue-300 flex items-center gap-2"
                    >
                        <Plus class="w-5 h-5" />
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
