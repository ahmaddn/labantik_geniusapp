<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from "@/Components/UI/DataTable.vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Users,
    TrendingUp,
    ClipboardList,
    Eye,
    Star,
    CheckCircle,
    Download
} from "lucide-vue-next";
import { ref } from "vue";
import { h } from "vue";

const props = defineProps({
    module: Object,
    students: Array,
    module_summary: Object,
    mission_logs: Array,
});

const activeTab = ref("summary"); // "summary" atau "history"


const goBack = () => {
    router.visit(route("admin.reports.index"));
};

const goToStudentReport = (studentId) => {
    router.visit(
        route("admin.reports.student", {
            modules: props.module.id,
            student: studentId,
        }),
    );
};

const scoreColor = (score) => {
    if (score >= 80) return "text-blue-600";
    if (score >= 60) return "text-yellow-600";
    return "text-red-500";
};

const columns = [
    { key: "name", label: "Nama Siswa", sortable: true },
    { key: "class", label: "Kelas", sortable: true },
    {
        key: "completion",
        label: "Progres Quiz",
        sortable: true,
        align: "center",
    },
    {
        key: "overall_score",
        label: "Nilai Akhir",
        sortable: true,
        align: "center",
    },
];

const actions = [
    {
        name: "detail",
        label: "Lihat Detail",
        icon: Eye,
        class: "bg-blue-500 border-blue-600",
        showIf: () => true,
    },
];

const handleAction = ({ action, data }) => {
    if (action === "detail") goToStudentReport(data.id);
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-4">
                        <button
                            @click="goBack"
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                        >
                            <ArrowLeft class="text-blue-600 w-5 h-5" />
                        </button>
                        <div>
                            <p
                                class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5"
                            >
                                Laporan Modul
                            </p>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                {{ module.name }}
                            </h1>
                        </div>
                    </div>
                    <a :href="route('admin.reports.export', { modules: module.id, include_all: 1 })" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-all shadow-sm">
                        <Download class="w-5 h-5" /> Export Excel
                    </a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-4 border-b border-gray-200">
                <button
                    @click="activeTab = 'summary'"
                    class="pb-3 px-2 font-bold text-lg transition-colors border-b-4"
                    :class="activeTab === 'summary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    Ringkasan & Siswa
                </button>
                <button
                    @click="activeTab = 'history'"
                    class="pb-3 px-2 font-bold text-lg transition-colors border-b-4"
                    :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    Riwayat Penyelesaian Misi
                </button>
            </div>

            <div v-if="activeTab === 'summary'" class="space-y-6">
                <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 flex items-center gap-4"
                >
                    <div
                        class="bg-blue-100 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    >
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Siswa Mengerjakan</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ module_summary.total_students }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-indigo-200 shadow-playful p-5 flex items-center gap-4"
                >
                    <div
                        class="bg-indigo-100 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    >
                        <ClipboardList class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Quiz di Modul</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ module_summary.total_quizzes }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-5 flex items-center gap-4"
                >
                    <div
                        class="bg-green-100 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    >
                        <TrendingUp class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rata-rata Nilai</p>
                        <p
                            class="text-2xl font-bold"
                            :class="
                                module_summary.avg_score !== null
                                    ? scoreColor(module_summary.avg_score)
                                    : 'text-gray-400'
                            "
                        >
                            {{ module_summary.avg_score ?? "—" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div>
                <div
                    class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Daftar Siswa
                    </h2>
                    <span
                        class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold"
                    >
                        {{ students.length }} Siswa
                    </span>
                </div>

                <DataTable
                    :columns="columns"
                    :data="students"
                    :actions="actions"
                    :initial-per-page="10"
                    empty-message="Belum ada siswa yang mengerjakan modul ini."
                    search-placeholder="Cari nama atau kelas siswa..."
                    @action="handleAction"
                >
                    <!-- Progress bar untuk kolom Progres Quiz -->
                    <template #cell-completion="{ row }">
                        <div class="flex items-center gap-2 justify-center">
                            <div
                                class="w-24 bg-gray-200 rounded-full h-2 overflow-hidden"
                            >
                                <div
                                    class="h-2 rounded-full transition-all"
                                    :class="
                                        row.completion >= 80
                                            ? 'bg-blue-500'
                                            : row.completion >= 50
                                              ? 'bg-yellow-400'
                                              : 'bg-red-400'
                                    "
                                    :style="{ width: row.completion + '%' }"
                                />
                            </div>
                            <span class="text-xs text-gray-500 shrink-0">
                                {{ row.quizzes_count }}/{{ row.quizzes_total }}
                            </span>
                        </div>
                    </template>

                    <!-- Icon + angka untuk kolom Nilai Akhir -->
                    <template #cell-overall_score="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <Star
                                v-if="row.overall_score >= 80"
                                class="w-4 h-4 text-blue-500 shrink-0"
                            />
                            <CheckCircle
                                v-else-if="row.overall_score >= 60"
                                class="w-4 h-4 text-yellow-500 shrink-0"
                            />
                            <AlertTriangle
                                v-else
                                class="w-4 h-4 text-red-500 shrink-0"
                            />
                            <span
                                class="font-bold"
                                :class="scoreColor(row.overall_score)"
                            >
                                {{ row.overall_score }}
                            </span>
                        </div>
                    </template>
                </DataTable>
            </div>
            </div>

            <!-- History Tab -->
            <div v-if="activeTab === 'history'" class="space-y-6">
                <div class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Misi Selesai</h2>
                    <span class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold">
                        {{ mission_logs.length }} Catatan
                    </span>
                </div>

                <div v-if="mission_logs.length === 0" class="bg-white p-8 rounded-3xl border-2 border-gray-200 text-center">
                    <p class="text-gray-500 font-medium">Belum ada riwayat penyelesaian misi.</p>
                </div>

                <div v-else class="space-y-4">
                    <details v-for="log in mission_logs" :key="log.id" class="group bg-white rounded-2xl border-2 border-blue-100 shadow-sm overflow-hidden open:border-blue-300 transition-all">
                        <summary class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 cursor-pointer bg-blue-50/50 hover:bg-blue-50 list-none gap-4">
                            <div class="flex items-center gap-4 w-full">
                                <div class="bg-blue-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold shrink-0">
                                    {{ log.attempt_number }}
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-800 text-lg">{{ log.student_name }}</h3>
                                    <p class="text-sm text-gray-500 font-medium">{{ log.mission_name }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-gray-700">{{ log.completed_at }}</p>
                                    <p class="text-xs text-blue-600 font-semibold uppercase">Selesai</p>
                                </div>
                                <div class="shrink-0 text-blue-400 group-open:rotate-180 transition-transform">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </div>
                        </summary>
                        <div class="p-4 border-t border-blue-100 bg-white">
                            <p class="text-gray-600 text-sm">
                                Siswa ini telah menyelesaikan seluruh tahapan materi dan soal yang ada pada <b>{{ log.mission_name }}</b> (Percobaan ke-{{ log.attempt_number }}).
                                Detail skor dapat dilihat di tombol <b>Lihat Detail</b> pada tab Ringkasan & Siswa.
                            </p>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
