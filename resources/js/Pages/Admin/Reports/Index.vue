<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import {
    BookOpen,
    Users,
    ClipboardList,
    TrendingUp,
    ChevronRight,
} from "lucide-vue-next";

const props = defineProps({
    modules: Array,
    total_students: Number,
    total_modules: Number,
    global_avg_score: Number,
    total_attempts: Number,
});

const goToModuleHistory = (moduleId) => {
    router.visit(route("admin.reports.history", moduleId));
};

const scoreColor = (score) => {
    if (score === null || score === undefined) return "text-gray-400";
    if (score >= 80) return "text-blue-600";
    if (score >= 60) return "text-yellow-600";
    return "text-red-500";
};

const scoreBg = (score) => {
    if (score === null || score === undefined) return "bg-gray-100";
    if (score >= 80) return "bg-blue-100";
    if (score >= 60) return "bg-yellow-100";
    return "bg-red-100";
};

const scoreLabel = (score) => {
    if (score === null || score === undefined) return "Belum ada data";
    if (score >= 80) return "Baik";
    if (score >= 60) return "Cukup";
    return "Perlu Perhatian";
};

const completionBarColor = (pct) => {
    if (pct >= 80) return "bg-blue-500";
    if (pct >= 50) return "bg-yellow-400";
    return "bg-red-400";
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto space-y-8">
            <!-- Page Title -->
            <div>
                <h1
                    class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                >
                    Dashboard Laporan
                </h1>
                <p class="text-gray-500 mt-1 text-sm">
                    Pantau perkembangan belajar siswa di semua modul
                </p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 flex flex-col gap-2"
                >
                    <div
                        class="bg-blue-100 w-10 h-10 rounded-2xl flex items-center justify-center"
                    >
                        <Users class="w-5 h-5 text-blue-600" />
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ total_students }}
                    </p>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-indigo-200 shadow-playful p-5 flex flex-col gap-2"
                >
                    <div
                        class="bg-indigo-100 w-10 h-10 rounded-2xl flex items-center justify-center"
                    >
                        <BookOpen class="w-5 h-5 text-indigo-600" />
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Total Modul</p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ total_modules }}
                    </p>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-5 flex flex-col gap-2"
                >
                    <div
                        class="bg-green-100 w-10 h-10 rounded-2xl flex items-center justify-center"
                    >
                        <ClipboardList class="w-5 h-5 text-green-600" />
                    </div>
                    <p class="text-sm text-gray-500 font-medium">
                        Total Pengerjaan
                    </p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ total_attempts }}
                    </p>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-yellow-200 shadow-playful p-5 flex flex-col gap-2"
                >
                    <div
                        class="bg-yellow-100 w-10 h-10 rounded-2xl flex items-center justify-center"
                    >
                        <TrendingUp class="w-5 h-5 text-yellow-600" />
                    </div>
                    <p class="text-sm text-gray-500 font-medium">
                        Rata-rata Nilai
                    </p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ global_avg_score ?? "—" }}
                    </p>
                </div>
            </div>

            <!-- Modules Section -->
            <div>
                <div
                    class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Ringkasan per Modul
                    </h2>
                    <span
                        class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold"
                    >
                        {{ modules.length }} Modul
                    </span>
                </div>

                <!-- Empty -->
                <div
                    v-if="modules.length === 0"
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-12 text-center"
                >
                    <BookOpen class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada modul
                    </h3>
                    <p class="text-gray-500">
                        Belum ada data modul yang tersedia.
                    </p>
                </div>

                <!-- Module cards list -->
                <div v-else class="space-y-3">
                    <div
                        v-for="m in modules"
                        :key="m.id"
                        @click="goToModuleHistory(m.id)"
                        class="bg-white rounded-3xl border-4 border-blue-100 hover:border-blue-300 shadow-playful p-5 cursor-pointer transition-all hover:scale-[1.01] flex flex-col sm:flex-row sm:items-center gap-4"
                    >
                        <!-- Icon -->
                        <div
                            class="bg-gradient-to-br from-blue-100 to-indigo-100 w-14 h-14 rounded-2xl flex items-center justify-center shrink-0"
                        >
                            <BookOpen class="w-7 h-7 text-blue-500" />
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3
                                class="font-heading font-bold text-gray-800 text-base truncate mb-2"
                            >
                                {{ m.name }}
                            </h3>

                            <!-- Progress bar penyelesaian siswa -->
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden"
                                >
                                    <div
                                        class="h-2.5 rounded-full transition-all"
                                        :class="
                                            completionBarColor(
                                                total_students > 0
                                                    ? Math.round(
                                                          (m.students_done /
                                                              total_students) *
                                                              100,
                                                      )
                                                    : 0,
                                            )
                                        "
                                        :style="{
                                            width:
                                                total_students > 0
                                                    ? Math.round(
                                                          (m.students_done /
                                                              total_students) *
                                                              100,
                                                      ) + '%'
                                                    : '0%',
                                        }"
                                    />
                                </div>
                                <span class="text-xs text-gray-500 shrink-0">
                                    {{ m.students_done }} /
                                    {{ total_students }} siswa
                                </span>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Quiz count -->
                            <div class="text-center hidden sm:block">
                                <p class="text-xs text-gray-400 font-medium">
                                    Quiz
                                </p>
                                <p class="text-lg font-bold text-gray-700">
                                    {{ m.total_quizzes }}
                                </p>
                            </div>

                            <!-- Attempts -->
                            <div class="text-center hidden sm:block">
                                <p class="text-xs text-gray-400 font-medium">
                                    Pengerjaan
                                </p>
                                <p class="text-lg font-bold text-gray-700">
                                    {{ m.completed_attempts }}
                                </p>
                            </div>

                            <!-- Avg score badge -->
                            <div
                                class="px-4 py-2 rounded-2xl text-center min-w-[72px]"
                                :class="scoreBg(m.avg_score)"
                            >
                                <p
                                    class="text-xs font-medium"
                                    :class="scoreColor(m.avg_score)"
                                >
                                    {{ scoreLabel(m.avg_score) }}
                                </p>
                                <p
                                    class="text-xl font-bold"
                                    :class="scoreColor(m.avg_score)"
                                >
                                    {{ m.avg_score ?? "—" }}
                                </p>
                            </div>

                            <ChevronRight class="w-5 h-5 text-gray-300" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
